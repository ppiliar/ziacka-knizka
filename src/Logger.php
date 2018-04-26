<?php
/**
 * Trieda pre logovanie s vyuzitim Monolog.
 */

namespace App;

use Monolog\Handler\StreamHandler;

class Logger {
    private static $logger = null;

    /**
     * Triedu nebude mozne vytvorit pomocou new
     */
    private function __construct() { }

    /**
     * Ziskat instanciu triety Monolog
     * @throws \Exception
     */
    public static function get() {
        if (self::$logger == null) {
            self::$logger = new \Monolog\Logger('App');
            $minLogLevel = (Config::get('app')['debug'])?\Monolog\Logger::DEBUG:\Monolog\Logger::INFO;
            self::$logger->pushHandler(new StreamHandler(APPLICATION_PATH . '/app.log', $minLogLevel));
        }

        return self::$logger;
    }
}