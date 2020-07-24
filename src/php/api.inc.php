<?php 
require 'dbc.inc.php';
require 'json.inc.php';

/**
 * 1t Api
 */
class Api
{
	private $dbc;

	function __construct() {
		$dbc = DatabaseConnection::newConnection();
	}

	function execute($req) {
		if (is_callable(array($this, $req->action))){
		    $this->{$req->action}($req);
		} else {
		    $this->invalid_action($req);
		}
	}

	private function add_link($req) {
		
	}

	function invalid_action($req) {
		Json::failure("Invalid action specified: '{$req->action}'");
	}
}