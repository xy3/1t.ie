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
        $allowed_actions = ['addLink', 'deleteLink', 'resolve'];
        if (!is_callable([$this, $req->action]) || in_array($req->action, $allowed_actions)) {
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
            WHERE links.link_hash_id=?
        ");

        $success = $stmt_get_url_row->execute([$req->link_hash_id]);

        if (!$success)
        {
            return $resp->body(Response::message(false, [
                "reason" => "Database lookup query error",
                "error_info" => $stmt_get_url_row->errorInfo()
            ]));
        }
        elseif (!$stmt_get_url_row->rowCount())
        {
            return $resp->body(Response::failure("No link exists with that ID"))->send();
        }

        if (!$this->incrementVisitCount($req->link_hash_id)) {
            return $resp->body(Response::failure("Failed to update visit count"))->send();
        }

        $row = $stmt_get_url_row->fetch();
        $url = base64_decode($row->url_base64);

        // Parameter forwarding
        if ($req->server()->REDIRECT_QUERY_STRING)
        {
            $url .= strpos($url, "?") ? "&" : "?";
            $url .= $req->server()->REDIRECT_QUERY_STRING;
        }
        return $resp->redirect($url, $code = 302);
    }

    /**
     * @param $link_hash_id
     * @return bool
     */
    private function incrementVisitCount($link_hash_id): bool
    {
        $stmt_update_visit_count = $this->pdo->prepare(/** @lang SQL */ "
            UPDATE links
            SET visits = visits + 1
            WHERE link_hash_id=?
        ");

        return $stmt_update_visit_count->execute([$link_hash_id]);
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


    /**
     * @param $req
     * @return string
     */
    private function addlink($req): string
    {
        $accounts = new Accounts($this->pdo);

        $is_missing_param = Utils::missingParams($req->params(), ['url']);
        if ($is_missing_param) {
            return $is_missing_param;
        }

        $url = $req->param("url");
        $api_key = $req->param("api_key") ?? "anonymous";
        $user_id = $accounts->getUserIdFromApiKey($api_key);

        $is_anonymous = $api_key == "anonymous";

        if (!$user_id) {
            return Response::invalidParameters($req->params(), "Invalid API key provided");
        } elseif (!StrictUrlValidator::validate($url, true, true)) {
            return Response::invalidParameters($req->params(), StrictUrlValidator::getError());
        }

        $url_id = $this->addUrl($url);
        if ($is_anonymous) {
            $hash_id = $this->hashids->encode($url_id);
        } else {
            $hash_id = $this->hashids->encode($url_id, $user_id);
        }

        // Check if link already exists
        $stmt = $this->pdo->prepare("SELECT * FROM links WHERE link_hash_id=?");
        $stmt->execute([$hash_id]);

        if (!$stmt->rowCount()) {
            // It doesn't exist so create it
            $stmt = $this->pdo->prepare("
                    INSERT INTO links (link_hash_id, user_id, url_id, does_expire, expiry_date)
                    VALUES (?,?,?,?, NOW() + INTERVAL 7 DAY)
                    ");
            $success = $stmt->execute([$hash_id, $user_id, $url_id, true]);
            if (!$success) {
                return Response::message(false, ["error_info" => $stmt->errorInfo()]);
            }
            // $this->pdo->lastInsertId();
        }

        $result = [
            'url_id' => $url_id,
            'link_hash_id' => $hash_id,
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
        // TODO: Find links with similar attributes and compare them
        // $parsed_url = parse_url($url);
        // for now though, let's just strip any excess slashes
        $url = chop($url, "/");
        $url_md5 = md5($url);

        $stmt = $this->pdo->prepare("SELECT * FROM urls WHERE md5=?");
        $stmt->execute([$url_md5]);
        if ($stmt->rowCount()) {
            $url_id = $stmt->fetch()->url_id;
            // Update the links_to_url count for this url
            $stmt = $this->pdo->prepare("UPDATE urls SET links_to_url = links_to_url + 1 WHERE md5=?");
            $stmt->execute([$url_md5]);
            return $url_id;
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO urls (url_base64, url_plain, md5) VALUES(?, ?, ?)");
            $stmt->execute([base64_encode($url), $url, $url_md5]);
            return $this->pdo->lastInsertId();
        }
    }

    /**
     * @param $req
     * @return bool|string
     */
    private function deleteLink($req)
    {
        $is_missing_param = Utils::missingParams($req->params(), ['link_hash_id', 'api_key']);
        if ($is_missing_param) {
            return $is_missing_param;
        }

        $api_key = $req->param("api_key");
        $link_hash_id = $req->param("link_hash_id");

        // Delete URL if it is only used for one link
        $stmt_delete_url = $this->pdo->prepare("
            DELETE urls FROM urls
            INNER JOIN links
            ON urls.url_id = links.url_id
            WHERE links.link_hash_id = ? AND urls.links_to_url = 1
        ");

        $success = $stmt_delete_url->execute([$link_hash_id]);
        if (!$success) {
            return Response::failure("Failed to delete corresponding URL.");
        }

        // Url deleted successfully.

        $stmt_get_link = $this->pdo->prepare(/** @lang SQL */"
            DELETE links
            FROM links
            INNER JOIN users
            ON links.user_id = users.user_id
            WHERE links.link_hash_id = ? AND users.api_key = ?
        ");

        $success = $stmt_get_link->execute([$link_hash_id, $api_key]);
        if (!$success) {
            return Response::failure("Link does not exist or you do not have permissions to delete it.");
        }

        // Link deleted successfully.

        return Response::success("Link deleted successfully.");
    }

    private function compareUrls(string $url_1, string $url_2)
    {
        $parsed_1 = parse_url($url_1);
        $parsed_2 = parse_url($url_2);
        // TODO: go through each attribute and compare them and perhaps compute a similarity score
    }
}