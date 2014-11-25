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
        ]);
    }

    public static function needsActionFromBuyer($state) {
        return in_array($state, [
            self::$STATES['unconfirmed'],
            self::$STATES['accepted'],
            self::$STATES['shipped'],
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

    public function getOneOfUser($userId, $isVendor, $idHash, $sessionSecret) {
        $sql = 'SELECT o.id, o.title, o.price, o.state, o.created_at, o.updated_at, o.buyer_id, o.vendor_id, o.amount, o.product_id, o.shipping_info, o.finish_text, ' .
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

    public function confirm($orderId, $shippingInfo) {
        $sql = 'UPDATE orders SET state = :state, shipping_info = :shipping_info WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $orderId, ':state' => self::$STATES['confirmed'], ':shipping_info' => $shippingInfo]);
    }

    public function accept($orderId) {
        $sql = 'UPDATE orders SET state = :state WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $orderId, ':state' => self::$STATES['accepted']]);
    }

    public function decline($orderId, $message) {
        $sql = 'UPDATE orders SET state = :state, finish_text = :finish_text WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $orderId, ':state' => self::$STATES['finished'],
            ':finish_text' => "Vendor declined order with message: \n" . $message]);
    }

    public function paid($orderId) {
        $sql = 'UPDATE orders SET state = :state WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $orderId, ':state' => self::$STATES['paid']]);
    }

    public function shipped($orderId) {
        $sql = 'UPDATE orders SET state = :state, shipping_info = :shipping_info WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $orderId, ':state' => self::$STATES['shipped'],
            ':shipping_info' => "Wiped shipping info, shipped at: " . date(DATE_RFC850)]);
    }

    public function received($order) {
        $this->db->beginTransaction();

        try {
            $sql = 'UPDATE orders SET state = :state, finish_text = :finish_text WHERE id = :id';
            $query = $this->db->prepare($sql);
            $result = $query->execute([':id' => $order->id, ':state' => self::$STATES['finished'],
                ':finish_text' => "Order successfully finished, funds released at: " . date(DATE_RFC850) ]);

            # create (empty) feedback
            if($result) {
                if(!$this->getModel('VendorFeedback')->createForOrder($order)) {
                    throw new \Exception('Feedback couldnt be created');
                }
            }
            else {
                throw new \Exception('Order couldnt be saved');
            }

            $this->db->commit();
            return true;
        }
        catch(\Exception $e){
            $this->db->rollBack();
            return false;
        }
    }

    public function delete($id) {
        $q = $this->db->prepare('DELETE FROM orders WHERE id = :id');
        return $q->execute([':id' => $id]);
    }
}