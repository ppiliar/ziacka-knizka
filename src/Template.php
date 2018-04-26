<?php
/**
 * Trieda pre pracu so sablonami s vyuzitim Twig.
 */

namespace App;

use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;

class Template {
    /**
     * @var Twig_Environment
     */
    private $twig = null;

    private static $instance = null;

    private function __construct() {
        $loader = new Twig_Loader_Filesystem(APPLICATION_PATH . '/templates');
        $this->twig = new Twig_Environment($loader, array(
            'cache' => false, // v pripade, ze chceme pouzivat cache, tu bude adresar, kam sa bude cache ukladat
            'debug' => Config::get('app')['debug'], // aby bolo mozne vypisovat obsah premennych pomocou dump
        ));

        $this->twig->addExtension(new Twig_Extension_Debug());

        // definovanie globalnych premennych, ktore sa v priebehu requestu nemenia
        $this->twig->addGlobal('baseUrl', Config::get('app')['baseUrl']);
        $this->twig->addGlobal('request', Request::parseUrl());
    }

    /**
     * @return Twig_Environment
     */
    public static function getTwig() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        // Nastavenie globalnych premennych, ktore sa mozu v priebehu requestu menit
        self::$instance->twig->addGlobal('loggedUser', $_SESSION['loggedUser'] ?? false);
        self::$instance->twig->addGlobal('loggedUserRole', $_SESSION['loggedUserRole'] ?? false);

        return self::$instance->twig;
    }
}
