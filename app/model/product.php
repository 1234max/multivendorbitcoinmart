<?php

namespace Scam;

class ProductModel extends Model {

    public function getAllOfUser($userId, $withHidden = true) {
        $q = $this->db->prepare('SELECT id, name, price, user_id, tags, is_hidden, code FROM products ' .
            'WHERE user_id = :user_id ORDER BY name ASC');

        $q->execute([':user_id' => $userId]);
        $products = $q->fetchAll();

        if(!$withHidden) {
            $products = array_filter($products, function($p) { return !$p->is_hidden; });
        }

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

    public function getAllVisible($query, $sorting) {
        $sql = 'SELECT p.id, p.name, p.price, p.user_id, p.tags, p.is_hidden, p.code, u.name AS user '
            .'FROM products p JOIN users u ON p.user_id = u.id WHERE p.is_hidden = 0';

        $params = [];

        if(!empty($query)) {
            $sql .= ' AND (p.name LIKE :query OR u.name LIKE :query OR p.tags LIKE :query)';
            $params['query'] = "%$query%";
        }

        $sortingMap = [
            'date-asc' => 'p.id ASC',
            'date-desc' => 'p.id DESC',
            'price-asc' => 'p.price ASC',
            'price-desc' => 'p.price DESC',
            'name-asc' => 'p.name ASC',
            'name-desc' => 'p.name DESC',
        ];

        $sortSql = isset($sortingMap[$sorting]) ? $sortingMap[$sorting] : 'p.id DESC';

        $sql .= ' ORDER BY ' . $sortSql;

        $q = $this->db->prepare($sql);
        $q->execute($params);
        $products = $q->fetchAll();
        return $products ? $products : [];
    }

    private function getFreeCode() {
        $code = null;
        do {
            $try = substr($this->getRandomStr(), 0, 12);
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
            $sql = 'INSERT INTO products (name, description, price, tags, is_hidden, code, image, user_id) ' .
                'VALUES (:name, :description, :price, :tags, :is_hidden, :code, :image, :user_id)';
            $query = $this->db->prepare($sql);
            $query->bindValue(':name', $product->name);
            $query->bindValue(':description', $product->description);
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

    public function getOneOfUser($userId, $code) {
        $q = $this->db->prepare('SELECT id, name, description, price, user_id, tags, is_hidden, code, !ISNULL(image) as hasImage ' .
            'FROM products WHERE user_id = :user_id and code = :code LIMIT 1');
        $q->execute([':user_id' => $userId, ':code' => $code]);
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
            $sql = 'UPDATE products SET name = :name, description = :description, price = :price, tags = :tags, is_hidden = :is_hidden WHERE id = :id';
            if($product->image != null) {
                $sql = 'UPDATE products SET name = :name, description = :description, price = :price, tags = :tags, is_hidden = :is_hidden, image = :image WHERE id = :id';
            }
            $query = $this->db->prepare($sql);
            $query->bindValue(':name', $product->name);
            $query->bindValue(':description', $product->description);
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
        $q = $this->db->prepare('SELECT p.id, p.name, p.description, p.price, p.code, p.user_id, p.tags, u.name AS user FROM products p '.
            'JOIN users u ON p.user_id = u.id WHERE p.code = :code LIMIT 1');
        $q->execute([':code' => $code]);
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