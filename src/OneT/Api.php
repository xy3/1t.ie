<?php

namespace OneT;

use Hashids\Hashids;
use PDO;

/**
 * 1t Api
 */
class Api implements ApiCore
{
    private $hashids;
    private $pdo;

    /**
     * Api constructor.
     * @param PDO $pdo
     */
    function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->hashids = new Hashids();
    }

    /**
     * @param $req
     * @param $resp
     * @return bool
     */
    public function execute($req, $resp): bool
    {
        if (!is_callable(array($this, $req->action))) {
            $resp->body(Response::invalidAction($req->action))->send();
            return false;
        }
        $body = $this->{$req->action}($req, $resp);
        $resp->body($body)->send();
        return true;
    }

    /**
     * @param $req
     * @param $resp
     * @return mixed
     */
    public function resolve($req, $resp)
    {
        $stmt_get_url_row = $this->pdo->prepare(/** @lang SQL */ "
            SELECT * FROM links 
            LEFT JOIN urls
            ON links.url_id = urls.url_id
            WHERE links.short_slug=?
        ");

        $success = $stmt_get_url_row->execute(array($req->short_slug));
        if (!$success) {
            return $resp->body(Response::message(false, array("reason" => "Database lookup query error",
                "error_info" => $stmt_get_url_row->errorInfo())));
        } elseif (!$stmt_get_url_row->rowCount()) {
            return $resp->body(Response::failure("No link exists with that ID"))->send();
        }
        $row = $stmt_get_url_row->fetch();
        $url = base64_decode($row->url_base64);

        // Parameter forwarding
        if ($req->server()->REDIRECT_QUERY_STRING) {
            $url .= strpos($url, "?") ? "&" : "?";
            $url .= $req->server()->REDIRECT_QUERY_STRING;
        }
        return $resp->redirect($url, $code = 200);
    }

    /**
     * @param $req
     * @return string
     */
    public function getSiteUrl($req)
    {
        $protocol = $req->isSecure() ? "https://" : "http://";
        return $protocol . $req->server()->get('HTTP_HOST') . "/";
    }


    private function addlink($req): string
    {
        $url = $req->param("url");
        $api_key = $req->param("api_key");

        $accounts = new Accounts($this->pdo);
        $user_id = $accounts->getUserIdFromApiKey($api_key);

        if (!$url) {
            return Response::invalidParameters($req->params(), "No Url provided");
        } elseif (!$api_key) {
            return Response::invalidParameters($req->params(), "No API key provided");
        } elseif (!$user_id) {
            return Response::invalidParameters($req->params(), "Invalid API key provided");
        } elseif (!StrictUrlValidator::validate($url, true, true)) {
            return Response::invalidParameters($req->params(), StrictUrlValidator::getError());
        }

        $url_id = $this->addUrl($url);
        $hash_id = $this->hashids->encode($url_id);

        // Check if link already exists
        $stmt = $this->pdo->prepare("SELECT * FROM links WHERE url_id=?");
        $stmt->execute(array($url_id));
        if (!$stmt->rowCount()) {
            // It doesn't exist so create it
            $stmt = $this->pdo->prepare("
                    INSERT INTO links (user_id, url_id, short_slug, does_expire, expiry_date)
                    VALUES (?,?,?,?, NOW() + INTERVAL 7 DAY)
                    ");
            $success = $stmt->execute(array($user_id, $url_id, $hash_id, true));
            if (!$success) {
                return Response::message(false, array("error_info" => $stmt->errorInfo()));
            }
            // $this->pdo->lastInsertId();
        }

        $result = [
            'url_id' => $url_id,
            'short_slug' => $hash_id,
            'full_link' => Api::getSiteUrl($req) . $hash_id
        ];
        return Response::message(true, $result);
    }

    /**
     * Adds a URL and returns its ID
     * @param string $url
     * @return int
     */
    private function addUrl(string $url): int
    {
        // $parsed_url = parse_url($url); // TODO: Find links with similar attributes and compare them
        // for now though, let's just strip any excess slashes
        $url = chop($url, "/");

        $stmt = $this->pdo->prepare("SELECT * FROM urls WHERE md5=?");
        $stmt->execute(array(md5($url)));
        if ($stmt->rowCount()) {
            return $stmt->fetch()->url_id;
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO urls (url_base64, url_plain, md5) VALUES(?, ?, ?)");
            $stmt->execute(array(base64_encode($url), $url, md5($url)));
            return $this->pdo->lastInsertId();
        }
    }

    private function compareUrls(string $url_1, string $url_2)
    {
        $parsed_1 = parse_url($url_1);
        $parsed_2 = parse_url($url_2);
        // TODO: go through each attribute and compare them and perhaps compute a similarity score
    }
}