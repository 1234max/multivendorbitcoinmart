<?php

namespace Scam;

class ProductModel extends Model {

    public function getAllOfUser($userId) {
        $q = $this->db->prepare('SELECT * FROM products WHERE user_id = :user_id ORDER BY name ASC');

        $q->execute([':user_id' => $userId]);
        $products = $q->fetchAll();
        if($products) {
            # include shipping options
            $shippingOption = $this->getModel('ShippingOption');
            foreach($products as $product) {
                $product->shippingOptions = $shippingOption->getOfProduct($product->id);
            }
            return $products;
        }
        return [];
    }

    private function getFreeCode() {
        $code = null;
        do {
            $try = substr(sha1(rand()), 0, 12);
            $q = $this->db->prepare('SELECT id FROM products WHERE code = :code');

            $q->execute([':code' => $try]);
            if(!$q->fetch()) {
                $code = $try;
            }
        } while($code == null);

        return $code;
    }

    public function createForUser($userId, $product) {
        $this->db->beginTransaction();

        try {
            $sql = 'INSERT INTO products (name, price, tags, is_hidden, code, user_id) VALUES (:name, :price, :tags, :is_hidden, :code, :user_id)';
            $query = $this->db->prepare($sql);
            $result = $query->execute(array(':name' => $product->name,
                ':price' => floatval($product->price),
                ':tags' => $product->tags,
                ':is_hidden' => intval($product->is_hidden),
                ':code' => $this->getFreeCode(),
                ':user_id' => $userId));

            # create shipping option links
            if($result) {
                $productId = $this->db->lastInsertId();
                foreach($product->shippingOptions as $id => $shippingOption) {
                    $sql = 'INSERT INTO products_shipping_options (product_id, shipping_option_id) VALUES (:product_id, :shipping_option_id)';
                    $query = $this->db->prepare($sql);
                    $res = $query->execute(array(':product_id' => intval($productId),
                        ':shipping_option_id' => intval($id)));
                    if(!$res) {
                        throw new \Exception('Shipping option link couldnt be saved');
                    }
                }
            }
            else {
                throw new \Exception('Product couldnt be saved');
            }

            $this->db->commit();
            return true;
        }
        catch(\Exception $e){
            $this->db->rollBack();
            return false;
        }
    }

    public function getOneOfUser($userId, $id) {
        $q = $this->db->prepare('SELECT * FROM products WHERE user_id = :user_id and id = :id LIMIT 1');
        $q->execute([':user_id' => $userId, ':id' => $id]);
        $product = $q->fetch();
        if($product) {
            # include shipping options
            $shippingOption = $this->getModel('ShippingOption');
            $product->shippingOptions = $shippingOption->getOfProduct($product->id);
            return $product;
        }
        return null;

        return $product ? $product : null;
    }

    public function update($product) {
        $this->db->beginTransaction();

        try {
            $sql = 'UPDATE products SET name = :name, price = :price, tags = :tags, is_hidden = :is_hidden WHERE id = :id';
            $query = $this->db->prepare($sql);
            $result = $query->execute(array(':name' => $product->name,
                ':price' => floatval($product->price),
                ':tags' => $product->tags,
                ':is_hidden' => intval($product->is_hidden),
                ':id' => $product->id));

            # create shipping option links
            if($result) {
                # delete existing first
                $sql = 'DELETE FROM products_shipping_options WHERE product_id = :product_id';
                $query = $this->db->prepare($sql);
                $res = $query->execute(array(':product_id' => $product->id));
                if(!$res) {
                    throw new \Exception('Shipping option link couldnt be saved');
                }

                foreach($product->shippingOptions as $id => $shippingOption) {
                    $sql = 'INSERT INTO products_shipping_options (product_id, shipping_option_id) VALUES (:product_id, :shipping_option_id)';
                    $query = $this->db->prepare($sql);
                    $res = $query->execute(array(':product_id' => $product->id,
                        ':shipping_option_id' => intval($id)));
                    if(!$res) {
                        throw new \Exception('Shipping option link couldnt be saved');
                    }
                }
            }
            else {
                throw new \Exception('Product couldnt be saved');
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
        $q = $this->db->prepare('DELETE FROM products WHERE id = :id');
        return $q->execute([':id' => $id]);
    }
}