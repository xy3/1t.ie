<?php


/**
 * Json helper
 */
class Json
{
	function status($status, $msg='') {
		echo json_encode(array('status' => $status, 'message' => $msg));
	}

	function success($msg='') {
		return status(1, $msg);
	}

	function failure($msg='') {
		return status(0, $msg);
	}
}