<?php


namespace OneT;


use PDO;

class User
{
    private $pdo;

    /**
     * User constructor.
     * @param PDO $pdo
     */
    function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param $user_id
     * @return array
     */
    public function getUserLinks($user_id)
    {
        $stmt_get_url_row = $this->pdo->prepare(/** @lang SQL */ "
            SELECT * FROM links 
            LEFT JOIN urls
            ON links.url_id = urls.url_id
            WHERE links.user_id = ?
        ");

        $success = $stmt_get_url_row->execute([$user_id]);
        if (!$success) {
            return [];
        }
        return $stmt_get_url_row->fetchAll();
    }
}