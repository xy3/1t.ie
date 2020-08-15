<?php

namespace OneT;
/**
 * Response helper
 */
class Response
{
    /**
     * @param $success
     * @param string $msg
     * @return string
     */
    static function status($success, $msg = ''): string
    {
        return json_encode(['success' => $success, 'message' => $msg]);
    }

    /**
     * @param string $msg
     * @return string
     */
    static function success($msg = ''): string
    {
        return self::status(true, $msg);
    }

    /**
     * @param string $msg
     * @return string
     */
    static function failure($msg = ''): string
    {
        return self::status(false, $msg);
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


    /**
     * @param string $action
     * @return string
     */
    static function invalidAction(string $action): string
    {
        return self::failure("Invalid action specified: '$action'");
    }


    /**
     * @param array $params
     * @param string $error_message
     * @return string
     */
    static function invalidParameters(array $params, string $error_message): string
    {
        $data = ["parameters_provided" => $params];
        $data['message'] = "Error: $error_message";
        $data['reason'] = "Invalid parameters provided";
        return self::message(false, $data);
    }

    /**
     * @param $response
     * @return bool
     */
    static function isSuccess($response)
    {
        return (bool) json_decode($response)['success'];
    }
}