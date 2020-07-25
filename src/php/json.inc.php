<?php


/**
 * Json helper
 */
class Json
{
	static function status($status, $msg='') : string {
		return json_encode(array('status' => $status, 'message' => $msg));
	}

	static function success($msg='') : string {
		return Json::status(1, $msg);
	}

	static function failure($msg='') : string {
		return Json::status(0, $msg);
	}

	static function message($success, $arr) : string {
	    $arr['success'] = $success;
	    return json_encode($arr);
    }
}