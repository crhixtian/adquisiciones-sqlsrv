<?php
// simple front controller for our MVC setup

// start session for authentication/menu if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// load core classes
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/Database.php';

// determine controller and action from query string
$controllerName = isset($_GET['controller']) ? ucfirst($_GET['controller']) . 'Controller' : 'RequerimientoController';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

$controllerFile = __DIR__ . "/app/controllers/{$controllerName}.php";
if (!file_exists($controllerFile)) {
    die("Controller $controllerName not found");
}
require_once $controllerFile;

if (!class_exists($controllerName)) {
    die("Controller class $controllerName does not exist");
}

$controller = new $controllerName();
if (!method_exists($controller, $action)) {
    die("Action $action not found in controller $controllerName");
}

$controller->$action();
