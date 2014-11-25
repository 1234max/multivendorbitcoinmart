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
        list($averageRating, $numberOfDeals) = $this->getModel('VendorFeedback')->getAverageAndDealsOfVendor($product->user_id);

        $this->renderTemplate('listings/product.php', ['product' => $product,  'averageRating' => $averageRating,
            'numberOfDeals' => $numberOfDeals]);
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
        $this->accessDeniedUnless(isset($this->get['u']) && is_string($this->get['u']));
        $user = $this->getModel('User')->getWithHash($this->get['u']);
        $this->notFoundUnless($user && $user->is_vendor);
        $products = $this->getModel('Product')->getAllOfUser($user->id, false);
        list($averageRating, $numberOfDeals) = $this->getModel('VendorFeedback')->getAverageAndDealsOfVendor($user->id);
        $feedbacks = $this->getModel('VendorFeedback')->getAllOfVendor($user->id);

        $this->renderTemplate('listings/vendor.php', ['vendor' => $user, 'products' => $products,
            'averageRating' => $averageRating, 'numberOfDeals' => $numberOfDeals, 'feedbacks' => $feedbacks]);
    }
}