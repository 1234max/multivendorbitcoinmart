<?php

namespace Scam;

class VendorFeedbackModel extends Model {
    public function createForOrder($order) {
        $sql = 'INSERT INTO vendor_feedbacks (order_id, vendor_id, buyer_id) VALUES (:order_id, :vendor_id, :buyer_id)';
        $query = $this->db->prepare($sql);
        return $query->execute([':order_id' => $order->id,
            ':vendor_id' => $order->vendor_id,
            ':buyer_id' => $order->buyer_id]);
    }

    public function getForOrder($orderId) {
        $q = $this->db->prepare('SELECT * FROM vendor_feedbacks WHERE order_id = :order_id LIMIT 1');
        $q->execute([':order_id' => $orderId]);
        $feedback = $q->fetch();
        return $feedback ? $feedback : null;
    }

    public function update($feedback) {
        $sql = 'UPDATE vendor_feedbacks SET rating = :rating, comment = :comment WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':rating' => intval($feedback->rating),
            ':comment' => $feedback->comment,
            ':id' => $feedback->id]);
    }

    public function getAverageAndDealsOfVendor($userId) {
        # MySQL's AVG handles null values just fine, so we dont have to exclude feedbacks with rating = NULL
        $q = $this->db->prepare('SELECT AVG(rating) AS average, COUNT(id) AS deals FROM vendor_feedbacks WHERE vendor_id = :vendor_id;');
        $q->execute([':vendor_id' => $userId]);
        $feedback = $q->fetch();
        return $feedback ? [$feedback->average, $feedback->deals] : [0, 0];
    }

    public function getAllOfVendor($userId) {
        $q = $this->db->prepare('SELECT v.rating, v.comment, ' .
            '(SELECT COUNT(v2.id) FROM vendor_feedbacks v2 where v2.buyer_id =v.buyer_id) as buyer_deal_count ' .
            'FROM vendor_feedbacks v ' .
            'JOIN users u ON v.buyer_id = u.id ' .
            'WHERE v.vendor_id = :vendor_id AND comment IS NOT NULL ' .
            'ORDER BY v.id DESC');
        $q->execute([':vendor_id' => $userId]);
        $feedbacks = $q->fetchAll();
        return $feedbacks ? $feedbacks : [];
    }
}