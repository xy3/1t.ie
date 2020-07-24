<?php 

DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_USER', 'root');
DEFINE ('DB_PASS', '');
DEFINE ('DB_NAME', '1t.ie');

/**
 * Database connection
 */
class DatabaseConnection
{
	public function newConnection() {
		$dbc = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		/* check connection */
		if ($dbc->connect_error) {
		    die('Connect Error (' . $dbc->connect_errno . ') '. $dbc->connect_error);
		}
		return $dbc;
	}
}