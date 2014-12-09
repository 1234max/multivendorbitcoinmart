<?php

namespace Scam;

class OrderModel extends Model {
    public static $STATES = [
        'unconfirmed' => 0,
        'confirmed' => 1,
        'accepted' => 2,
        'paid' => 3,
        'shipped' => 4,
        'finished' => 5,
        'dispute' => 6,
    ];

    public static function stateDescription($state) {
        return ($key = array_search($state, self::$STATES)) ? $key : 'invalid';
    }

    public static function needsActionFrom($isVendor, $state) {
        return $isVendor ? self::needsActionFromVendor($state) : self::needsActionFromBuyer($state);
    }

    public static function needsActionFromVendor($state) {
        return in_array($state, [
            self::$STATES['confirmed'],
            self::$STATES['paid'],
            self::$STATES['dispute'],
        ]);
    }

    public static function needsActionFromBuyer($state) {
        return in_array($state, [
            self::$STATES['unconfirmed'],
            self::$STATES['accepted'],
            self::$STATES['shipped'],
            self::$STATES['dispute'],
        ]);
    }

    public static function isDeletable($order, $userId) {
        $daysLastUpdatedAgo = round((time() - strtotime($order->updated_at)) / (60 * 60 * 24), 2);
        return ($order->buyer_id == $userId && $order->state == self::$STATES['unconfirmed']) ||
        ($order->state == self::$STATES['finished'] && $daysLastUpdatedAgo >= 14);
    }

    public function getUnconfirmedOfUser($userId, $isVendor) {
        # dont show unconfirmed to vendors
        if($isVendor) {
            return [];
        }

        return $this->getForList($userId, $isVendor, 'state = ' . self::$STATES['unconfirmed']);
    }

    public function getPendingOfUser($userId, $isVendor) {
        return $this->getForList($userId, $isVendor, 'state BETWEEN ' . self::$STATES['confirmed'] . ' AND ' . self::$STATES['shipped']);
    }

    public function getFinishedOfUser($userId, $isVendor) {
        return $this->getForList($userId, $isVendor, 'state = ' . self::$STATES['finished']);
    }

    public function getNeededActionsOfUser($userId, $isVendor) {
        return $isVendor ? $this->getNeedingActionsOfVendor($userId) : $this->getNeedingActionsOfBuyer($userId);
    }

    public function getNeedingActionsOfVendor($userId) {
        return $this->getForList($userId, true, 'state = ' . self::$STATES['confirmed'] . ' OR state = ' . self::$STATES['paid']);
    }

    public function getNeedingActionsOfBuyer($userId) {
        return $this->getForList($userId, false, 'state = ' . self::$STATES['accepted'] . ' OR state = ' . self::$STATES['shipped']);
    }

    # gets all orders in a given state (according to $stateWhere) for either a vendor or a buyer.
    private function getForList($userId, $isVendor, $stateWhere) {
        $sql = 'SELECT o.id, o.title, o.price, o.state, o.updated_at, o.buyer_id, o.vendor_id, ' .
            'v.name AS vendor_name, b.name AS buyer_name FROM orders o ' .
            'JOIN users v ON o.vendor_id = v.id '.
            'JOIN users b ON o.buyer_id = b.id '.
            'WHERE ' . ($isVendor ? 'v' : 'b') . '.id = :user_id AND ' . $stateWhere . ' ' .
            'ORDER BY updated_at DESC';
        $q = $this->db->prepare($sql);

        $q->execute([':user_id' => $userId]);
        $orders = $q->fetchAll();
        return $orders ? $orders : [];
    }

    public function hasOrdersAsBuyer($userId) {
        $sql = 'SELECT id FROM orders WHERE buyer_id = :buyer_id LIMIT 1';
        $q = $this->db->prepare($sql);

        $q->execute([':buyer_id' => $userId]);
        $order = $q->fetch();
        return $order ? true : false;
    }

    public function getDisputesForAdmin() {
        $sql = 'SELECT o.id, o.title, o.price, o.state, o.updated_at, o.buyer_id, o.vendor_id, ' .
            'v.name AS vendor_name, b.name AS buyer_name FROM orders o ' .
            'JOIN users v ON o.vendor_id = v.id '.
            'JOIN users b ON o.buyer_id = b.id '.
            'WHERE o.state = :dispute '.
            'ORDER BY updated_at DESC';
        $q = $this->db->prepare($sql);

        $q->execute([':dispute' => self::$STATES['dispute']]);
        $orders = $q->fetchAll();
        return $orders ? $orders : [];
    }

