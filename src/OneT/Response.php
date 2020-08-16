<?php

namespace OneT;
/**
 * Response helper
 */
class Response
{
    /**
     * @param $success
     * @param string $message
     * @return string
     */
    static function status(bool $success, string $message=''): string
    {
        return json_encode(['success' => $success, 'message' => $message]);
    }

    /**
     * @param string $message
     * @return string
     */
    static function success(string $message=''): string
    {
        return self::status(true, $message);
    }

    /**
     * @param string $message
     * @return string
     */
    static function failure(string $message=''): string
    {
        return self::status(false, $message);
    }

    /**
     * @param string $success
     * @param array $data
     * @return string
     */
    static function message(string $success, array $data): string
    {
        $data['success'] = $success;
        return json_encode($data);
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
     * @param string $json_response
     * @return bool
     */
    static function isSuccess(string $json_response)
    {
        return (bool) json_decode($json_response)['success'];
    }
}