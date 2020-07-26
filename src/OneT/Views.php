<?php

namespace OneT;

class Views
{
    const DIR = 'src/views/';

    static function showProtected($view): bool
    {
        if (logged_in())
            return Views::show($view);
        return Views::show('login.phtml');
    }

    static function show(string $view): bool
    {
        require_once Views::DIR . $view;
        return true;
    }

    function adminProtected($view, $resp): bool
    {
        if (logged_in()) {
            if ($_SESSION['username'] == 'admin_username_here') {
                return Views::show($view);
            }
            $resp->redirect("/")->send();
        }
        return Views::show('login.phtml');
    }

}