    public function getDisputeForAdmin($orderId) {
        $sql = 'SELECT o.*, ' .
            'v.name AS vendor_name, b.name AS buyer_name, p.name AS product_name, p.code AS product_code, p.price AS product_price ' .
            'FROM orders o ' .
            'JOIN users v ON o.vendor_id = v.id '.
            'JOIN users b ON o.buyer_id = b.id '.
            'LEFT OUTER JOIN products p ON o.product_id = p.id ' .
            'WHERE o.id = :id AND o.state = :dispute LIMIT 1';
        $q = $this->db->prepare($sql);

        $q->execute([':id' => $orderId, ':dispute' => self::$STATES['dispute']]);
        $order = $q->fetch();
        return $order ? $order : null;
    }

    public function getOneOfUser($userId, $isVendor, $idHash, $sessionSecret) {
        $sql = 'SELECT o.id, o.title, o.price, o.state, o.created_at, o.updated_at, o.buyer_id, o.vendor_id, ' .
            'o.amount, o.product_id, o.shipping_info, o.finish_text, ' .
            'o.buyer_public_key, o.buyer_key_index, o.buyer_refund_address, ' .
            'o.vendor_public_key, o.vendor_key_index, o.vendor_payout_address, ' .
            'o.admin_public_key, o.admin_key_index, ' .
            'o.multisig_address, o.redeem_script, ' .
            'o.unsigned_transaction, o.partially_signed_transaction, ' .
            'o.dispute_message, o.dispute_signed_transaction, ' .
            'v.name AS vendor_name, b.name AS buyer_name, p.name AS product_name, p.code AS product_code, p.price AS product_price, ' .
            'f.rating, f.comment, f.id AS feedback_id FROM orders o ' .
            'JOIN users v ON o.vendor_id = v.id '.
            'JOIN users b ON o.buyer_id = b.id '.
            'LEFT OUTER JOIN products p ON o.product_id = p.id ' .
            'LEFT OUTER JOIN vendor_feedbacks f ON o.id = f.order_id ' .
            'WHERE ' . ($isVendor ? 'v' : 'b') . '.id = :user_id AND SHA2(CONCAT(o.id, :session_secret), "256") = :id_hash ' .
            'LIMIT 1';
        $q = $this->db->prepare($sql);

        $q->execute([':user_id' => $userId, ':id_hash' => $idHash, ':session_secret' => $sessionSecret]);
        $order = $q->fetch();
        return $order ? $order : null;
    }

    public function getOne($orderId) {
        $sql = 'SELECT * FROM orders WHERE id = :id LIMIT 1';
        $q = $this->db->prepare($sql);

        $q->execute(['id' => $orderId]);
        $order = $q->fetch();
        return $order ? $order : null;
    }

    public function create($order) {
        $sql = 'INSERT INTO orders (title, price, amount, buyer_id, vendor_id, product_id, shipping_option_id, created_at, updated_at) '
            . 'VALUES (:title, :price, :amount, :buyer_id, :vendor_id, :product_id, :shipping_option_id, null, null)';
        $query = $this->db->prepare($sql);
        $req = $query->execute([':title' => $order->title,
            ':price' => floatval($order->price),
            ':amount' => intval($order->amount),
            ':buyer_id' => $order->buyer_id,
            ':vendor_id' => $order->vendor_id,
            ':product_id' => $order->product_id,
            ':shipping_option_id' => $order->shipping_option_id]);
        return $req ? $this->db->lastInsertId() : false;
    }

