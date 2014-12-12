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
     *            in the form ['controllername' => ['availableaction1' => ['REQUEST_METHOD1, 'POST'], 'availableaction2' => ['GET', 'POST']], 'controller2' => [], ...].
     *            is used to lookup the responsible controller via controller = $_GET['c'], action = $_GET['a']
     */
    private $routes = [
        'users' => ['login' => 'GET', 'captcha' => 'GET', 'doLogin' => 'POST', 'logout' => 'GET', 'register' => 'GET', 'doRegister' => 'POST'],
        'profile' => ['settings' => 'GET', 'updatePassword' => 'POST', 'updateProfilePin' => 'POST', 'setPGP' => 'POST',
            'resetProfilePin' => 'POST', 'bip32' => 'GET', 'setBip32' => 'POST', 'becomeVendor' => 'GET', 'doBecomeVendor' => 'POST'],
        'shippingOptions' => ['index' => 'GET', 'build' => 'GET', 'create' => 'POST', 'edit' => 'GET', 'update' => 'POST', 'destroy' => 'POST'],
        'products' => ['index' => 'GET', 'build' => 'GET', 'create' => 'POST', 'edit' => 'GET', 'update' => 'POST',
            'destroyImage' => 'GET', 'destroy' => 'POST'],
        'listings' => ['index' => 'GET', 'product' => 'GET', 'productImage' => 'GET', 'vendor' => 'GET'],
        'orders' => ['index' => 'GET', 'create' => 'POST', 'show' => 'GET', 'confirm' => 'POST', 'accept' => 'POST',
            'decline' => 'POST', 'shipped' => 'POST', 'dispute' => 'POST', 'feedback' => 'POST', 'destroy' => 'POST' ],
        'admin' => ['index' => 'GET', 'doLogin' => 'POST', 'logout' => 'GET', 'disputes' => 'GET', 'showDispute' => 'GET',
            'addDisputeMessage' => 'POST', 'createNewTransaction' => 'POST', 'enterSignedTransaction' => 'POST'],
    ];

    public function openDatabaseConnection() {
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'"
        ];

        /* Setting the correct charset for db connection as mentioned here: http://www.phptherightway.com/#php_and_utf8 */
        return new \PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, $options);
    }

    private function dispatchToController($db) {
        $controllerName = isset($_GET['c']) ? $_GET['c'] : '';
        # default action = index
        $actionName = isset($_GET['a']) ? $_GET['a'] : 'index';

        if($this->isValidControllerAndAction($controllerName, $actionName)) {
            # convert controller from param (c=bla) to CamelCase Scam\BlaController
            $controllerClassName = "Scam\\" . mb_convert_case($controllerName, MB_CASE_TITLE, "UTF-8") . "Controller";

            require_once '../app/controller/' . $controllerName . '.php';
            $controller = new $controllerClassName($db);
            $controller ->{$actionName}();
        }
        # no controller or action given => home page
        elseif(!isset($_GET['c']) && !isset($_GET['a'])) {
            require_once '../app/controller/listings.php';
            $controller = new ListingsController($db);
            $controller->index();
        }
        # not existing controller / action => show 404
        else {
            require_once 'exceptions/not_found.php';
            throw new NotFoundException();
        }
    }

    private function isValidControllerAndAction($controllerName, $actionName){
        # check that controller & action are in $routes and that request method matches
        return isset($this->routes[$controllerName]) && isset($this->routes[$controllerName][$actionName])
        && $_SERVER['REQUEST_METHOD'] === $this->routes[$controllerName][$actionName];
    }

    public function run() {
        try {
            $db = $this->openDatabaseConnection();
            $this->dispatchToController($db);
        }
        catch(AccessDeniedException $e) {
            require_once '../app/controller/error.php';
            $controller = new ErrorController($db);
            $controller->accessDenied();
        }
        catch(NotFoundException $e) {
            require_once '../app/controller/error.php';
            $controller = new ErrorController($db);
            $controller->notFound();
        }
        catch(\Exception $e) {
            if(PRODUCTION) {
            require_once '../app/controller/error.php';
            $controller = new ErrorController($db);
            $controller->unknown();
            }
            else {
                # show error to developer
                throw $e;
            }
        }
    }
}

