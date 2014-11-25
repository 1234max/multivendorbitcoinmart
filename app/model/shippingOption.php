<?php

namespace Scam;

class ShippingOptionModel extends Model {

    public function getAllOfUser($userId) {
        $q = $this->db->prepare('SELECT * FROM shipping_options WHERE user_id = :user_id ORDER BY name ASC');
        $q->execute([':user_id' => $userId]);
        $options = $q->fetchAll();
        return $options ? $options : [];
    }

    public function createForUser($userId, $option) {
        $sql = 'INSERT INTO shipping_options (name, price, user_id) VALUES (:name, :price, :user_id)';
        $query = $this->db->prepare($sql);
        return $query->execute([':name' => $option->name,
            ':price' => floatval($option->price),
            ':user_id' => $userId]);
    }

    public function getOneOfUser($userId, $idHash, $sessionSecret) {
        $q = $this->db->prepare('SELECT * FROM shipping_options WHERE user_id = :user_id and SHA2(CONCAT(id, :session_secret), "256") = :id_hash LIMIT 1');
        $q->execute([':user_id' => $userId, ':session_secret' => $sessionSecret, ':id_hash' => $idHash]);
        $option = $q->fetch();
        return $option ? $option : null;
    }

    public function update($option) {
        $sql = 'UPDATE shipping_options SET name = :name, price = :price WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':name' => $option->name,
            ':price' => floatval($option->price),
            ':id' => $option->id]);
    }

    public function delete($id) {
        $q = $this->db->prepare('DELETE FROM shipping_options WHERE id = :id');
        return $q->execute([':id' => $id]);
    }

    public function getOfProduct($productId) {
        $q = $this->db->prepare('SELECT shipping_options.* FROM shipping_options ' .
            'JOIN products_shipping_options ON shipping_options.id = products_shipping_options.shipping_option_id ' .
            'WHERE products_shipping_options.product_id = :product_id ORDER BY name ASC');
        $q->execute([':product_id' => $productId]);
        $options = $q->fetchAll();
        return $options ? $options : [];
    }

    public function getUsingProducts($shippingOptionId) {
        $q = $this->db->prepare('SELECT products.* FROM products ' .
            'JOIN products_shipping_options ON products.id = products_shipping_options.product_id ' .
            'WHERE products_shipping_options.shipping_option_id = :shipping_option_id');
        $q->execute([':shipping_option_id' => $shippingOptionId]);
        $products = $q->fetchAll();
        return $products ? $products : [];
    }
}