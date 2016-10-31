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
    function getRevision($file = false) 
    {
        static $r = null;
        if ($r) {
            return $r;
        }
        if (file_exists($file)) {
           return trim(file_get_contents($file));
        }
        $revisionFile = APP_ROOT .'/.revision';
        if (file_exists($revisionFile)) {
            return trim(file_get_contents($revisionFile));
        }
        return time();
    }
}

if (!function_exists('getBuild')) {
    function getBuild($file = false) {
        return getRevision($file);
    }
}

