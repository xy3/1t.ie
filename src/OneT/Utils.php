<?php


namespace OneT;


class Utils
{
    /**
     * Returns false if all wanted parameters are present
     * @param array $params
     * @param array $wanted_params
     * @return bool|string
     */
    public static function missingParams($params, $wanted_params)
    {
        foreach ($wanted_params as $param) {
            if (!isset($params[$param]) || !$params[$param]) {
                return Response::invalidParameters($params, "No $param provided");
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function startSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return true;
    }
}