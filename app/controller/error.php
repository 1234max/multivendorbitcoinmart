<?php

namespace Scam;

class ErrorController extends Controller {
    public function error404() {
        $this->renderTemplate('error/error.php', ['message' => 'Not found'], ['statusCode' => 404]);
    }
}