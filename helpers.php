<?php


if (!function_exists('env')) {
    function env($env, $default = '') 
    {
        return getenv($env) ?: $default;
    }
}

if (!function_exists('server')) {
    function server($var, $default = null) {
        if (isset($_SERVER[$var])) {
            return $_SERVER[$var];
        }
        return $default;
    }
}

if (!function_exists('getRevision')) {
    function getRevision() 
    {
        static $r = null;
        if ($r) {
            return $r;
        }
        $revisionFile = APP_ROOT .'/.revision';
        if (file_exists($revisionFile)) {
            $r = trim(file_get_contents($revisionFile));
        } else {
            $r = time();
        }
        return $r;
    }
}