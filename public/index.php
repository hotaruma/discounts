<?php

declare(strict_types=1);

use Hotaruma\HttpRouter\{Enum\HttpMethod, RouteDispatcher, RouteMap};

require_once __DIR__ . "/../vendor/autoload.php";

$routeMap = new RouteMap();

$routeMap->get('/home/', function () {
    echo 'Hi honey';
});

$dispatcher = new RouteDispatcher();
$dispatcher->config(
    requestHttpMethod: HttpMethod::tryFrom($_SERVER['REQUEST_METHOD']),
    requestPath: $_SERVER['REQUEST_URI'],
    routes: $routeMap->getRoutes()
);

$route = $dispatcher->match();
$route->getAction()();