    public function confirm($order, $shippingInfo, $buyerRefundAddress) {
        $this->db->beginTransaction();
        $userModel = $this->getModel('User');

        try {
            # generate new pubkey from bip32 key of buyer
            list($buyerKeyIndex, $buyerPublicKey) = $userModel->getNextPublicKeyFromBip32($order->buyer_id);

            # encrypt shipping message
            $encryptedShippingInfo = $userModel->encryptMessageForUser($order->vendor_id, $shippingInfo);
            if($encryptedShippingInfo === false) {
                throw new \Exception('Could not encrypt');
            }

            $sql = 'UPDATE orders SET state = :state, shipping_info = :shipping_info, ' .
                'buyer_public_key = :buyer_public_key, buyer_key_index = :buyer_key_index, ' .
                'buyer_refund_address = :buyer_refund_address WHERE id = :id';
            $query = $this->db->prepare($sql);
            $ret = $query->execute([':id' => $order->id,
                ':state' => self::$STATES['confirmed'],
                ':shipping_info' => $encryptedShippingInfo,
                ':buyer_public_key' => $buyerPublicKey,
                ':buyer_key_index' => $buyerKeyIndex,
                ':buyer_refund_address' => $buyerRefundAddress]);
            if(!$ret) {
                throw new \Exception('Error while saving order');
            }
            else {
                $this->db->commit();
                return true;
            }
        }
        catch(\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function accept($order, $vendorPayoutAddress) {
        $this->db->beginTransaction();
        $userModel = $this->getModel('User');

        try {
            # first, generate new pubkey from bip32 key of vendor
            list($vendorKeyIndex, $vendorPublicKey) = $userModel->getNextPublicKeyFromBip32($order->vendor_id);

            # generate new pubkey from bip32 key of admin
            list($adminKeyIndex, $adminPublicKey) = $userModel->getNextPublicKeyFromBip32OfAdmin();

            # then create multisig address
            list($multisigAddress, $redeemScript) = $this->createMultisigAddress($vendorPublicKey, $order->buyer_public_key, $adminPublicKey);

            $sql = 'UPDATE orders SET state = :state, vendor_public_key = :vendor_public_key, ' .
                'vendor_key_index = :vendor_key_index, vendor_payout_address = :vendor_payout_address, ' .
                'admin_public_key = :admin_public_key, admin_key_index = :admin_key_index, ' .
                'multisig_address = :multisig_address, redeem_script = :redeem_script '.
                'WHERE id = :id';
            $query = $this->db->prepare($sql);
            $ret = $query->execute([':id' => $order->id,
                ':state' => self::$STATES['accepted'],
                ':vendor_public_key' => $vendorPublicKey,
                ':vendor_key_index' => $vendorKeyIndex,
                ':vendor_payout_address' => $vendorPayoutAddress,
                ':admin_public_key' => $adminPublicKey,
                ':admin_key_index' => $adminKeyIndex,
                ':multisig_address' => $multisigAddress,
                ':redeem_script' => $redeemScript]);
            if(!$ret) {
                throw new \Exception('Error while saving order');
            }
            else {
                $this->db->commit();
                return true;
            }
        }
        catch(\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    private function createMultisigAddress($vendorPublicKey, $buyerPublicKey, $adminPublicKey) {
        $c = $this->getBitcoinClient();

        # return if no bitcoin server is running
        if(!$c->getinfo()) {
            throw new \Exception('No bitcoind running');
        }

        $ret = $c->createmultisig(2, [$vendorPublicKey, $buyerPublicKey, $adminPublicKey]);
        return [$ret['address'], $ret['redeemScript']];
    }

    /* returns transaction ids (and their order ids) that we need to watch for transactions FROM them:
     * - orders that are shipped and buyer signs & broadcasts the funding transaction
     * - orders that are being disputed and are resolved by a new transaction
     */
    public function getWatchedTransactionsForFinishingOrders() {
        # we dont only watch transaction for orders with state = shipped, but all payments that came to our multisig addresses.
        # this way, we see any scam that happens before state was set to shipped (ie vendor & buyer pay the multisig funds early to another address)
        $sql = 'SELECT o.id, p.tx_id FROM orders o JOIN bitcoin_payments p ON o.multisig_address = p.address WHERE state <> :finished';
        $q = $this->db->prepare($sql);

        $q->execute([':finished' => self::$STATES['finished']]);
        $orders = $q->fetchAll();
        if($orders)
        {
            # index by tx id for easier lookup (and that we have order id afterwards)
            $indexed = [];
            foreach($orders as $order){
                $indexed[$order->tx_id] = $order;
            }
            return $indexed;
        }
        return [];
    }

    /* returns multisig address that we need to watch for transactions TO them:
     * - orders that are accepted and need to be paid
     */
    public function getWatchedAddressesForAcceptedOrders() {
        $sql = 'SELECT multisig_address FROM orders WHERE state = :accepted';
        $q = $this->db->prepare($sql);

        $q->execute([':accepted' => self::$STATES['accepted']]);
        $orders = $q->fetchAll();
        return $orders ? array_map(function($order){return $order->multisig_address;}, $orders) : [];
    }

    /* get all accepted orders who have bit coin payments fulfilling their price */
    public function GetNowPaidOrders() {
        $sql = 'SELECT o.id, o.price, o.vendor_payout_address, SUM(p.value) as total FROM orders o ' .
            'JOIN bitcoin_payments p ON o.multisig_address=p.address ' .
            'WHERE o.state = :state ' .
            'HAVING SUM(p.value) >= o.price;';
        $q = $this->db->prepare($sql);
        $q->execute([':state' => self::$STATES['accepted']]);
        $orders = $q->fetchAll();
        return $orders ? $orders : [];
    }

    public function decline($orderId, $message) {
        $sql = 'UPDATE orders SET state = :state, finish_text = :finish_text WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $orderId, ':state' => self::$STATES['finished'],
            ':finish_text' => "Vendor declined order with message: \n" . $message]);
    }

    public function paid($orderId, $toAddress) {
        # create transaction for vendor
        $unsignedTransaction = $this->createUnsignedTransaction($orderId, $toAddress);

        $sql = 'UPDATE orders SET state = :state, unsigned_transaction = :unsigned_transaction WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $orderId,
            ':state' => self::$STATES['paid'],
            ':unsigned_transaction' => $unsignedTransaction]);
    }

