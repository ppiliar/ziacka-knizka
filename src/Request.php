<?php
/**
 * Spracovanie parametrov poziadavky
 */

namespace App;

class Request {
    /**
     * Udaje, ktore boli ziskane analyzou pozidavky
     * @var array
     */
    private static $parsedUrl = null;

    /**
     * Ziskat nazov kontrolera
     * @return string
     */
    public static function getControllerName() {
        if (self::$parsedUrl == null) {
            self::parseUrl();
        }

        return self::$parsedUrl['controller'];
    }

    /**
     * Ziskat hodnotu jedneho parametra
     * @param string $paramName Nazov parametra
     * @param string $defaultValue Vychodzia hodnota ak parameter nebol zadany
     * @return string
     */
    public static function getParam($paramName, $defaultValue = null) {
        if (self::$parsedUrl == null) {
            self::parseUrl();
        }

        return self::$parsedUrl['params'][$paramName] ?? $defaultValue;
    }

    /**
     * Ziskat nazov akcie
     * @return string
     */
    public static function getActionName() {
        if (self::$parsedUrl == null) {
            self::parseUrl();
        }

        return self::$parsedUrl['action'];
    }

    /**
     * Ziskat zoznam parametrov
     * @return array
     */
    public static function getParams() {
        if (self::$parsedUrl == null) {
            self::parseUrl();
        }

        return self::$parsedUrl['params'];
    }

    /**
     * Ziskat nazov metody akou bola zaslana poziadavka
     * @return string
     */
    public static function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Je poziadavka zaslana metodou POST?
     * @return bool
     */
    public static function isPost() {
        return self::getMethod() == 'POST';
    }

    /**
     * Je poziadavka zaslana metodou GET?
     * @return bool
     */
    public static function isGet() {
        return self::getMethod() == 'GET';
    }

    /**
     * Ziskat objekt kontrolera na zaklade jeho nazvu
     * @param $controllerName
     * @return Object
     */
    public static function getControllerByName($controllerName) {
        $controllerClassName = "\\App\\Controller\\" . ucfirst($controllerName);
        $controller = new $controllerClassName();

        return $controller;
    }

    /**
     * Vykonat akciu na zaklade analyzy aktualnej URL
     * @return string
     */
    public static function route() {
        $request = self::parseUrl();
        return self::executeAction($request['controller'], $request['action']);
    }

    /**
     * Vykonat akciu na zaklade nazvu akcie a kontrolera
     * @param string $controllerName
     * @param string $actionName
     * @param null $params
     * @return string
     */
    public static function executeAction($controllerName, $actionName, $params=null) {
        $controller = self::getControllerByName($controllerName);
        $actionMethodName = "{$actionName}Action";

        // upravim nazvy akcie a kontrolera v twigu aby som v templatoch pouzival aktialne data
        self::$parsedUrl['controller'] = $controllerName;
        self::$parsedUrl['action'] = $actionName;
        self::$parsedUrl['params'] = $params;
        Template::getTwig()->addGlobal('request', self::$parsedUrl);

        if(Security::hasAccess($controller)) {
            return $controller->$actionMethodName();
        }else{
            if(isset($_SESSION['loggedUserRole'])) {
                $controller = self::getControllerByName('index');
                $actionMethodName = "indexAction";
                Template::getTwig()->addGlobal('errorMessage', 'Access dennied');
                return $controller->$actionMethodName();
            }else{
                $controller = self::getControllerByName('user');
                $actionMethodName = "loginAction";
                return $controller->$actionMethodName();
            }
        }
    }

    /**
     * Analyzovat URL a vratit nazov pozadovaneho kontrollera, akcie a parametre
     * @param null|string $url
     * @return array
     */
    public static function parseUrl($url = null) {
        if ($url === null) {
            $url = $_SERVER['REQUEST_URI']; // url aktualnej poziadavky, ktora prisla na server
        }

        // odstrihnut 'baseUrl' a znak \ zo zaciatku a konca url
        $baseUrl = Config::get('app')['baseUrl'];
        $url = preg_replace("%^({$baseUrl}|/)%", '', $url); // baseUrl
        $url = preg_replace("%/$%", '', $url);              // lomitko na konci
        $url = preg_replace("%^/%", '', $url);              // lomitko na zaciatku
        // odstrihnem z konca url premenne zadane cez ? (GET)
        $url = preg_replace("%\\?.*$%", '', $url);

        $urlParts = explode('/', $url);

        $params = $_REQUEST; // doplnim parametre zadane cez ? (GET) a hodnoty ulozene v Cookies prehliadaca

        $c = array_shift($urlParts);
        $a = array_shift($urlParts);
        // musim osetrit aby sa mi nestalo, ze nazov controllera bude napriklad null alebo prazdny retazec ""
        $controller = (is_string($c) && strlen($c) > 0) ? $c : 'index';
        $action = (is_string($a) && strlen($a) > 0) ? $a : 'index';

        for ($i = 0; $i < count($urlParts); $i += 2) {
            $params[$urlParts[$i]] = $urlParts[$i + 1] ?? null;
        }

        self::$parsedUrl = [
            'controller' => preg_replace("/[^a-zA-Z0-9]/", '', $controller),
            'action'     => preg_replace("/[^a-zA-Z0-9]/", '', $action),
            'params'     => $params
        ];

        return self::$parsedUrl;
    }
}