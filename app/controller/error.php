<?php

namespace Scam;

class ErrorController extends Controller {

    protected function redirectToLoginIfNotLoggedIn() {
        # no redirect = no check for logged in user for this controller
    }

    public function notFound() {
        $this->renderTemplate('error/error.php', ['message' => 'Not found'], ['statusCode' => 404]);
    }
    public function accessDenied() {
        $this->renderTemplate('error/error.php', ['message' => 'Access denied'], ['statusCode' => 403]);
    }
    public function unknown() {
        $this->renderTemplate('error/error.php', ['message' => 'Unknown error'], ['statusCode' => 500]);
    }
}