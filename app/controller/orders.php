<?php

namespace Scam;

class OrdersController extends Controller {
    public function index() {
        $orderModel = $this->getModel('Order');

        $unconfirmedOrders = $orderModel->getUnconfirmedOfUser($this->user->id, $this->user->is_vendor);
        $pendingOrders = $orderModel->getPendingOfUser($this->user->id, $this->user->is_vendor);
        $finishedOrders = $orderModel->getFinishedOfUser($this->user->id, $this->user->is_vendor);

        $this->renderTemplate('orders/index.php', ['unconfirmedOrders' => $unconfirmedOrders,
            'pendingOrders' => $pendingOrders,
            'finishedOrders' => $finishedOrders]);
    }

    # accessible for: buyer
    # creates a new order with product, amount & shipping order.
    public function create() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['product_code']) && is_string($this->post['product_code']));
        $this->accessDeniedUnless(isset($this->post['amount']) && ctype_digit($this->post['amount']) && $this->post['amount'] > 0);
        $this->accessDeniedUnless(isset($this->post['shipping_option_h']) && is_string($this->post['shipping_option_h']));

        # verify product
        $product = $this->getModel('Product')->getProduct($this->post['product_code']);
        $this->notFoundUnless($product);

        # verify shipping option
        $shippingOptions = array_filter($product->shippingOptions, function($s){return $this->h($s->id) == $this->post['shipping_option_h'];});
        $this->notFoundUnless(count($shippingOptions) == 1);
        $shippingOption = $shippingOptions[0];

        # calculate price & title
        $amount = intval($this->post['amount']);
        $price = ($product->price * $amount) + $shippingOption->price;
        $title = $amount . 'x ' . $product->name . ' (shipped with ' . $shippingOption->name . ')';

        $order = (object)['title' => $title,
            'price' => $price,
            'amount' => $amount,
            'buyer_id' => $this->user->id,
            'vendor_id' => $product->user_id,
            'product_id' => $product->id,
            'shipping_option_id' => $shippingOption->id];

        $success = false;
        $errorMessage = '';
        $orderId = 0;

        if($this->user->bip32_extended_public_key) {
            if (!$this->user->is_vendor) {
                if ($orderId = $this->getModel('Order')->create($order)) {
                    $success = true;
                } else {
                    $errorMessage = 'Could not create order due to unknown error.';
                }
            } else {
                $errorMessage = 'Vendors are not allowed to order products.';
            }
        } else {
            $errorMessage = 'Please set up BIP32 configuration first.';
        }

        # create in database
        if ($success) {
            $this->setFlash('success', 'Successfully created order.');
            $this->redirectTo('?c=orders&a=show&h=' . $this->h($orderId));
        } else {
            list($averageRating, $numberOfDeals) = $this->getModel('VendorFeedback')->getAverageAndDealsOfVendor($product->user_id);

            $this->renderTemplate('listings/product.php', ['product' => $product,
                'error' => $errorMessage, 'averageRating' => $averageRating,
                'numberOfDeals' => $numberOfDeals]);
        }
    }

    # accessible for: buyer & vendor
    # valid states: all
    # shows a order.
    public function show() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->get['h']) && is_string($this->get['h']));

        # check that order belongs to user
        $orderModel = $this->getModel('Order');
        $order = $orderModel->getOneOfUser($this->user->id, $this->user->is_vendor, $this->get['h'], $_SESSION['k']);
        $this->notFoundUnless($order);

        # make sure vendor cant access unconfirmed orders
        $this->notFoundIf($order->state == \Scam\OrderModel::$STATES['unconfirmed'] && $this->user->is_vendor);

        $this->renderTemplate('orders/show.php', ['order' => $order]);
    }

    # accessible for: buyer
    # valid states: only unconfirmed
    # confirms the order (profile pin & shipping info) required, now the vendor gets notified
    public function confirm() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['h']) && is_string($this->post['h']));
        $this->accessDeniedUnless(isset($this->post['shipping_info']) && is_string($this->post['shipping_info']) && mb_strlen($this->post['shipping_info']) >= 0);
        $this->accessDeniedUnless(isset($this->post['refund_address']) && is_string($this->post['refund_address']));
        $this->accessDeniedUnless(isset($this->post['profile_pin']) && is_string($this->post['profile_pin']) && mb_strlen($this->post['profile_pin']) >= 0);

        # check that order belongs to user
        $orderModel = $this->getModel('Order');
        $order = $orderModel->getOneOfUser($this->user->id, $this->user->is_vendor, $this->post['h'], $_SESSION['k']);
        $this->notFoundUnless($order);

        # only buyer allowed
        $this->accessDeniedIf($this->user->is_vendor);
        # only unconfirmed orders
        $this->accessDeniedUnless($order->state == \Scam\OrderModel::$STATES['unconfirmed']);

        $success = false;
        $errorMessage = '';

        # validate payout address
        if ($this->getModel('BitcoinTransaction')->isValidBitcoinAddress(trim($this->post['refund_address']))) {
            # check profile pin
            if ($this->getModel('User')->checkProfilePin($this->user->id, $this->post['profile_pin'])) {
                if ($orderModel->confirm($order, $this->post['shipping_info'], trim($this->post['refund_address']))) {
                    $success = true;
                } else {
                    $errorMessage = 'Could not confirm order due to unknown error.';
                }
            } else {
                $errorMessage = 'Profile pin wrong.';
            }
        }
        else {
            $errorMessage = 'Refund address is not a valid bitcoin address (27-35 hex chars).';
        }

        if($success) {
            $this->setFlash('success', 'Successfully confirmed order, vendor received it.');
            $this->redirectTo('?c=orders');
        }
        else {
            $this->renderTemplate('orders/show.php', ['order' => $order, 'error' => $errorMessage]);
        }
    }

    # accessible for: vendor
    # valid states: only confirmed
    # accepts the order, now the buyer has to pay
    public function accept() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['h']) && is_string($this->post['h']));
        $this->accessDeniedUnless(isset($this->post['payout_address']) && is_string($this->post['payout_address']));
        $this->accessDeniedUnless(isset($this->post['profile_pin']) && is_string($this->post['profile_pin']) && mb_strlen($this->post['profile_pin']) >= 0);

        # check that order belongs to user
        $orderModel = $this->getModel('Order');
        $order = $orderModel->getOneOfUser($this->user->id, $this->user->is_vendor, $this->post['h'], $_SESSION['k']);
        $this->notFoundUnless($order);

        # only vendor allowed
        $this->accessDeniedUnless($this->user->is_vendor);
        # only confirmed orders
        $this->accessDeniedUnless($order->state == \Scam\OrderModel::$STATES['confirmed']);

        $success = false;
        $errorMessage = '';

        # validate payout address
        if ($this->getModel('BitcoinTransaction')->isValidBitcoinAddress(trim($this->post['payout_address']))) {
            # check profile pin
            if($this->getModel('User')->checkProfilePin($this->user->id, $this->post['profile_pin'])) {
                if ($orderModel->accept($order, trim($this->post['payout_address']))) {
                    $success = true;
                } else {
                    $errorMessage = 'Could not accept order due to unknown error.';
                }
            }
            else {
                $errorMessage = 'Profile pin wrong.';

            }
        }
        else {
            $errorMessage = 'Payout address is not a valid bitcoin address (27-35 hex chars).';

        }

        if($success) {
            $this->setFlash('success', 'Successfully accepted order, now buyer has to pay.');
            $this->redirectTo('?c=orders');
        }
        else {
            $this->renderTemplate('orders/show.php', ['order' => $order, 'error' => $errorMessage]);
        }
    }

    # accessible for: vendor
    # valid states: only confirmed
    # declines (& finishes) the order with a decline message.
    public function decline() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['h']) && is_string($this->post['h']));
        $this->accessDeniedUnless(isset($this->post['profile_pin']) && is_string($this->post['profile_pin']) && mb_strlen($this->post['profile_pin']) >= 0);
        $this->accessDeniedUnless(isset($this->post['decline_message']) && is_string($this->post['decline_message']) && mb_strlen($this->post['decline_message']) >= 0);

        # check that order belongs to user
        $orderModel = $this->getModel('Order');
        $order = $orderModel->getOneOfUser($this->user->id, $this->user->is_vendor, $this->post['h'], $_SESSION['k']);
        $this->notFoundUnless($order);

        # only vendor allowed
        $this->accessDeniedUnless($this->user->is_vendor);
        # only confirmed orders
        $this->accessDeniedUnless($order->state == \Scam\OrderModel::$STATES['confirmed']);

        $success = false;
        $errorMessage = '';

        # check profile pin
        if($this->getModel('User')->checkProfilePin($this->user->id, $this->post['profile_pin'])) {
            if($orderModel->decline($order->id, $this->post['decline_message'])) {
                $success = true;
            }
            else {
                $errorMessage = 'Could not decline order due to unknown error.';
            }
        }
        else {
            $errorMessage = 'Profile pin wrong.';
        }

        if($success) {
            $this->setFlash('success', 'Successfully declined order.');
            $this->redirectTo('?c=orders');
        }
        else {
            $this->renderTemplate('orders/show.php', ['order' => $order, 'error' => $errorMessage]);
        }
    }

    # accessible for: vendor
    # valid states: only paid
    # vendor indicates that he has shipped
    public function shipped() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['h']) && is_string($this->post['h']));
        $this->accessDeniedUnless(isset($this->post['partially_signed_transaction']) && is_string($this->post['partially_signed_transaction']));

        # check that order belongs to user
        $orderModel = $this->getModel('Order');
        $order = $orderModel->getOneOfUser($this->user->id, $this->user->is_vendor, $this->post['h'], $_SESSION['k']);
        $this->notFoundUnless($order);

        # only vendor allowed
        $this->accessDeniedUnless($this->user->is_vendor);
        # only paid orders
        $this->accessDeniedUnless($order->state == \Scam\OrderModel::$STATES['paid']);

        $success = false;
        $errorMessage = '';

        # check if signed transaction is valid & partially signed
        if($this->getModel('BitcoinTransaction')->isValidSignedTransaction($order->unsigned_transaction, $this->post['partially_signed_transaction'])) {
            if($orderModel->shipped($order->id, $this->post['partially_signed_transaction'])) {
                $success = true;
            }
            else {
                $errorMessage = 'Could not set order to shipped due to unknown error.';
            }
        }
        else {
            $errorMessage = 'Raw transaction is in invalid format or not signed.';
        }

        if($success) {
            $this->setFlash('success', 'Successfully marked order as shipped.');
            $this->redirectTo('?c=orders');
        }
        else {
            $this->renderTemplate('orders/show.php', ['order' => $order, 'error' => $errorMessage]);
        }

    }

    # accessible for: buyer
    # valid states: only received
    # buyer can leave/update feedback
    public function feedback() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['h']) && is_string($this->post['h']));
        $this->accessDeniedUnless(isset($this->post['rating']) && ctype_digit($this->post['rating']) && in_array($this->post['rating'], range(1,5)));
        $this->accessDeniedUnless(isset($this->post['comment']) && is_string($this->post['comment']));

        # check that order belongs to user
        $orderModel = $this->getModel('Order');
        $order = $orderModel->getOneOfUser($this->user->id, $this->user->is_vendor, $this->post['h'], $_SESSION['k']);
        $this->notFoundUnless($order);

        # only buyer allowed
        $this->accessDeniedIf($this->user->is_vendor);

        $feedbackModel = $this->getModel('VendorFeedback');
        $feedback = $feedbackModel->getForOrder($order->id);
        # this will only exist when order was finished properly, so no need for state check
        $this->notFoundUnless($feedback);

        $success = false;
        $errorMessage = '';

        $feedback->rating = $this->post['rating'];
        $feedback->comment = $this->post['comment'];

        if($feedbackModel->update($feedback)) {
            $success = true;
        }
        else {
            $errorMessage = 'Could not leave feedback due to unknown error.';
        }

        if($success) {
            $this->setFlash('success', 'Successfully updated feedback.');
            $this->redirectTo('?c=orders&a=show&h=' . $this->post['h']);
        }
        else {
            $this->renderTemplate('orders/show.php', ['order' => $order, 'error' => $errorMessage]);
        }
    }

    # accessible for: buyer & vendor
    # valid states: unconfirmed (buyer), received (buyer & vendor after period)
    # order with its history is deleted
    public function destroy() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['h']) && is_string($this->post['h']));

        # check that order belongs to user
        $orderModel = $this->getModel('Order');
        $order = $orderModel->getOneOfUser($this->user->id, $this->user->is_vendor, $this->post['h'], $_SESSION['k']);
        $this->notFoundUnless($order && \Scam\OrderModel::isDeletable($order, $this->user->id));

        if($orderModel->delete($order->id)) {
            $this->setFlash('success', 'Successfully deleted order.');
            $this->redirectTo('?c=orders');
        }
        else {
            $this->renderTemplate('orders/show.php', ['order' => $order, 'error' => 'Unknown error, could not delete order.']);
        }
    }

    # accessible for: buyer & vendor
    # valid states: paid, shipped, dispute
    # order is set to state 'dispute', admin can resolve the dispute by creating a new transaction
    public function dispute() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['h']) && is_string($this->post['h']));
        $this->accessDeniedUnless(isset($this->post['dispute_message']) && is_string($this->post['dispute_message']) && mb_strlen($this->post['dispute_message']) > 0);

        # check that order belongs to user
        $orderModel = $this->getModel('Order');
        $order = $orderModel->getOneOfUser($this->user->id, $this->user->is_vendor, $this->post['h'], $_SESSION['k']);
        $this->notFoundUnless($order);

        # check order states
        $this->accessDeniedUnless(in_array($order->state, [\Scam\OrderModel::$STATES['paid'], \Scam\OrderModel::$STATES['shipped'], \Scam\OrderModel::$STATES['dispute']]));

        # append new message to already existing message
        $message = $order->dispute_message . "User " . $this->user->name . " at " . date(DATE_RFC850). ": " . $this->post['dispute_message'] . "\n\n";

        if($orderModel->dispute($order->id, $message)) {
            $this->setFlash('success', 'Successfully disputed order.');
            $this->redirectTo('?c=orders&a=show&h=' . $this->post['h']);
        }
        else {
            $this->renderTemplate('orders/show.php', ['order' => $order, 'error' => 'Unknown error, could not dispute order.']);
        }
    }
}