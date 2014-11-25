<?php

namespace Scam;

class ShippingOptionsController extends Controller {
    public function __construct($db) {
        # make sure only vendors can access this controller
        parent::__construct($db);

        $this->accessDeniedUnless($this->user->is_vendor);
    }

    public function index() {
        $options = $this->getModel('ShippingOption')->getAllOfUser($this->user->id);

        $this->renderTemplate('shippingOptions/index.php', ['shippingOptions' => $options]);
    }

    public function build() {
        $option = (object)['name' => '', 'price' => '0.0'];

        $this->renderTemplate('shippingOptions/build.php', ['shippingOption' => $option]);
    }

    public function create() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['name']) && is_string($this->post['name']) && mb_strlen($this->post['name']) >= 3);
        $this->accessDeniedUnless(isset($this->post['price']) && is_string($this->post['price']) && is_numeric($this->post['price']) && $this->post['price'] >= 0.0);

        $option = (object)['name' => $this->post['name'], 'price' => $this->post['price']];
        $shippingOption = $this->getModel('ShippingOption');

        if($shippingOption->createForUser($this->user->id, $option)) {
            $this->setFlash('success', 'Successfully created shipping option.');
            $this->redirectTo('?c=shippingOptions');
        }
        else {
            $this->renderTemplate('shippingOptions/build.php', ['shippingOption' => $option, 'error' => 'Could not create shipping option due to unknown error.']);
        }
    }

    public function edit() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->get['h']) && is_string($this->get['h']));

        # check that shipping option belongs to user
        $shippingOption = $this->getModel('ShippingOption');
        $option = $shippingOption->getOneOfUser($this->user->id, $this->get['h'], $_SESSION['k']);
        $this->notFoundUnless($option);

        $this->renderTemplate('shippingOptions/edit.php', ['shippingOption' => $option]);
    }

    public function update() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['h']) && is_string($this->post['h']));
        $this->accessDeniedUnless(isset($this->post['name']) && is_string($this->post['name']) && mb_strlen($this->post['name']) >= 3);
        $this->accessDeniedUnless(isset($this->post['price']) && is_string($this->post['price']) && is_numeric($this->post['price']) && $this->post['price'] >= 0.0);

        # check that shipping option belongs to user
        $shippingOption = $this->getModel('ShippingOption');
        $option = $shippingOption->getOneOfUser($this->user->id, $this->post['h'], $_SESSION['k']);
        $this->notFoundUnless($option);

        $option->name = $this->post['name'];
        $option->price = $this->post['price'];

        if($shippingOption->update($option)) {
            $this->setFlash('success', 'Successfully updated shipping option.');
            $this->redirectTo('?c=shippingOptions');
        }
        else {
            $this->renderTemplate('shippingOptions/edit.php', ['shippingOption' => $option, 'error' => 'Could not update shipping option due to unknown error.']);
        }

        $this->redirectTo('?c=shippingOptions');
    }

    public function destroy() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['h']) && is_string($this->post['h']));

        # check that shipping option belongs to user
        $shippingOption = $this->getModel('ShippingOption');
        $option = $shippingOption->getOneOfUser($this->user->id, $this->post['h'], $_SESSION['k']);
        $this->notFoundUnless($option);

        $success = false;
        $errorMessage = '';

        # check that there are no products using this
        $usingProducts = $shippingOption->getUsingProducts($option->id);

        if(empty($usingProducts)) {
            if($shippingOption->delete($option->id)) {
                $success = true;
            }
            else {
                $errorMessage = 'Unknown error while deleting shipping options';
            }
        }
        else {
            $productNames = join(array_map(function($v){return $v->name; }, $usingProducts), ', ');
            $errorMessage = "Shipping option is still in use by products $productNames. Please unassign first.";
        }

        if($success) {
            $this->setFlash('success', 'Successfully deleted shipping option.');
        }
        else {
            $this->setFlash('error', $errorMessage);
        }
        $this->redirectTo('?c=shippingOptions');
    }
}