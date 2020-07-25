<?php

use Hashids\Hashids;

require 'json.inc.php';

/**
 * 1t Api
 */
class Api
{
    public $site_url = "https://1t.ie/";
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
            $body = $this->invalid_action($req->action);
            return $resp->body($body)->send();
        }
        $body = $this->{$req->action}($req, $resp);
        return $resp->body($body)->send();
    }

    private function invalid_action($action): string
    {
        return Json::failure("Invalid action specified: '$action'");
    }

    public function get_site_url() {
        return $this->site_url;
    }

    private function add_link($req, $resp): string
    {
        $db = $this->app->db;
        $subdomain = $req->param("subdomain");
        $user_id = $req->param("user_id");
        $url = $req->param("url");

        if (!$url) {
            return $this->invalid_parameters($req->paramsGet(), "No Url provided");
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->invalid_parameters($req->paramsGet(), "Url is invalid");
        }

        $url_id = $this->add_url($url);
        $hash_id = $this->hashids->encode($url_id);

        // Check if link already exists
        $stmt = $db->prepare("SELECT * FROM links WHERE url_id=?");
        $stmt->execute(array($url_id));
        if ($stmt->rowCount()) {
            $row = $stmt->fetch(PDO::FETCH_OBJ);
        } else {
            // It doesn't - create it
            $stmt = $db->prepare("INSERT INTO links (user_id, url_id, short_slug, subdomain, does_expire, expiry_date) VALUES (?,?,?,?,?, NOW() + INTERVAL 7 DAY)");
            $success = $stmt->execute(array(2, $url_id, $hash_id, "", true));
            if (!$success) {
                return Json::message(false, array("error_info" => $stmt->errorInfo()));
            }
            $row = $stmt->fetch(PDO::FETCH_OBJ);
        }

        $result = array(
            'url_id' => $url_id,
            'short_slug' => $hash_id,
            'full_link' => $row->subdomain . Api::get_site_url() . $hash_id
        );
        return Json::message(true, $result);
    }

    private function invalid_parameters($params, $error_message): string
    {
        $data = array("parameters_provided" => $params->all());
        $data['reason'] = "Incorrect parameters provided. [Error: $error_message]";
        return Json::message(false, $data);
    }

    private function add_url(string $url)
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
    private function compare_urls(string $url_1, string $url_2) {
        $parsed_1 = parse_url($url_1);
        $parsed_2 = parse_url($url_2);
        // TODO: go through each attribute and compare them and perhaps compute a similarity score
    }
}