<?php
/**
 * Akcia tykajuce sa pouzivatelov
 */

namespace App\Controller;

use App\Security;
use App\Template;

class Links {
    /**
     * @return string
     * @throws \Exception
     */
    public function indexAction() {
        Security::isGranted('ADMIN');
        $links = [
            [
                'name' => 'Web UMB',
                'url' => 'https://www.umb.sk'
            ],
            [
                'name' => 'GitLab FPV',
                'url' => 'https://gitlab.labs.fpv.umb.sk'
            ]
        ];

        return Template::getTwig()->render('links/index.twig', ['links' => $links]);
    }
}