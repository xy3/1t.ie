<?php

//
//error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
require 'vendor/autoload.php';

use OneT\Api;
use OneTUI\Views;
use Klein\Klein;


$config = parse_ini_file("config.ini");

$klein = new Klein();

$klein->respond(function ($request, $response, $service, $app) use ($config, $klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
        $klein->service()->flash($err_msg);
        $klein->service()->back();
    });

    // lazy services (Only get instantiated on first call)
    $app->register('db', function () use ($config) {
        // Replace the values in config.ini with your actual database login details
        $db = new PDO("mysql:dbname={$config['name']};host={$config['host']}", $config['username'], $config['password']);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        return $db;
    });

    $app->register('api', function () use ($app) {
        return new Api($app);
    });
});

$klein->respond('GET', '/', function ($req, $resp, $service, $app) {
    $v = new Views();
    echo $v->hello();
//    echo Views::hello();
//    echo "hey";
//    $views->home();
//    $service->render(VIEWS_DIR . "home.phtml", array('views' => VIEWS_DIR, 'components' => COMPONENTS_DIR));
});

$klein->respond(['POST', 'GET'], '/api/[:action]', function ($req, $resp, $service, $app) {
    $app->api->execute($req, $resp);
});

$klein->respond(['POST', 'GET'], '/[a:short_slug]', function ($req, $resp, $service, $app) {
    $app->api->resolve($req, $resp);
});


$klein->dispatch();