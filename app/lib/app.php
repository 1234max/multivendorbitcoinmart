<?php
namespace Scam;

/**
 * Class App
 * @package Scam
 * @author Matthias Winzeler <matthias.winzeler@gmail.com>
 *
 * Entry point of the application.
 * Holds a map of all publicly accessible controller & actions ($routes).
 * On each request, it
 * a) opens the database connection
 * b.) dispatches the request to the responsible controller (with fallback to default controller and error pages)
 */
class App {

    /**
     * @var array map of all publicly accessible controller actions,
     *            in the form ['controllername' => ['availableaction1', 'availableaction2'], 'controller2' => [], ...].
     *            is used to lookup the responsible controller via $_GET['c'] = controller, $_GET['a'] = action
     */
    private $routes = ['test' => ['index']];

    private function openDatabaseConnection() {
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
        ];

        /* Setting the correct charset for db connection as mentioned here: http://www.phptherightway.com/#php_and_utf8 */
        return new \PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, $options);
    }

    private function dispatchToController($db) {
        # check that invoked controller & action is in $routes
        if(isset($_GET['c']) && isset($this->routes[$_GET['c']]) && isset($_GET['a']) && in_array($_GET['a'], $this->routes[$_GET['c']])) {
            # convert controller from param (c=bla) to CamelCase Scam\BlaController
            $controllerName = "Scam\\" . mb_convert_case($_GET['c'], MB_CASE_TITLE, "UTF-8") . "Controller";

            require '../app/controller/' . $_GET['c'] . '.php';

            $controller = new $controllerName($db);

            $actionName = $_GET['a'];
            $controller ->{$actionName}();
        }
        # no controller or action given => home page
        elseif(!isset($_GET['c']) && !isset($_GET['a'])) {
            require '../app/controller/test.php';
            $controller = new \Scam\TestController($db);
            $controller->index();
        }
        # not existing controller / action => show 404
        else {
            require '../app/controller/error.php';
            $controller = new \Scam\ErrorController($db);
            $controller->error404();
        }
    }

    public function run() {
        $db = $this->openDatabaseConnection();
        $this->dispatchToController($db);
    }
}

