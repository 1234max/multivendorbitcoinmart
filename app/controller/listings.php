<?php

namespace Scam;

class ListingsController extends Controller {
    public function index() {
        $this->renderTemplate('listings/index.php');
    }
}