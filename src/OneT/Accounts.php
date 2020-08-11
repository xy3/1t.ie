<?php


namespace OneT;

use PDO;
use React\Promise\Util;

/**
 * Class Accounts
 * @package OneT
 */
class Accounts implements ApiCore
{
    private $pdo;

    /**
     * Accounts constructor.
     * @param PDO $pdo
     */
    function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return bool
     */
    public static function isLoggedIn()
    {
        Utils::startSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
    }

    /**
     * @param string $api_key
     * @return bool
     */
    public function getUserIdFromApiKey($api_key)
    {
        $stmt_get_user_id = $this->pdo->prepare(/** @lang SQL */ "
            SELECT user_id FROM users 
            WHERE api_key=?
        ");

        $success = $stmt_get_user_id->execute(array($api_key));
        if (!$success || !$stmt_get_user_id->rowCount()) {
            return false;
        }
        return $stmt_get_user_id->fetch()->user_id;
    }

    /**
     * @param $req
     * @param $resp
     * @return mixed
     */
    public function execute($req, $resp): bool
    {
        Utils::startSession();
        if (!is_callable(array($this, $req->action))) {
            $body = Response::invalidAction($req->action);
            $resp->body($body)->send();
            return false;
        }
        $body = $this->{$req->action}($req, $resp);
        $resp->body($body)->send();
        return true;
    }

    /**
     * @return string
     */
    public function logout()
    {
        unset($_SESSION['logged_in']);
        unset($_SESSION['user_id']);
        return Response::success("Logged out successfully");
    }

    /**
     * @param $request
     * @return string
     */
    public function register($request)
    {
        $is_missing_param = Utils::missingParams($request->params(), ['username', 'password', 'email']);
        if ($is_missing_param) {
            return $is_missing_param;
        }

        $username = $request->param("username");
        $password = $request->param("password");
        $email = $request->param("email");

        $stmt_insert_user = $this->pdo->prepare(/** @lang SQL */ "
            INSERT INTO users (username, email, password, api_key, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");

        $success = $stmt_insert_user->execute(array(
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            substr(sha1($username . uniqid()), 0, 20),
            $_SERVER['REMOTE_ADDR'],
        ));

        if (!$success) {
            return Response::message(false, array("reason" => "Could perform registration request",
                "error_info" => $stmt_insert_user->errorInfo()));
        }

        return $this->login($request);
    }

    /**
     * @param $request
     * @return string
     */
    public function login($request)
    {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
            return Response::success("Logged in successfully");
        }

        $is_missing_param = Utils::missingParams($request->params(), ['email', 'password']);
        if ($is_missing_param) {
            return $is_missing_param;
        }

        $email = $request->param('email');
        $password = $request->param('password');

        $stmt_get_user = $this->pdo->prepare(/** @lang SQL */ "
            SELECT * FROM users 
            WHERE email=?
        ");

        $success = $stmt_get_user->execute(array($email));
        if (!$success || !$stmt_get_user->rowCount()) {
            return Response::message(false, array("reason" => "Could perform login request",
                "error_info" => $stmt_get_user->errorInfo()));
        }
        $row = $stmt_get_user->fetch();
        if (password_verify($password, $row->password)) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user'] = $row;
            return Response::success("Logged in successfully");
        }
        return Response::failure("Incorrect password");
    }
}
