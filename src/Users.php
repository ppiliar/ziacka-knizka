<?php
/**
 * Created by PhpStorm.
 * User: jsilaci
 * Date: 24.04.2018
 * Time: 22:12
 */

namespace App;


class Users {
    /**
     * Overenie spravnosti prihlasovacich udajov pouzivatela
     * @param $username
     * @param $password
     * @return bool
     * @throws \Exception
     */
    public function authenticate($username, $password) {
        $db = \App\Db::get();

        // pri overovani hesla som vyuzil hashovaciu funkciu sha1 implementovanu priamo v MySQL
        // alternativne som mohol heslo najskor zahashovat pomocou PHP funkcie sha1 a potom ho vlozit do SQL dopytu
        $result = $db->fetchOne("SELECT COUNT(*) FROM users WHERE login = ? AND heslo = sha1(?)"
            , [$username, $password]);

        return $result == 1;
    }

    /**
     * Ziskat data o pouzivatelovi z DB
     * @param $username
     * @return array
     * @throws \Exception
     */
    public function getUserData($username) {
        $db = Db::get();
        $userData = $db->fetchRow("SELECT * FROM users WHERE login = ?", [$username]);

        return $userData;
    }
}