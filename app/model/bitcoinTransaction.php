<?php

namespace Scam;

class BitcoinTransactionModel extends Model {
    public function addNewBlock($blockId) {
        $c = $this->getBitcoinClient();

        # return if no bitcoin server is running
        if(!$c->getinfo()) {
            throw new \Exception('No bitcoind running');
        }

        # get block infos (ie transactions) from bitcoind
        $blockHash = $c->getblock($blockId);

        print "-- Received block $blockId, start fetching transaction at " . date(DATE_RFC850) . "\n";

        if(!isset($blockHash['tx'])) {
            throw new \Exception('Could not receive block info from bitcoind for block: ' . $blockId);
        }

        print "Found " . count($blockHash['tx']) . " transactions\n";

        # save transactions to database for later handling
        $this->db->beginTransaction();

        $sql = 'INSERT INTO bitcoin_transactions (tx_id, raw_tx) VALUES (:tx_id, :raw_tx)';
        $query = $this->db->prepare($sql);

        try {
            # get transaction details for every transaction
            foreach($blockHash['tx'] as $txId) {
                print "Saving TX $txId for later handling\n";
                $query->execute([':tx_id' => $txId, ':raw_tx' => $c->getrawtransaction($txId)]);
            }

            $this->db->commit();
            print "-- Finished block $blockId at " . date(DATE_RFC850) . "\n";
            return true;
        }
        catch(\Exception $e){
            $this->db->rollBack();
            throw $e;
            return false;
        }
    }

    public function checkTransactionsForOrderPayments() {
        $c = $this->getBitcoinClient();

        # return if no bitcoin server is running
        if(!$c->getinfo()) {
            throw new \Exception('No bitcoind running');
        }

        $this->db->beginTransaction();

        print "-- Checking saved transaction for order payments at " . date(DATE_RFC850) . "\n";

        try {
            # get all transactions that "blockNotify/addNewBlock" has saved before in bitcoin_transactions, traverse them and delete them.
            $q = $this->db->prepare('SELECT * FROM bitcoin_transactions');
            $q->execute();
            $transactions = $q->fetchAll();
            $transactions = $transactions ? $transactions : [];
            print "Checking " . count($transactions) . " transactions that came in.\n";

            # get all multisig addresses that we need to watch from orders (to be paid to by the buyer)
            $addressesToWatch = $this->getModel('Order')->getWatchedAddressesForAcceptedOrders();

            # get all transactions (that were used to pay to the multisig address) and are used to pay the vendor
            $txIdsToWatch = $this->getModel('Order')->getWatchedTransactionsForFinishingOrders();

            print "Watching " . count($addressesToWatch) . " addresses for transactions to them, " . count($txIdsToWatch)  . " txids for transactions from them.\n";

            foreach($transactions as $transaction) {
                print "Decoding raw transaction " . $transaction->tx_id . "\n";

                try {
                    $decodedTx = $c->decoderawtransaction($transaction->raw_tx);
                }
                catch(\Exception $e) {
                    print "Skipping error while decoding raw transaction\n";
                    # sometimes, bitcoind's json rpc api seems to have problems with giant transactions. skip them.
                    continue;
                }

                # if we'd like to handle a transaction only after some confirmations, we should check here via $c->gettransaction

                ### check for payment to our multisig address (buyer paid)
                # store them in database, since multiple transaction might be made until the order price is met
                $toAddresses = $this->parseAddressesFromVout($decodedTx['vout']);
                foreach($toAddresses as $toAddress) {
                    if(in_array($toAddress['address'], $addressesToWatch)) {
                        print "Found transaction " . $transaction->tx_id . " that pays " . $toAddress['value'] . " BTC to watched address " . $toAddress['address'] . ".\n";
                        $sql = 'INSERT INTO bitcoin_payments (tx_id, address, value, vout, pk_script) VALUES (:tx_id, :address, :value, :vout, :pk_script)';
                        $query = $this->db->prepare($sql);
                        try {
                            $query->execute([
                                ':tx_id' => $transaction->tx_id,
                                ':address' => $toAddress['address'],
                                ':value' => $toAddress['value'],
                                ':vout' => $toAddress['vout'],
                                ':pk_script' => $toAddress['pkScript'],
                            ]);
                        }
                        catch(\Exception $e){
                            # ignore errs duplicate entries (if we handle the same transaction twice) silently
                            if(strpos($e->getMessage(), 'Duplicate') === false) {
                                throw $e;
                            }
                            else {
                                print "Ignoring already handled transaction\n";
                            }
                        }
                    }
                }

                ### check for payment from our multisig address (buyer signed & broadcasted) = a transaction that was paid before is now used as input
                # get all transactions that are used as inputs in this transaction and check if it is one of the payments to our multisig addresses
                $spentTransactions = $this->parseTxIdsFromVin($decodedTx['vin']);
                foreach($spentTransactions as $spentTransaction) {
                    if(isset($txIdsToWatch[$spentTransaction])) {
                        $order = $txIdsToWatch[$spentTransaction];
                        print "Found transaction " . $transaction->tx_id . " that uses transaction " . $spentTransaction . " as input for a payment.\n";
                        print "It belongs to order " .$order->id . ", checking if valid\n";
                        # lookup complete order (to get unsigned transaction etc)
                        $order = $this->getModel('Order')->getOne($order->id);
                        # check transaction validity: we must compare with the unsigned_transaction (for normal orders) ore with the
                        $baseTransaction = $order->state ==  \Scam\OrderModel::$STATES['dispute'] ? $order->dispute_signed_transaction : $order->unsigned_transaction;
                        $isValid = $this->isValidSignedTransaction($baseTransaction, $transaction->raw_tx);
                        print "Transaction for order is: " . ($isValid ? 'valid' : 'invalid') .". Setting order to finished.\n";
                        $this->getModel('Order')->received($order, $transaction->tx_id, $isValid);
                    }
                }
            }

            $this->db->prepare('DELETE FROM bitcoin_transactions')->execute();
            $this->db->commit();

            print "-- Finished checking saved transaction for order payments at " . date(DATE_RFC850) . "\n";
            return true;
        }
        catch(\Exception $e){
            $this->db->rollBack();
            return false;
        }
    }

