<?php

namespace CMS;

use \App\Version;
use \Bugsnag\Client;
use \Bugsnag\Handler;
use \Dotenv\Dotenv;

class Boot
{
    protected static $bugsnag;

    protected static $testDotEnvFile = '/.env.test';

    protected static $bugsnagOptions = [
        'alwaysReport' => false,
        'key'   => 'xxx',
        'keyJs'   => 'xxx',
        'stage' => 'Local',
        'type'  => 'PHP',
        'releaseStages' => ['Production', 'Staging', 'Development'], 
    ];

    public static function init()
    {
        if (php_sapi_name() == 'cli') {
            $_SERVER['HTTP_HOST'] = false;
        }
        self::setTime();
        self::loadEnv();
        self::dispatch();
        self::loadShutdownHandler();
    }

    public static function loadShutdownHandler()
    {
        register_shutdown_function(function(){
            
        });
    }

    public static function bugsnag($options)
    {
        foreach ($options as $key => $value) {
            if (isset(self::$bugsnagOptions[$key])) {
                self::$bugsnagOptions[$key] = $value;
            }
        }

        if (!self::$bugsnagOptions['key']) {
            return;
        }

        if (self::$bugsnagOptions['alwaysReport']) {
            self::$bugsnagOptions['releaseStages'][] = self::$bugsnagOptions['stage'];
        }
        
        // if (!in_array(self::$bugsnagOptions['stage'], self::$bugsnagOptions['releaseStages'])) {
        //     return;
        // }

        $bugsnag = Client::make(self::$bugsnagOptions['key']);
        $bugsnag->setNotifyReleaseStages(self::$bugsnagOptions['releaseStages']);
        $bugsnag->setReleaseStage(self::$bugsnagOptions['stage']);
        $bugsnag->setAppType(self::$bugsnagOptions['type']);
        $bugsnag->setAppVersion(Version::current());

        Handler::register($bugsnag);

        self::$bugsnag = $bugsnag;

        return $bugsnag;
    }

    public static function bugsnagException(Exception $e)
    {
        if (self::$bugsnag) {
            self::$bugsnag->notifyException($e);
        }
    }

    public static function bugsnagError($type = 'Error', $message)
    {
        if (self::$bugsnag) {
            self::$bugsnag->notifyError($type, $message);
        }
    }

    public static function bugsnagJsData($options = [])
    {

        foreach ($options as $key => $value) {
            if (isset(self::$bugsnagOptions[$key])) {
                self::$bugsnagOptions[$key] = $value;
            }
        }

        if (!self::$bugsnagOptions['keyJs']) {
            return;
        }

        if (self::$bugsnagOptions['alwaysReport']) {
            self::$bugsnagOptions['releaseStages'][] = self::$bugsnagOptions['stage'];
        }
        
        if (!in_array(self::$bugsnagOptions['stage'], self::$bugsnagOptions['releaseStages'])) {
            return;
        }
        
        $bugsnagParams = [
            'data-apikey'               => self::$bugsnagOptions['keyJs'],
            'data-appversion'           => Version::current(),
            'data-releasestage'         => self::$bugsnagOptions['stage'],
        ];

        $paramsJoined = '';
        foreach($bugsnagParams as $param => $value) {
           $paramsJoined .= " $param=\"$value\"";
        }
        return $paramsJoined;
    }

    protected static function setTime()
    {
        if( ! ini_get('date.timezone') )
        {
            // date_default_timezone_set('GMT');
            date_default_timezone_set('America/New_York');
        }
    }

    public static function testDotEnvFile($file)
    {
        if (file_exists($file)) {
            self::$testDotEnvFile = $file;
        }
    }

    protected static function loadEnv()
    {
        $dotenv = '/.env';

        if ($_SERVER['HTTP_HOST'] == 'localhost:8888' || strpos($_SERVER['HTTP_HOST'], '.test')) {
            $dotenv = self::$testDotEnvFile;
        }

        if (file_exists(APP_ROOT . $dotenv)) {
            $phpdotenv = new Dotenv(APP_ROOT, $dotenv);
            $phpdotenv->load();
        }

        if ( ! defined('ENV')) {
            define('ENV',  env('APP_ENV', 'local'));
            define('ENV_FULL', env('APP_ENV_FULL', 'Local'));
            define('APP_DEBUG', env('APP_DEBUG', false) || env('APP_ENV_IS_PUBLIC', false));
            define('ENV_DEBUG', APP_DEBUG);
        }
    }

    /**
     * print out version in web request
     *
     * @return void
     **/
    protected static function dispatch()
    {
        if (server('REQUEST_METHOD') == 'GET') {
            if (server('REQUEST_URI') == '/misc/version') {
                die(Version::current());
            }
            if (server('REQUEST_URI') == '/misc/version?shield') {
                $ver = str_replace('-', '--', Version::current());
                $url = 'https://img.shields.io/badge/'.ENV_FULL.'-v'.$ver.'-green.svg';
                header("Location: {$url}", TRUE, 302);
                exit;
            }
            if (server('REQUEST_URI') == '/misc/version?previous') {
                die(Version::previous());
            }
            if (server('REQUEST_URI') == '/misc/version?json') {
                header('Content-Type: application/json');
                die(Version::json());
            }
        }
    }

}


