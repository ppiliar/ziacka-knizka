<?php
// inicializujem session
session_start(['name' => 'web3-projekt']);

require_once 'vendor/autoload.php';
// definujem konstantu, ktora bude obsahovat cestu k hlavnemu adresaru aplikacie
define('APPLICATION_PATH', __DIR__);

try {
// Spracovanie requestu (vygenerovanie HTML kodu odpovede)
    $html = \App\Request::route();
    echo $html;
} catch (Throwable $e) { // nie kazda vynimka je dedena z Exception
    echo "<h3>Chyba: {$e->getMessage()}</h3>";
    echo "<p>Subor: {$e->getFile()} [{$e->getLine()}]</p>";
    echo "<p>Call stack</p>";
    echo "<pre>";
    echo $e->getTraceAsString();
    echo "</pre>";
}



