<?php 

session_start();

const VIEWS = 'src/views/';



function status($status, $msg='') {
	echo json_encode(array('status' => $status, 'message' => $msg));
}
function success($msg='') { return status(1, $msg); }
function failure($msg='') { return status(0, $msg); }



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

function show($view, $request='', $page_script='')
{
	require VIEWS . $view;
}

function show_protected($view, $request='', $page_script='')
{
	if (logged_in())
		show($view, $request, $page_script);
	else
		show('login.php', $request);
}

function admin_protected($view, $response)
{
	if (logged_in()) {
		if ($_SESSION['username'] == 'depp') {
			return show($view);
		}
		$response->redirect("/")->send();
	}
	return show('login.php', $request);
}


function allset($check, $data)
{
	foreach ($check as $key) {
		if (!isset($data[$key])) {
			return false;
		}
	}
	return true;
}



function listfiles($dir){ return array_slice(scandir ($dir), 2); }

function contains($haystack, $needle){ return strpos($haystack, $needle); }

function getIpAddr(){
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {$ip=$_SERVER['HTTP_CLIENT_IP'];}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];}
	else {$ip=$_SERVER['REMOTE_ADDR'];}
	return $ip;
}

function when($date)
{
	$ago = daysAgo($date);
	switch ($ago) {
		case -1:
			return "Just now";
		case 0:
			return "Today";
		case 1:
			return "Yesterday";
		default:
			return abs($ago) . " days ago";
	}
	
	return date("d/m/Y", $date);
}

function daysAgo($past){
	$now  = strtotime(date("Y-m-d"));
	$past = strtotime($past);
	$diff = $now - $past;
	$days = floor($diff/(60*60*24));
	return $days;
}
