<?php

namespace App\Data;

use App\Db;
use App\Template;
use PHPUnit\Runner\Exception;

class Users {

    /**
     * zoznam vsetkych pouzivatelov
     * @return array
     * @throws \Exception
     */
    public function getUsers() {
        $db = \App\Db::get();
        $result = $db->fetchAll("SELECT * FROM users");
        return $result;
    }

    /**
     * vymazat pouzivatela z databazy
     * @param $username
     * @throws \Exception
     */
    public function deleteUser($username) {
        $db = \App\Db::get();
        $db->delete('users', ["login = ?", [$username]]);
    }

    /**
     * Zmena hesla pouzivatela
     * @param $username
     * @param $password
     */
    public function setPassword($username, $password) {
        $db = \App\Db::get();
        $userExists = $db->fetchOne("SELECT COUNT(*) FROM users WHERE login = ?", [$username]);
        if (!$userExists) {
            throw new Exception("Používateľ {$username} neexistuje");
        }
        $data = ['$password' => sha1($password)];
        $db->update('users', $data,["login = ?", [$username]]);
    }

    /**
     * Ulozit udaje o novom pouzivatelovi
     * @param array $userData
     * @throws \Exception
     */
    public function save(array $userData) {
        // kontrola udajov
        if ($userData['login'] == '') {
            throw new \Exception("Login je povinný údaj!");
        }

        $db = \App\Db::get();
        $userExists = $db->fetchOne("SELECT COUNT(*) FROM users WHERE login = ?", [$userData['login']]);

        if ($userExists) {
            $db->update('users', $userData, ["login = ?", [$userData['login']]]);
        } else {
            $userData['password'] = sha1('aaa');
            $db->insert('users', $userData);
        }
    }

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
        $result = $db->fetchOne("SELECT COUNT(*) FROM users WHERE login = ? AND password = sha1(?)"
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

    public function getTeachers(){
        $db = Db::get();
        $teachers = $db->fetchAll("SELECT login,meno,priezvisko,user_id FROM users"); //TODO WHERE role = teachers
        return $teachers;
    }
}