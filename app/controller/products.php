<?php

namespace Scam;

class ProductsController extends Controller {
    public function __construct($db) {
        # make sure only vendors can access this controller
        parent::__construct($db);

        $this->accessDeniedUnless($this->user->is_vendor);
    }

    public function index() {
        $this->renderTemplate('products/index.php');
    }
}