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
}