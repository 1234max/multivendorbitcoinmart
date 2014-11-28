<?php

namespace Scam;

class BitcoinTransactionModel extends Model {
    public function addNewBlock($blockId) {
        $c = $this->getBitcoinClient();

        # return if no bitcoin server is running
        if(!$c->getinfo()) {
            throw new Exception('No bitcoind running');
        }

        # get block infos (ie transactions) from bitcoind
        $blockHash = $c->getblock($blockId);

        print "-- Received block $blockId, start fetching transaction at " . date(DATE_RFC850) . "\n";

        if(!isset($blockHash['tx'])) {
            throw new Exception('Could not receive block info from bitcoind for block: ' . $blockId);
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
            print "-- Finished block $blockId at" . date(DATE_RFC850) . "\n";
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
            throw new Exception('No bitcoind running');
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

            # get all multisig address that we need to watch from orders

            $addressesToWatch = $this->getModel('Order')->getWatchedAddresses();
            print "Checking " . count($addressesToWatch) . " addresses that we have to watch.\n";

            foreach($transactions as $transaction) {
                $decodedTx = $c->decoderawtransaction($transaction->raw_tx);

                # check for payment to our multisig address (buyer paid)
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

                # payment from our multisig address (buyer signed & broadcasted)
                # TODO
            }

            $this->db->prepare('DELETE FROM bitcoin_transactions')->execute();
            $this->db->commit();

            print "-- Finished checking saved transaction for order payments at " . date(DATE_RFC850) . "\n";
            return true;
        }
        catch(\Exception $e){
            $this->db->rollBack();
            throw $e;
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

    public function checkIfOrdersArePaid() {
        $nowPaidOrders = $this->getModel('Order')->GetNowPaidOrders();
        print "Accepted orders that are now paid: " . count($nowPaidOrders) . "\n";
        foreach($nowPaidOrders as $order) {
            print "Order with id " . $order->id ." is now paid with a total amount of " .
                $order->total ." (price: " . $order->price . "). Setting to paid and creating transaction for vendor. \n";
            $this->getModel('Order')->paid($order->id, $order->vendor_payout_address);
        }
    }
}