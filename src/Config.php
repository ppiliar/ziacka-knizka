<?php
/**
 * Trieda pre pracu s konfiguracnym suborom aplikacie
 */

namespace App;


class Config {
    private static $config = null;

    private function __construct() { }

    /**
     * Ziskat konfiguracne nastavenia
     * @param string $configSection Nazov sekcie v konfiguracnom subore
     * @return array|bool|null
     */
    public static function get($configSection = null) {
        if (self::$config == null) {
            self::$config = parse_ini_file(APPLICATION_PATH . "/config.ini", true, INI_SCANNER_TYPED);
        }

        if ($configSection == null) {
            return self::$config;
        } else {
            return self::$config[$configSection];
        }
    }

}