<?php

namespace Scam;

class ListingsController extends Controller {
    public function index() {
        $query = '';
        if(isset($this->get['q']) && is_string($this->get['q'])) {
            $query = $this->get['q'];
        }

        $sorting = isset($this->get['sort']) ? $this->get['sort'] : 'date-desc';

        $products = $this->getModel('Product')->getAllVisible($query, $sorting);

        $orderModel = $this->getModel('Order');
        $unconfirmedOrders = $orderModel->getUnconfirmedOfUser($this->user->id, $this->user->is_vendor);
        $orderNeedingActions = $orderModel->getNeededActionsOfUser($this->user->id, $this->user->is_vendor);

        $this->renderTemplate('listings/index.php', ['products' => $products,
            'query' => $query,
            'sorting' => $sorting,
            'unconfirmedOrders' => $unconfirmedOrders,
            'orderNeedingActions' => $orderNeedingActions]);
    }

    public function product() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->get['code']) && is_string($this->get['code']));
        $product = $this->getModel('Product')->getProduct($this->get['code']);
        $this->notFoundUnless($product);

        $this->renderTemplate('listings/product.php', ['product' => $product]);
    }

    public function productImage(){
        $this->accessDeniedUnless(isset($this->get['code']) && is_string($this->get['code']));
        $img = $this->getModel('Product')->getImage($this->get['code']);
        if($img) {
            header("Content-Type: image/jpeg");
            echo $img;
        }
        else {
            $this->redirectTo('/img/no_picture.gif');
        }
    }

    public function vendor() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->get['id']) && ctype_digit($this->get['id']));
        $user = $this->getModel('User')->getUser($this->get['id']);
        $products = $this->getModel('Product')->getAllOfUser($user->id, false);

        $this->notFoundUnless($user && $user->is_vendor);

        $this->renderTemplate('listings/vendor.php', ['vendor' => $user, 'products' => $products]);
    }
}