<?php
/**
 * Akcia tykajuce sa pouzivatelov
 */

namespace App\Controller;

use App\Template;

class Index {
    /**
     * @return string
     * @throws \Exception
     */
    public function indexAction() {
        return Template::getTwig()->render('index/index.twig');
    }
}