<?php

namespace Scam;

class AdminController extends Controller {

    protected function redirectToLoginIfNotLoggedIn()
    {
        # dont allow logged in user
        if($this->isUserLoggedIn()) {
            $this->setFlash('error', 'Please logout first.');
            $this->redirectTo('?');
        }

        # but check if admin is logged in
        if(!isset($_SESSION['is_admin']) && $this->action != 'index' && $this->action != 'doLogin') {
            $this->redirectTo('?c=admin');
        }
    }

    public function index() {
        if(isset($_SESSION['is_admin'])) {
            $this->redirectTo('?c=admin&a=disputes');
        }

        # get a random sign for challenge/response login (using bitcoin pubkey crypto)
        $_SESSION['random_str'] = $this->getModel('User')->getRandomStr();
        $this->renderTemplate('admin/index.php');
    }

    public function doLogin() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['signature']) && is_string($this->post['signature']));
        $this->accessDeniedUnless(isset($_SESSION['random_str']));
        $this->accessDeniedIf(isset($_SESSION['is_admin']));

        # verify provide bitcoin signature
        if($this->getModel('User')->checkAdminLogin($_SESSION['random_str'], $this->post['signature'])) {
            $_SESSION['is_admin'] = true;
            unset($_SESSION['random_str']);
            session_regenerate_id(true);

            $this->setFlash('success', 'Successfully logged in.');
            $this->redirectTo('?c=admin&a=disputes');
        }
        else {
            # new challenge
            $_SESSION['random_str'] = $this->getModel('User')->getRandomStr();
            $this->renderTemplate('admin/index.php', ['error' => 'Verification failed.']);
        }
    }

    public function logout() {
        $this->clearSession();

        $this->redirectTo('?');
    }

    public function disputes() {
        $this->renderTemplate('admin/disputes.php', ['disputes' => $this->getModel('Order')->getDisputesForAdmin()]);
    }

    public function showDispute(){
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->get['id']) && ctype_digit($this->get['id']));

        $orderModel = $this->getModel('Order');
        $dispute = $orderModel->getDisputeForAdmin($this->get['id']);
        $this->notFoundUnless($dispute);

        $this->renderTemplate('admin/showDispute.php', ['dispute' => $dispute]);
    }

    public function addDisputeMessage() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['id']) && ctype_digit($this->post['id']));
        $this->accessDeniedUnless(isset($this->post['dispute_message']) && is_string($this->post['dispute_message']) && mb_strlen($this->post['dispute_message']) >= 0);

        $orderModel = $this->getModel('Order');
        $dispute = $orderModel->getDisputeForAdmin($this->post['id']);
        $this->notFoundUnless($dispute);

        $disputeMessage = $dispute->dispute_message . "Admin at " . date(DATE_RFC850). ": " . $this->post['dispute_message'] . "\n\n";

        if($orderModel->dispute($dispute->id, $disputeMessage)) {
            $this->setFlash('success', 'Successfully updated dispute.');
            $this->redirectTo('?c=admin&a=showDispute&id=' . $dispute->id);
        }
        else {
            $this->renderTemplate('admin/showDispute.php', ['dispute' => $dispute, 'error' => 'Could not dispute order due to unknown error.']);
        }
    }

    public function createNewTransaction() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['id']) && ctype_digit($this->post['id']));
        $this->accessDeniedUnless(isset($this->post['vendor_refund']) && is_numeric($this->post['vendor_refund']) && $this->post['vendor_refund'] >= 0);
        $this->accessDeniedUnless(isset($this->post['buyer_refund']) && is_numeric($this->post['buyer_refund']) && $this->post['buyer_refund'] >= 0);

        $orderModel = $this->getModel('Order');
        $dispute = $orderModel->getDisputeForAdmin($this->post['id']);
        $this->notFoundUnless($dispute);

        $success = false;
        $errorMessage = '';

        # check that new refunds match order price
        if($this->formatPrice(floatval($this->post['vendor_refund']) + floatval($this->post['buyer_refund'])) == $this->formatPrice($dispute->price)) {
            $disputeMessage = $dispute->dispute_message . "Admin at " . date(DATE_RFC850). ": new transaction, vendor: "
                . $this->formatPrice($this->post['vendor_refund'])
                . "; buyer: " . $this->formatPrice($this->post['buyer_refund'])
                . " \n\n";

            if($orderModel->createNewTransactionForDispute($dispute->id, $disputeMessage,
                [
                    $dispute->vendor_payout_address => floatval($this->post['vendor_refund']),
                    $dispute->buyer_refund_address => floatval($this->post['buyer_refund'])
                ])) {
                $success = true;
            }
            else {
                $errorMessage = 'Could not dispute order due to unknown error.';
            }
        }
        else {
            $errorMessage = 'Refunds do not add up to order price.';

        }

        if($success) {
            $this->setFlash('success', 'Successfully create new transaction, please sign below.');
            $this->redirectTo('?c=admin&a=showDispute&id=' . $dispute->id);
        }
        else {
            $this->renderTemplate('admin/showDispute.php', ['dispute' => $dispute, 'error' => $errorMessage]);
        }
    }

    public function enterSignedTransaction() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['id']) && is_string($this->post['id']));
        $this->accessDeniedUnless(isset($this->post['partially_signed_transaction']) && is_string($this->post['partially_signed_transaction']));

        $orderModel = $this->getModel('Order');
        $dispute = $orderModel->getDisputeForAdmin($this->post['id']);
        $this->notFoundUnless($dispute);

        $success = false;
        $errorMessage = '';

        # check if signed transaction is valid & partially signed
        if($this->getModel('BitcoinTransaction')->isValidSignedTransaction($dispute->dispute_unsigned_transaction, $this->post['partially_signed_transaction'])) {
            if($orderModel->enterSignedTransactionForDispute($dispute->id, $this->post['partially_signed_transaction'])) {
                $success = true;
            }
            else {
                $errorMessage = 'Could not enter transation due to unknown error.';
            }
        }
        else {
            $errorMessage = 'Raw transaction is in invalid format or not signed.';
        }

        if($success) {
            $this->setFlash('success', 'Successfully entered signed transaction.');
            $this->redirectTo('?c=admin&a=showDispute&id=' . $dispute->id);
        }
        else {
            $this->renderTemplate('admin/showDispute.php', ['dispute' => $dispute, 'error' => $errorMessage]);
        }
    }
}