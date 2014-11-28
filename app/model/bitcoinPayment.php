<?php

namespace Scam;

class BitcoinPaymentModel extends Model {
    public function getAllOfOrder($orderId){
        $sql = 'SELECT p.* FROM bitcoin_payments p ' .
            'JOIN orders o ON o.multisig_address=p.address ' .
            'WHERE o.id = :id';
        $q = $this->db->prepare($sql);
        $q->execute([':id' => $orderId]);
        $payments = $q->fetchAll();
        return $payments ? $payments : [];
    }
}