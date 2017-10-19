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

if (!function_exists('getFileData')) {
    function getFileData($file) {
        if (file_exists($file)) {
           return trim(file_get_contents($file));
        }
        return false;
    }
}

if (!function_exists('getRevision')) {
    function getRevision($file = false, $defaultTime = true) {
        $revisionFile = APP_ROOT .'/.revision';
        return getFileData($file) ?:  
                getFileData($revisionFile) ?:
                ($defaultTime ? time() : false);
    }
}

if (!function_exists('getBuild')) {
    function getBuild($file = false) {
        return getRevision($file);
    }
}

if (!function_exists('getCommit')) {
    function getCommit($file = false) {
        $commitFile = APP_ROOT .'/.commit';
        return getFileData($file) || getFileData($commitFile) || false;

    }
}