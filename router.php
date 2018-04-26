<?php
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    // subory s vybranymi koncovkami posielam priamo
    return false;
} else {
    // vsetky ostatne poziadavky smerujem na index.php
    include __DIR__ . '/index.php';
}