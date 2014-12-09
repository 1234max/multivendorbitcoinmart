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
        $product = (object)['name' => '', 'description' => '', 'price' => '0.0', 'tags' => '', 'is_hidden' => 0];
        $shippingOptions = $this->getModel('ShippingOption')->getAllOfUser($this->user->id);

        $this->renderTemplate('products/build.php', ['product' => $product, 'shippingOptions' => $shippingOptions]);
    }

    public function create() {
        $this->accessDeniedUnless($this->user->bip32_extended_public_key);

        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['name']) && is_string($this->post['name']) && mb_strlen($this->post['name']) >= 3);
        $this->accessDeniedUnless(isset($this->post['description']) && is_string($this->post['description']) && mb_strlen($this->post['description']) >= 0);
        $this->accessDeniedUnless(isset($this->post['price']) && is_string($this->post['price']) && is_numeric($this->post['price']) && $this->post['price'] >= 0.0);
        $this->accessDeniedUnless(isset($this->post['tags']) && is_string($this->post['tags']));

        $shippingOptions = $this->getModel('ShippingOption')->getAllOfUser($this->user->id);

        # verify shipping options
        $validShippingOptions = [];

        if(isset($this->post['shipping_options']) && is_array($this->post['shipping_options'])){
            foreach($shippingOptions as $shippingOption) {
                if(in_array($this->h($shippingOption->id), $this->post['shipping_options'])) {
                    $validShippingOptions[$shippingOption->id] = $shippingOption;
                }
            }
        }

        $product = (object)['name' => $this->post['name'],
            'description' => $this->post['description'],
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
                $productModel = $this->getModel('Product');

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
        $this->accessDeniedUnless(isset($this->get['code']) && is_string($this->get['code']));

        # check that product belongs to user
        $productModel = $this->getModel('Product');
        $product = $productModel->getOneOfUser($this->user->id, $this->get['code']);
        $this->notFoundUnless($product);

        $shippingOptions = $this->getModel('ShippingOption')->getAllOfUser($this->user->id);

        $this->renderTemplate('products/edit.php', ['product' => $product, 'shippingOptions' => $shippingOptions]);
    }

    public function update() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['code']) && is_string($this->post['code']));
        $this->accessDeniedUnless(isset($this->post['name']) && is_string($this->post['name']) && mb_strlen($this->post['name']) >= 3);
        $this->accessDeniedUnless(isset($this->post['description']) && is_string($this->post['description']) && mb_strlen($this->post['description']) >= 0);
        $this->accessDeniedUnless(isset($this->post['price']) && is_string($this->post['price']) && is_numeric($this->post['price']) && $this->post['price'] >= 0.0);
        $this->accessDeniedUnless(isset($this->post['tags']) && is_string($this->post['tags']));

        # check that product belongs to user
        $productModel = $this->getModel('Product');
        $product = $productModel->getOneOfUser($this->user->id, $this->post['code']);
        $this->notFoundUnless($product);

        $shippingOptions = $this->getModel('ShippingOption')->getAllOfUser($this->user->id);

        # verify shipping options
        $validShippingOptions = [];

        if(isset($this->post['shipping_options']) && is_array($this->post['shipping_options'])){
            foreach($shippingOptions as $shippingOption) {
                if(in_array($this->h($shippingOption->id), $this->post['shipping_options'])) {
                    $validShippingOptions[$shippingOption->id] = $shippingOption;
                }
            }
        }

        $product->name = $this->post['name'];
        $product->description = $this->post['description'];
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
                $productModel = $this->getModel('Product');

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
        $this->accessDeniedUnless(isset($this->get['code']) && is_string($this->get['code']));

        # check that product belongs to user
        $productModel = $this->getModel('Product');
        $product = $productModel->getOneOfUser($this->user->id, $this->get['code']);
        $this->notFoundUnless($product);

        if($productModel->deleteImage($product->id)) {
            $this->setFlash('success', 'Successfully deleted product image.');
        }
        else {
            $this->setFlash('success', 'Unknown error, could not delete product image.');
        }

        $this->redirectTo('?c=products&a=edit&code=' . $this->get['code']);
    }

    public function destroy() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['code']) && is_string($this->post['code']));

        # check that product belongs to user
        $productModel = $this->getModel('Product');
        $product = $productModel->getOneOfUser($this->user->id, $this->post['code']);
        $this->notFoundUnless($product);

        if($productModel->delete($product->id)) {
            $this->setFlash('success', 'Successfully deleted product.');
        }
        else {
            $this->setFlash('error', 'Unknown error, could not delete product.');
        }

        $this->redirectTo('?c=products');
    }

    private function handleImage(&$errorMessage) {
        if(isset($this->files['image']) && isset($this->files['image']['error']) && $this->files['image']['error'] != UPLOAD_ERR_NO_FILE) {
            if($this->files['image']['error'] == UPLOAD_ERR_FORM_SIZE || $this->files['image']['error'] == UPLOAD_ERR_INI_SIZE) {
                $errorMessage = 'Image size must not be bigger than 5MB.';
                return null;
            }

            # check for php errors
            if($this->files['image']['error'] != UPLOAD_ERR_OK) {
                $errorMessage = 'Upload error. Try again.';
                return null;
            }

            # check size
            if($this->files['image']['size'] > 5242880) { # 5MB
                $errorMessage = 'Image size must not be bigger than 5MB.';
                return null;
            }

            $tmpPath = $this->files['image']['tmp_name'];

            # check type
            $format = rtrim(shell_exec('identify -format "%m" '.escapeshellarg($tmpPath)));
            if(!in_array($format, ['JPEG', 'PNG', 'GIF'])) {
                $errorMessage = 'Image format must be JPEG, PNG or GIF.';
                return null;
            }

            # strip metadata & comments
            $ret = -1;
            $output = [];
            exec('mogrify -strip '.escapeshellarg($tmpPath), $output, $ret);
            if($ret !== 0) {
                $errorMessage = 'Error while handling image. Please try another or save in other format.';
                return null;
            }

            # resize & convert to jpeg (with overwritting file)
            $ret = -1;
            $output = [];
            # flatten makes png backgrounds white
            exec('convert ' . escapeshellarg($tmpPath) . ' -flatten -quality 95 -geometry 640x480 jpg:'.escapeshellarg($tmpPath), $output, $ret);
            if($ret !== 0) {
                $errorMessage = 'Error while handling image. Please try another or save in other format.';
                return null;
            }

            return fopen($tmpPath, 'rb');
        }
        return null;
    }
}