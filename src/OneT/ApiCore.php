<?php


namespace OneT;


use PDO;

interface ApiCore
{
    /**
     * ApiCore constructor.
     * @param PDO $pdo
     */
    function __construct($pdo);

    /**
     * @param $request
     * @param $response
     * @return bool
     */
    public function execute($request, $response): bool;

}