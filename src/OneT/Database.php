<?php


namespace OneT;


use PDO;

class Database
{
    static function newConnection($config)
    {
        $db = new PDO("mysql:dbname={$config['name']};host={$config['host']}", $config['username'], $config['password']);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        return $db;
    }
}