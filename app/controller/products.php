<?php

namespace Scam;

class ProductsController extends Controller {
    public function __construct($db) {
        # make sure only vendors can access this controller
        parent::__construct($db);

        $this->accessDeniedUnless($this->user->is_vendor);
    }

    public function index() {
        $products = $this->getModel('Product')->getAllOfUser($this->user->id);
        $shippingOptions = $this->getModel('ShippingOption')->getAllOfUser($this->user->id);

        $this->renderTemplate('products/index.php', ['products' => $products, 'hasShippingOptions' => !empty($shippingOptions)]);
    }

    public function build() {
        $product = (object)['name' => '', 'price' => '0.0', 'tags' => '', 'is_hidden' => 0];
        $shippingOptions = $this->getModel('ShippingOption')->getAllOfUser($this->user->id);

        $this->renderTemplate('products/build.php', ['product' => $product, 'shippingOptions' => $shippingOptions]);
    }

    public function create() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['name']) && is_string($this->post['name']) && mb_strlen($this->post['name']) >= 3);
        $this->accessDeniedUnless(isset($this->post['price']) && is_string($this->post['price']) && is_numeric($this->post['price']) && $this->post['price'] >= 0.0);
        $this->accessDeniedUnless(isset($this->post['tags']) && is_string($this->post['tags']));

        $shippingOptions = $this->getModel('ShippingOption')->getAllOfUser($this->user->id);

        # verify shipping options
        $validShippingOptions = [];

        if(isset($this->post['shipping_options']) && is_array($this->post['shipping_options'])){
            foreach($shippingOptions as $shippingOption) {
                if(in_array($shippingOption->id, $this->post['shipping_options'])) {
                    $validShippingOptions[$shippingOption->id] = $shippingOption;
                }
            }
        }

        $product = (object)['name' => $this->post['name'],
            'price' => $this->post['price'],
            'tags' => $this->post['tags'],
            'is_hidden' => isset($this->post['is_hidden']) ? 1 : 0,
            'shippingOptions' => $validShippingOptions];


        $errorMessage = '';

        # verify image
        $product->image = $this->handleImage($errorMessage);

        $success = false;

        if(empty($errorMessage)){
            if(!empty($product->shippingOptions)) {
                $productModel = $this->getModel('product');

                if ($productModel->createForUser($this->user->id, $product)) {
                    $success = true;
                } else {
                    $errorMessage = 'Could not create product due to unknown error.';
                }
            }
            else {
                $errorMessage = 'Please specify at least one valid shipping option.';
            }
        }

        if($success) {
            $this->setFlash('success', 'Successfully created product.');
            $this->redirectTo('?c=products');
        }
        else {
            $this->renderTemplate('products/build.php', ['product' => $product,
                'shippingOptions' => $shippingOptions,
                'error' => $errorMessage ]);
        }
    }

    public function edit() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->get['id']) && ctype_digit($this->get['id']));

        # check that shipping option belongs to user
        $productModel = $this->getModel('product');
        $product = $productModel->getOneOfUser($this->user->id, $this->get['id']);
        $this->notFoundUnless($product);

        $shippingOptions = $this->getModel('ShippingOption')->getAllOfUser($this->user->id);

        $this->renderTemplate('products/edit.php', ['product' => $product, 'shippingOptions' => $shippingOptions]);
    }

    public function update() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['id']) && ctype_digit($this->post['id']));
        $this->accessDeniedUnless(isset($this->post['name']) && is_string($this->post['name']) && mb_strlen($this->post['name']) >= 3);
        $this->accessDeniedUnless(isset($this->post['price']) && is_string($this->post['price']) && is_numeric($this->post['price']) && $this->post['price'] >= 0.0);
        $this->accessDeniedUnless(isset($this->post['tags']) && is_string($this->post['tags']));

        # check that product belongs to user
        $productModel = $this->getModel('product');
        $product = $productModel->getOneOfUser($this->user->id, $this->post['id']);
        $this->notFoundUnless($product);

        $shippingOptions = $this->getModel('ShippingOption')->getAllOfUser($this->user->id);

        # verify shipping options
        $validShippingOptions = [];

        if(isset($this->post['shipping_options']) && is_array($this->post['shipping_options'])){
            foreach($shippingOptions as $shippingOption) {
                if(in_array($shippingOption->id, $this->post['shipping_options'])) {
                    $validShippingOptions[$shippingOption->id] = $shippingOption;
                }
            }
        }

        $product->name = $this->post['name'];
        $product->price = $this->post['price'];
        $product->tags = $this->post['tags'];
        $product->is_hidden = isset($this->post['is_hidden']) ? 1 : 0;
        $product->shippingOptions = $validShippingOptions;

        $errorMessage = '';

        # verify image
        $product->image = $this->handleImage($errorMessage);

        $success = false;

        if(empty($errorMessage)) {
            if(!empty($product->shippingOptions)) {
                $productModel = $this->getModel('product');

                if ($productModel->update($product)) {
                    $success = true;
                } else {
                    $errorMessage = 'Could not update product due to unknown error.';
                }
            }
            else {
                $errorMessage = 'Please specify at least one valid shipping option.';
            }
        }

        if($success) {
            $this->setFlash('success', 'Successfully updated product.');
            $this->redirectTo('?c=products');
        }
        else {
            $this->renderTemplate('products/edit.php', ['product' => $product,
                'shippingOptions' => $shippingOptions,
                'error' => $errorMessage ]);
        }
    }

    public function destroyImage() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->get['id']) && ctype_digit($this->get['id']));

        # check that product belongs to user
        $productModel = $this->getModel('product');
        $product = $productModel->getOneOfUser($this->user->id, $this->get['id']);
        $this->notFoundUnless($product);

        if($productModel->deleteImage($this->get['id'])) {
            $this->setFlash('success', 'Successfully deleted product image.');
        }
        else {
            $this->setFlash('success', 'Unknown error, could not delete product image.');
        }

        $this->redirectTo('?c=products&a=edit&id=' . $this->get['id']);
    }

    public function destroy() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['id']) && ctype_digit($this->post['id']));

        # check that product belongs to user
        $productModel = $this->getModel('product');
        $product = $productModel->getOneOfUser($this->user->id, $this->post['id']);
        $this->notFoundUnless($product);

        if($productModel->delete($this->post['id'])) {
            $this->setFlash('success', 'Successfully deleted product.');
        }
        else {
            $this->setFlash('success', 'Unknown error, could not delete product.');
        }

        $this->redirectTo('?c=products');
    }

    private function handleImage(&$errorMessage) {
        if(isset($this->files['image']) && $this->files['image']['tmp_name'] && is_readable($this->files['image']['tmp_name'])) {
            # check size

            # check type

            # strip

            # convert & resize
            return fopen($this->files['image']['tmp_name'], 'rb');
        }
        return null;
    }
}