<?php


namespace OneT;


class Statistics
{
    private $app;

    /**
     * Statistics constructor.
     * @param $_app
     */
    function __construct($_app)
    {
        $this->app = $_app;
    }

    public function getUserLinks($user_id)
    {
        $db = $this->app->db;
        $stmt_get_url_row = $db->prepare(/** @lang SQL */ "
            SELECT * FROM links 
            LEFT JOIN urls
            ON links.url_id = urls.url_id
            WHERE links.user_id = ?
        ");

        $success = $stmt_get_url_row->execute(array($user_id));
        if (!$success) {
            return [];
        }
        return $stmt_get_url_row->fetchAll();
    }
}