<?php


error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

use Klein\Klein;

require 'vendor/autoload.php';

const VIEWS_DIR = "src/views/";

$klein = new Klein();

$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
        $klein->service()->flash($err_msg);
        $klein->service()->back();
    });

    // lazy services (Only get instantiated on first call)
    $app->register('db', function () {
        // Replace this with your actual database login DSN
        $db = new PDO('mysql:dbname=1t.ie;host=localhost', 'root', '');
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        return $db;
    });

    $app->register('api', function () use ($app) {
        return new Api($app);
    });
});

$klein->respond('GET', '/', function ($req, $resp, $service, $app) {
    $service->render(VIEWS_DIR . "home.phtml");
});

$klein->respond(['POST', 'GET'], '/api/[a:action]', function ($req, $resp, $service, $app) {
    $app->api->execute($req, $resp);
});

$klein->respond(['POST', 'GET'], '/[a:short_slug]', function ($req, $resp, $service, $app) {
    $app->api->resolve($req, $resp);
});




$klein->dispatch();