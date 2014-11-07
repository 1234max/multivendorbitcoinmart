<?php

namespace Scam;

class OrdersController extends Controller {
    public function index() {
        $this->renderTemplate('orders/index.php');
    }
}