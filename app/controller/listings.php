<?php

namespace Scam;

class ListingsController extends Controller {
    public function index() {
        $this->renderTemplate('listings/index.php');
    }

    public function product() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->get['code']) && is_string($this->get['code']));
        $product = $this->getModel('Product')->getProduct($this->get['code']);
        $this->notFoundUnless($product);

        $this->renderTemplate('listings/product.php', ['product' => $product]);
    }

    public function productImage(){
        $img = $this->getModel('Product')->getImage($this->get['code']);
        if($img) {
            header("Content-Type: image/png");
            echo $img;
        }
    }
}