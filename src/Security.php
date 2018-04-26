<?php
/**
 * Created by PhpStorm.
 * User: jsilaci
 * Date: 18. 4. 2018
 * Time: 11:29
 */

namespace App;


class Security
{
    /**
     * Ak pouzivatel nema pozadovanu rolu aplikacia konci
     * @param string|array $roleNames Nazov pozadovanyj roly, alebo pole rol, ktore su povolene
     */
    public static function isGranted($roleNames) {
        if (is_string($roleNames)) {
            $roleNames = [$roleNames];
        }

        if (!isset($_SESSION['loggedUserRole']) || !in_array($_SESSION['loggedUserRole'], $roleNames)) {
            die("Neoprávnený prístup. Je vyžadovaná jedna z rôl " . implode(', ', $roleNames));
        }
    }
}