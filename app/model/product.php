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
            $sql = 'INSERT INTO products (name, price, tags, is_hidden, code, image, user_id) VALUES (:name, :price, :tags, :is_hidden, :code, :image, :user_id)';
            $query = $this->db->prepare($sql);
            $query->bindValue(':name', $product->name);
            $query->bindValue(':price', floatval($product->price));
            $query->bindValue(':tags', $product->tags);
            $query->bindValue(':is_hidden', intval($product->is_hidden));
            $code = $this->getFreeCode();
            $query->bindValue(':code', $code);
            $query->bindParam(':image', $product->image, \PDO::PARAM_LOB);
            $query->bindValue(':user_id', $userId);

            $result = $query->execute();

            # create shipping option links
            if($result) {
                $productId = $this->db->lastInsertId();
                foreach($product->shippingOptions as $id => $shippingOption) {
                    $sql = 'INSERT INTO products_shipping_options (product_id, shipping_option_id) VALUES (:product_id, :shipping_option_id)';
                    $query = $this->db->prepare($sql);
                    $res = $query->execute([':product_id' => intval($productId),
                        ':shipping_option_id' => intval($id)]);
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
        $q = $this->db->prepare('SELECT id, name, price, user_id, tags, is_hidden, code, !ISNULL(image) as hasImage ' .
            'FROM products WHERE user_id = :user_id and id = :id LIMIT 1');
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
            # dont update image if not a new one is given
            $sql = 'UPDATE products SET name = :name, price = :price, tags = :tags, is_hidden = :is_hidden WHERE id = :id';
            if($product->image != null) {
                $sql = 'UPDATE products SET name = :name, price = :price, tags = :tags, is_hidden = :is_hidden, image = :image WHERE id = :id';
            }
            $query = $this->db->prepare($sql);
            $query->bindValue(':name', $product->name);
            $query->bindValue(':price', floatval($product->price));
            $query->bindValue(':tags', $product->tags);
            $query->bindValue(':is_hidden', intval($product->is_hidden));
            $query->bindValue(':id', $product->id);
            if($product->image != null) {
                $query->bindParam(':image', $product->image, \PDO::PARAM_LOB);
            }
            $result = $query->execute();

            # create shipping option links
            if($result) {
                # delete existing first
                $sql = 'DELETE FROM products_shipping_options WHERE product_id = :product_id';
                $query = $this->db->prepare($sql);
                $res = $query->execute([':product_id' => $product->id]);
                if(!$res) {
                    throw new \Exception('Shipping option link couldnt be saved');
                }

                foreach($product->shippingOptions as $id => $shippingOption) {
                    $sql = 'INSERT INTO products_shipping_options (product_id, shipping_option_id) VALUES (:product_id, :shipping_option_id)';
                    $query = $this->db->prepare($sql);
                    $res = $query->execute([':product_id' => $product->id,
                        ':shipping_option_id' => intval($id)]);
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

    public function getProduct($code) {
        $q = $this->db->prepare('SELECT name, code, !ISNULL(image) as hasImage FROM products WHERE code = :code LIMIT 1');
        $q->execute([':code' => $code]);
        $product = $q->fetch();
        return $product ? $product : null;
    }

    public function getImage($code) {
        $q = $this->db->prepare('SELECT image FROM products WHERE code = :code LIMIT 1');

        $image = null;
        $q->bindColumn(1, $image, \PDO::PARAM_LOB);
        $q->execute([':code' => $code]);
        $ret = $q->fetch(\PDO::FETCH_BOUND);
        return $ret ? $image : null;
    }

    public function deleteImage($productId) {
        $sql = 'UPDATE products SET image = NULL WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $productId]);
    }
}