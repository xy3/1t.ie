<?php

use Hashids\Hashids;

require 'Json.php';

/**
 * 1t Api
 */
class Api
{
    /**
     * @var Hashids
     */
    private $hashids;
    private $app;

    function __construct($_app)
    {
        $this->app = $_app;
        $this->hashids = new Hashids();
    }

    public function execute($req, $resp)
    {
        if (!is_callable(array($this, $req->action))) {
            $body = $this->invalidAction($req->action);
            return $resp->body($body)->send();
        }
        $body = $this->{$req->action}($req, $resp);
        return $resp->body($body)->send();
    }

    public function resolve($req, $resp)
    {
        $db = $this->app->db;
        $stmt_get_url_row = $db->prepare(/** @lang SQL */ "
            SELECT * FROM links 
            LEFT JOIN urls
            ON links.url_id = urls.url_id
            WHERE links.short_slug=?
        ");

        $success = $stmt_get_url_row->execute(array($req->short_slug));
        if (!$success) {
            return $resp->body(Json::message(false, array("reason" => "Database lookup query error",
                "error_info" => $stmt_get_url_row->errorInfo())));
        } elseif (!$stmt_get_url_row->rowCount()) {
            return $resp->body(Json::failure("No link exists with that ID"))->send();
        }
        $row = $stmt_get_url_row->fetch();
        $url = base64_decode($row->url_base64);

        // Parameter forwarding
        if ($req->server()->REDIRECT_QUERY_STRING) {
            $url .= strpos($url, "?") ? "&" : "?";
            $url .= $req->server()->REDIRECT_QUERY_STRING;
        }
        return $resp->redirect($url, $code=200);
    }

    private function invalidAction(string $action): string
    {
        return Json::failure("Invalid action specified: '$action'");
    }

    private function addlink(object $req): string
    {
        $db = $this->app->db;
        $user_id = $req->param("user_id") ?? 2;
        $url = $req->param("url");

        if (!$url) {
            return $this->invalidParameters($req->paramsGet(), "No Url provided");
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->invalidParameters($req->paramsGet(), "Url is invalid");
        }

        $url_id = $this->addUrl($url);
        $hash_id = $this->hashids->encode($url_id);

        // Check if link already exists
        $stmt = $db->prepare("SELECT * FROM links WHERE url_id=?");
        $stmt->execute(array($url_id));
        if (!$stmt->rowCount()) {
            // It doesn't - create it
            $stmt = $db->prepare("
                    INSERT INTO links (user_id, url_id, short_slug, does_expire, expiry_date)
                    VALUES (?,?,?,?, NOW() + INTERVAL 7 DAY)
                    ");
            $success = $stmt->execute(array($user_id, $url_id, $hash_id, true));
            if (!$success) {
                return Json::message(false, array("error_info" => $stmt->errorInfo()));
            }
            $db->lastInsertId();
        }

        $result = array(
            'url_id' => $url_id,
            'short_slug' => $hash_id,
            'full_link' => Api::getSiteUrl() . $hash_id
        );
        return Json::message(true, $result);
    }

    private function invalidParameters(array $params, string $error_message): string
    {
        $data = array("parameters_provided" => $params->all());
        $data['reason'] = "Incorrect parameters provided. [Error: $error_message]";
        return Json::message(false, $data);
    }

    private function addUrl(string $url)
    {
        // $parsed_url = parse_url($url); // TODO: Find links with similar attributes and compare them
        // for now though, let's just strip any excess slashes
        $url = chop($url, "/");

        $db = $this->app->db;
        $stmt = $db->prepare("SELECT * FROM urls WHERE md5=?");
        $stmt->execute(array(md5($url)));
        if ($stmt->rowCount()) {
            return $stmt->fetch()->url_id;
        } else {
            $stmt = $db->prepare("INSERT INTO urls (url_base64, md5) VALUES(?, ?)");
            $stmt->execute(array(base64_encode($url), md5($url)));
            return $db->lastInsertId();
        }
    }

    public function getSiteUrl($req)
    {
        return "//" . $req->server()->HTTP_HOST . "/";
    }

    private function compareUrls(string $url_1, string $url_2)
    {
        $parsed_1 = parse_url($url_1);
        $parsed_2 = parse_url($url_2);
        // TODO: go through each attribute and compare them and perhaps compute a similarity score
    }
}