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
 */
class Controller {
    private $db = null;
    protected $session = [];
    protected $get = [];
    protected $post = [];

    public function __construct($db) {
        $this->db = $db;

        session_start();

        # shortcuts to use in templates:
        $this->session = $_SESSION;
        $this->get = $_GET;
        $this->post = $_POST;
    }

    /* shortcut to escape strings in template */
    protected function e($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'utf-8');
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
    }

    protected function getModel($modelName) {
        require '../app/model/' . strtolower($modelName) . '.php';
        $className = 'Scam\\' . $modelName . 'Model';
        return new $className($this->db);
    }
}