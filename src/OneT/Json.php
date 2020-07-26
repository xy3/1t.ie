<?php

namespace OneT;
/**
 * Json helper
 */
class Json
{
    static function status($success, $msg = ''): string
    {
        return json_encode(array('success' => $success, 'message' => $msg));
    }

    static function success($msg = ''): string
    {
        return Json::status(true, $msg);
    }

    static function failure($msg = ''): string
    {
        return Json::status(false, $msg);
    }

    static function message($success, $arr): string
    {
        $arr['success'] = $success;
        return json_encode($arr);
    }
}