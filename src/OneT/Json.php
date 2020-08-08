<?php

namespace OneT;
/**
 * Json helper
 */
class Json
{
    /**
     * @param $success
     * @param string $msg
     * @return string
     */
    static function status($success, $msg = ''): string
    {
        return json_encode(array('success' => $success, 'message' => $msg));
    }

    /**
     * @param string $msg
     * @return string
     */
    static function success($msg = ''): string
    {
        return Json::status(true, $msg);
    }

    /**
     * @param string $msg
     * @return string
     */
    static function failure($msg = ''): string
    {
        return Json::status(false, $msg);
    }

    /**
     * @param $success
     * @param $arr
     * @return string
     */
    static function message($success, $arr): string
    {
        $arr['success'] = $success;
        return json_encode($arr);
    }
}