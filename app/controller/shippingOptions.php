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
        $this->accessDeniedUnless(isset($this->get['id']) && ctype_digit($this->get['id']));

        # check that shipping option belongs to user
        $shippingOption = $this->getModel('ShippingOption');
        $option = $shippingOption->getOneOfUser($this->user->id, $this->get['id']);
        $this->notFoundUnless($option);

        $this->renderTemplate('shippingOptions/edit.php', ['shippingOption' => $option]);
    }

    public function update() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['id']) && ctype_digit($this->post['id']));
        $this->accessDeniedUnless(isset($this->post['name']) && is_string($this->post['name']) && mb_strlen($this->post['name']) >= 3);
        $this->accessDeniedUnless(isset($this->post['price']) && is_string($this->post['price']) && is_numeric($this->post['price']) && $this->post['price'] >= 0.0);

        # check that shipping option belongs to user
        $shippingOption = $this->getModel('ShippingOption');
        $option = $shippingOption->getOneOfUser($this->user->id, $this->post['id']);
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
        $this->accessDeniedUnless(isset($this->post['id']) && ctype_digit($this->post['id']));

        # check that shipping option belongs to user
        $shippingOption = $this->getModel('ShippingOption');
        $option = $shippingOption->getOneOfUser($this->user->id, $this->post['id']);
        $this->notFoundUnless($option);

        if($shippingOption->delete($this->post['id'])) {
            $this->setFlash('success', 'Successfully deleted shipping option.');
        }
        else {
            $this->setFlash('success', 'Unknown error, could not delete shipping option.');
        }

        $this->redirectTo('?c=shippingOptions');
    }
}