    private function createUnsignedTransaction($orderId, $toAddress) {
        $c = $this->getBitcoinClient();

        # return if no bitcoin server is running
        if(!$c->getinfo()) {
            throw new \Exception('No bitcoind running');
        }

        # get all bitcoin payments that went to the multisig address and construct the transaction paying the vendor
        $totalPrice = 0.0; # for now, we just pay the vendor all funds that were coming to the multisig address (even if it's more)
        $inputs = []; # inputs are all TX_OUTs from bitcoin_payments
        foreach($this->getModel('BitcoinPayment')->getAllOfOrder($orderId) as $payment) {
            $inputs[] = [
                'txid' => $payment->tx_id,
                'vout' => intval($payment->vout)];
            # we could embed redeemscript & pk_script here, but it's not possible without
            # manually building transaction since createrawtransaction cant take additional params.
            $totalPrice += $payment->value;
        }
        return $c->createrawtransaction($inputs, [$toAddress => $totalPrice]);
    }

    /* when signing the transaction that releases the funds from the multisig to the vendor,
     * the end users must sign not only the raw transaction, but must also provide all inputs and redeem scripts (
     * (see https://bitcoin.org/en/developer-examples#p2sh-multisig in part with signrawtransaction)
     */
    public function getPaymentInputsForSigning($orderId, $redeemScript) {
        $inputs = []; # inputs are all TX_OUTs from bitcoin_payments
        foreach($this->getModel('BitcoinPayment')->getAllOfOrder($orderId) as $payment) {
            $inputs[] = [
                'txid' => $payment->tx_id,
                'vout' => intval($payment->vout),
                'scriptPubKey' => $payment->pk_script,
                'redeemScript' => $redeemScript];
        }
        return $inputs;
    }

    public function shipped($orderId, $partiallySignedTransaction) {
        $sql = 'UPDATE orders SET state = :state, shipping_info = :shipping_info, partially_signed_transaction = :partially_signed_transaction WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $orderId,
            ':state' => self::$STATES['shipped'],
            ':partially_signed_transaction' => $partiallySignedTransaction,
            ':shipping_info' => "Wiped shipping info, shipped at: " . date(DATE_RFC850)]);
    }

    public function received($order, $txId, $isValid) {
        $sql = 'UPDATE orders SET state = :state, finish_text = :finish_text WHERE id = :id';
        $query = $this->db->prepare($sql);
        $finishText = $isValid ? "Order successfully finished, funds released with transaction $txId at: " . date(DATE_RFC850) :
            "Order finished but with an invalid transaction ($txId) at: " . date(DATE_RFC850);
        $result = $query->execute([':id' => $order->id, ':state' => self::$STATES['finished'], ':finish_text' => $finishText]);

        # create (empty) feedback
        if($result) {
            if($isValid) {
                if(!$this->getModel('VendorFeedback')->createForOrder($order)) {
                    throw new \Exception('Feedback couldnt be created');
                }
            }
        }
        else {
            throw new \Exception('Order couldnt be saved');
        }
    }

    public function delete($id) {
        $q = $this->db->prepare('DELETE FROM orders WHERE id = :id');
        return $q->execute([':id' => $id]);
    }

    public function dispute($orderId, $disputeMessage) {
        $sql = 'UPDATE orders SET state = :state, dispute_message = :dispute_message WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $orderId,
            ':state' => self::$STATES['dispute'],
            ':dispute_message' => $disputeMessage]);
    }

    /* creates a new (unsigned) raw transaction that refunds buyer & vendor as specified in $recipients */
    public function createNewTransactionForDispute($orderId, $disputeMessage, $recipients) {
        $c = $this->getBitcoinClient();

        # return if no bitcoin server is running
        if(!$c->getinfo()) {
            return false;
        }

        # get all bitcoin payments that went to the multisig address and construct the transaction paying as we specified
        $inputs = []; # inputs are all TX_OUTs from bitcoin_payments
        foreach($this->getModel('BitcoinPayment')->getAllOfOrder($orderId) as $payment) {
            $inputs[] = [
                'txid' => $payment->tx_id,
                'vout' => intval($payment->vout)];
        }
        $disputeTransaction = $c->createrawtransaction($inputs, $recipients);

        $sql = 'UPDATE orders SET dispute_message = :dispute_message, dispute_unsigned_transaction = :transaction WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $orderId,
            ':dispute_message' => $disputeMessage, ':transaction' => $disputeTransaction]);
    }

    public function enterSignedTransactionForDispute($orderId, $transaction) {
        $sql = 'UPDATE orders SET dispute_signed_transaction = :dispute_signed_transaction, ' .
            'dispute_unsigned_transaction = NULL ' .
            'WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $orderId,
            ':dispute_signed_transaction' => $transaction]);
    }
}