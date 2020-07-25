<?php 

session_start();

const VIEWS = 'src/views/';


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

//function admin_protected($view, $response)
//{
//	if (logged_in()) {
//		if ($_SESSION['username'] == 'depp') {
//			return show($view);
//		}
//		$response->redirect("/")->send();
//	}
//	return show('login.php', $request);
//}
