<?php 

session_start();

function scripts($arr) {
	$v = "1.0000";
	foreach ($arr as $script) {
		echo "<script src='src/js/$script?v=$v'></script>";
	}
	return;
}


//=================== Views ==================


function logged_in() {
	return ((isset($_SESSION['logged_in']) && $_SESSION['logged_in']));
}

