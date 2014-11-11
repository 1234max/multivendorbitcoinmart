<?php

namespace Scam;

class VendorController extends Controller {
    public function __construct($db) {
        # make sure only vendors can access this controller
        parent::__construct($db);

        $this->accessDeniedUnless($this->user->is_vendor);
    }

    public function multisig() {
        $this->renderTemplate('vendor/multisig.php');
    }
}