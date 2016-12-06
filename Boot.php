<?php

namespace CMS;

use \App\Version;
use \Bugsnag\Client;
use \Bugsnag\Handler;
use \Dotenv\Dotenv;

class Boot
{

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
        self::setTime();
        self::loadEnv();
        self::dispatch();
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

        if (self::$bugsnagOptions['alwaysReport'] || self::$bugsnagOptions['stage'] == 'Local') {
            return;
        }

        $bugsnag = Client::make(self::$bugsnagOptions['key']);
        $bugsnag->setNotifyReleaseStages(self::$bugsnagOptions['releaseStages']);
        $bugsnag->setReleaseStage(self::$bugsnagOptions['stage']);
        $bugsnag->setAppType(self::$bugsnagOptions['type']);
        $bugsnag->setAppVersion(Version::current());

        Handler::register($bugsnag);

        return $bugsnag;
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

        if (self::$bugsnagOptions['alwaysReport'] || self::$bugsnagOptions['stage'] == 'Local') {
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

        if ($_SERVER['HTTP_HOST'] == 'localhost:8888') {
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


