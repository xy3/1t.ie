<?php


namespace OneT;

use PDO;

/**
 * Class Accounts
 * @package OneT
 */
class Accounts
{
    private $pdo;

    /**
     * Statistics constructor.
     * @param PDO $pdo
     */
    function __construct($pdo)
    {
        $this->pdo = $pdo;
    }


    public function execute()
    {
        self::startSession();
        return $this;
    }

    /**
     * @return bool
     */
    private static function startSession()
    {
        session_start();
        return true;
    }

    /**
     * @param $username
     * @param $password
     * @return bool
     */
    public function login($username, $password)
    {
        if ($_SESSION['logged_in']) {
            return true;
        }

        $stmt_get_user = $this->pdo->prepare(/** @lang SQL */ "
            SELECT * FROM users 
            WHERE username=?
        ");

        $success = $stmt_get_user->execute(array($username));
        if (!$success) {
            return false;
        }
        $row = $stmt_get_user->fetch();
        if (password_verify($password, $row->password)) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $row['user_id'];
        }
        return true;
    }

    /**
     * @return bool
     */
    public function logout()
    {
        unset($_SESSION['logged_in']);
        unset($_SESSION['user_id']);
        return true;
    }

    /**
     * @param $email
     * @param $username
     * @param $password
     * @return bool
     */
    public function register($email, $username, $password)
    {
        $stmt_get_user = $this->pdo->prepare(/** @lang SQL */ "
            INSERT INTO users (username, email, password, ip_address)
            VALUES (?, ?, ?, ?)
        ");

        $success = $stmt_get_user->execute(array(
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $_SERVER['REMOTE_ADDR'],
        ));

        if (!$success) {
            return false;
        }

        return $this->login($username, $password);
    }

    /**
     * @param $username
     * @return bool
     */
    public static function isLoggedIn($username)
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
    }
}