    /* gets hash {address => payment_value} from a decoded raw tx (vout) that looks like this:
    "vout" : [
        {
            "value" : 50.00000000,
            "n" : 0,
            "scriptPubKey" : {
                "asm" : "022953f8c23b3a0c02f8d28e8821c1d683bca5714d794afe51dc7d54cfb44d4c48 OP_CHECKSIG",
                "hex" : "21022953f8c23b3a0c02f8d28e8821c1d683bca5714d794afe51dc7d54cfb44d4c48ac",
                "reqSigs" : 1,
                "type" : "pubkey",
                "addresses" : [
                    "mqxLrsALFQrSfAP1Vo2Ydb5Z9CCGoPkW7T"
                ]
            }
        }
    ]
    */
    private function parseAddressesFromVout($voutArr) {
        $addresses = [];
        foreach($voutArr as $vout) {
            if(isset($vout['scriptPubKey']['addresses'][0])) {
                $addresses[] = ['address' => $vout['scriptPubKey']['addresses'][0],
                    'pkScript' => $vout['scriptPubKey']['hex'],
                    'value' => $vout['value'],
                    'vout' => $vout['n']];
            }
        }
        return $addresses;
    }

    private function parseTxIdsFromVin($vinArr) {
        return array_map(function($vin){return isset($vin['txid']) ? $vin['txid'] : '';}, $vinArr);
    }

    public function checkIfOrdersArePaid() {
        $nowPaidOrders = $this->getModel('Order')->GetNowPaidOrders();
        print "Accepted orders that are now paid: " . count($nowPaidOrders) . "\n";
        foreach($nowPaidOrders as $order) {
            print "Order with id " . $order->id ." is now paid with a total amount of " .
                $order->total ." (price: " . $order->price . "). Setting to paid and creating transaction for vendor. \n";
            $this->getModel('Order')->paid($order->id, $order->vendor_payout_address);
        }
    }

    /* checks if a given transaction (provided by the user -partially signed - or picked up in the blockchain - completely signed)
    is the signed equivalent of an unsigned one (that we created) */
    public function isValidSignedTransaction($validTransaction, $signedTransaction) {
        try {
            $c = $this->getBitcoinClient();

            # return if no bitcoin server is running
            if(!$c->getinfo()) {
                throw new \Exception('No bitcoind running');
            }

            # decode both first
            $rawValid = $c->decoderawtransaction($validTransaction);
            $rawSigned = $c->decoderawtransaction($signedTransaction);

            # now compare vins & vouts - should be exactly the same, only vin -> scriptSig should be given in signedTransaction
            $valid = true;
            foreach($rawSigned['vout'] as $i => $vout) {
                foreach($vout as $k => $v) {
                    if ($rawValid['vout'][$i][$k] !== $v) {
                        $valid = false;
                    }
                }
            }

            foreach($rawSigned['vin'] as $i => $vin) {
                foreach($vin as $k => $v) {
                    if($k == 'scriptSig') {
                        if(!(strlen($v['asm']) > 0 && strlen($v['hex']) > 0)) {
                            $valid = false;
                        }
                    }
                    else {
                        if($rawValid['vin'][$i][$k] !== $v){
                            $valid = false;
                        }
                    }
                }
            }
            return $valid;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function isValidBitcoinAddress($p) {
        /* base58 encoded, 26-35 chars length
        https://en.bitcoin.it/wiki/Address */
        return preg_match('/^[a-km-zA-HJ-NP-Z0-9]{26,35}$/', $p);
    }
}