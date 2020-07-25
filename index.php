<?php 


 error_reporting(E_ALL);
 ini_set('display_errors', TRUE);
 ini_set('display_startup_errors', TRUE);

use Klein\Klein;

require 'vendor/autoload.php';

$klein = new Klein();

$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
        $klein->service()->flash($err_msg);
//        $klein->service()->back();
    });

    // $app also can store lazy services, e.g. if you don't want to
    // instantiate a database connection on every response
    $app->register('db', function() {
        // Replace this with your actual database login DSN
        $db = new PDO('mysql:dbname=1t.ie;host=localhost', 'root', '');
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        return $db;
    });
});



$klein->respond(['POST','GET'], '/api/[:action]', function ($req, $resp, $service, $app) {
	$api = new Api($app);
	$api->execute($req, $resp);
});


$klein->respond('GET', '/', function ($req) {
	show('home.php', $req);
});


$klein->dispatch();