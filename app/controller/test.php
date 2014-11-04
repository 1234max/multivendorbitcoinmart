<?php

namespace Scam;

class TestController extends Controller {
    public function index() {
        $m = $this->getModel('Test');
        $tests = $m->getTests();
        $this->renderTemplate('test/index.php', ['bla' => $tests]);
    }
}