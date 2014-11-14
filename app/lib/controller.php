<?php

namespace Scam;

/**
 * Class Controller
 * @package Scam
 * @author Matthias Winzeler <matthias.winzeler@gmail.com>
 *
 * Base class for all application controllers.
 * Child controllers (see app/controller) use in their actions
 * the methods getModel to obtain a database model, do the business logic on it and then use
 * renderTemplate to render a view (with passing parameters to it) to the user.
 *
 * Some more helpful functions for access control, redirection & flashes are available, too.
 */
class Controller {
    private $db = null;
    protected $get = [];
    protected $post = [];
    protected $files = [];
    protected $isPost = false;
    protected $controller = '';
    protected $action = '';
    protected $user = null;
    private $flashes = [];

    public function __construct($db) {
        $this->db = $db;

        if (session_status() == PHP_SESSION_NONE) {
            session_name('scam');
            session_start();
            if(!isset($_SESSION['flashes'])) {
                $_SESSION['flashes'] = [];
            }
        }
        # shortcuts to use in templates:
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = isset($_FILES) ? $_FILES : [];

        $this->controller = isset($this->get['c']) ? $this->get['c'] : 'listings';
        $this->action = isset($this->get['a']) ? $this->get['a'] : 'index';

        # fetch user from session
        if(isset($_SESSION['user_id'])){
            $this->user = $this->getModel('User')->getUser($_SESSION['user_id']);
        }

        # by default, all our controllers can only be invoked by logged in users
        $this->redirectToLoginIfNotLoggedIn();
    }

    protected function isUserLoggedIn() {
        return $this->user != null;
    }

    protected function redirectToLoginIfNotLoggedIn() {
        if(!$this->isUserLoggedIn()) {
            $this->redirectTo('?c=users&a=login');
        }
    }

    /* shortcut to escape strings in template */
    protected function e($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'utf-8');
    }

    protected function formatPrice($decimal) {
        return floatval($decimal). ' BTC';
    }

    protected function renderTemplate($template, $vars = [], $options = []) {
        header('Content-Type: text/html; charset=UTF-8');

        if(isset($options['statusCode'])) {
            http_response_code($options['statusCode']);
        }

        # make $vars directly usable in view
        extract($vars, EXTR_SKIP);

        # render view
        ob_start();
        require '../app/views/' . $template;
        $content = ob_get_clean();

        # render layout
        $layout = isset($options['layout']) ? $options['layout'] : 'default.php';
        require '../app/views/_layout/' . $layout;

        $this->prepareFlashesForNextRequest();
    }

    protected function getModel($modelName) {
        require_once '../app/model/' . lcfirst($modelName) . '.php';
        $className = 'Scam\\' . $modelName . 'Model';
        return new $className($this->db);
    }

    protected function accessDeniedIf($condition) {
        if($condition) {
            require_once 'exceptions/access_denied.php';
            throw new AccessDeniedException();
        }
    }

    protected function accessDeniedUnless($condition) {
        $this->accessDeniedIf(!$condition);
    }

    protected function notFoundIf($condition) {
        if($condition) {
            require_once 'exceptions/not_found_exception.php';
            throw new NotFoundException();
        }
    }

    protected function notFoundUnless($condition) {
        $this->notFoundIf(!$condition);
    }

    protected function redirectTo($url) {
        $this->prepareFlashesForNextRequest();
        header("Location: $url", true, 302);
        die();
    }

    /* flashes are session variables that live only for the next request (i.e. to show 'success!' on next page after redirect) */
    protected function setFlash($key, $value) {
        $this->flashes[$key] = $value;
    }

    # shortcut function to output escapped flash in view
    protected function fl($key){
        return isset($_SESSION['flashes'][$key]) ? $this->e($_SESSION['flashes'][$key]) : null;
    }

    protected function prepareFlashesForNextRequest(){
        if (session_status() != PHP_SESSION_NONE) {
            # delete flashes from this request
            unset($_SESSION['flashes']);
            # set flashes for next request
            $_SESSION['flashes'] = $this->flashes;
        }
    }
}