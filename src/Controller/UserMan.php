<?php
/**
 * Created by PhpStorm.
 * User: mrppi
 * Date: 13.5.2018
 * Time: 10:18
 */

namespace App\Controller;

use App\Security;
use App\Data\Users;
use App\Template;

class UserMan
{
    /**
     * Zoznam pouzivatelov
     * @throws \Exception
     * @return string
     */
    public function indexAction()
    {
        Security::isGranted('admin'); // zobrazenie zoznamu pouzivatelov vyzaduje rolu ADMIN
        $users = new Users();
        $userList = $users->getUsers();

        return Template::getTwig()->render('user/index.twig', ['users' => $userList]);
    }

    /**
     * Vymazat pouzivatela
     * @return string
     * @throws \Exception
     */
    public function deleteAction()
    {
        $errorMessage = null;
        $users = new Users();
        $username = \App\Request::getParam('username');
        try {
            $users->deleteUser($username);
        }catch (\Exception $e){
            if($e->getCode()== '23000'){
                $errorMessage = "Nie je možné vymazať používateľa: Používateľ je stále zapísaný v triede";
            }else{
                $errorMessage = "Chyba: {$e->getMessage()}";
            }
        }
        //return \App\Request::executeAction('userMan', 'index', ['errorMessage' => $errorMessage]);
        $usersList = $users->getUsers();
        return Template::getTwig()->render('user/index.twig',
            ['users' => $usersList, 'errorMessage' => $errorMessage]);
    }

    public function passwdAction()
    {
        $username = \App\Request::getParam('username');
        $errorMessage = null;
        if (\App\Request::isPost()) {
            $users = new Users();
            $password1 = \App\Request::getParam('heslo1');
            $password2 = \App\Request::getParam('heslo2');
            try {
                if ($password1 != $password2) {
                    throw new \Exception("Heslá sa nezhodujú!");
                }

                $users->setPassword($username, $password1);
                Template::getTwig()->addGlobal('successMessage', "Heslo pre používateľa {$username} bolo nastavené.");
                return \App\Request::executeAction('userMan', 'index');
            } catch (\Exception $e) {
                $errorMessage = "Chyba: {$e->getMessage()}";
            }
        }

        return Template::getTwig()->render('user/passwd.twig', [
            'username' => $username,
            'errorMessage' => $errorMessage
        ]);
    }

    /**
     * Upravit alebo vytvorit noveho pouzivatela
     * @throws \Exception
     */
    public function editAction()
    {
        $users = new Users();
        $errorMessage = null;
        if (\App\Request::isPost()) {
            $userData = \App\Request::getParams();
            try {
                $users->save($userData);
                Template::getTwig()->addGlobal('successMessage', "Používateľ {$userData['login']} bol uložený.");
                return \App\Request::executeAction('userMan', 'index');
            } catch (\Exception $e) {
                $errorMessage = "Chyba: {$e->getMessage()}";
            }
        } else {
            $editUser = \App\Request::getParam('username', false);
            if ($editUser) {
                $userData = $users->getUserData($editUser);
            } else {
                $userData = [];
            }
        }

        return Template::getTwig()->render('user/edit.twig', [
            'userData' => $userData,
            'errorMessage' => $errorMessage
        ]);
    }

}