<?php 


// error_reporting(E_ALL);
// ini_set('display_errors', TRUE);
// ini_set('display_startup_errors', TRUE);

require 'vendor/autoload.php';
require 'src/php/util.inc.php';

$router = new \Klein\Klein();


$router->respond(['POST','GET'], '/api/[:action]', function ($req, $resp) {
	require_once 'src/php/api.inc.php';
	$api = new Api();
	$api->execute($req);
});


$router->respond('GET', '/', function ($req) {
	show('home.php', $req);
});



// On error, redirect to homepage
$router->onHttpError(function ($code, $router) {
	$router->response()->redirect('/')->send();
});


$router->dispatch();