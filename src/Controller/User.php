<?php
/**
 * Akcia tykajuce sa pouzivatelov
 */

namespace App\Controller;

use App\Db;
use App\Model\Classroom;
use App\Security;
use App\Template;
use App\Users;
use http\Env\Request;
use PHPUnit\Runner\Exception;

class User
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
        $users = new Users();
        $username = \App\Request::getParam('username');
        $users->deleteUser($username);
        return \App\Request::executeAction('user', 'index');
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
                return \App\Request::executeAction('user', 'index');
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
                return \App\Request::executeAction('user', 'index');
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

    /**
     * @return string
     * @throws \Exception
     */
    public function loginAction()
    {
        if (\App\Request::isGet()) { // ak bola stranka nacitana metodov GET
            // len zobrazenie formulara
            return Template::getTwig()->render('user/login.twig');
        } else if (\App\Request::isPost()) { // ak bola stranka nacitana metodov POST (zaslane data)
            // spracovanie prihlsenia
            $params = \App\Request::getParams();
            $username = $params['username'] ?? null;
            $password = $params['password'] ?? null;

            // otestovanie prihlasovacich udajov
            $user = new \App\Users();

            if ($user->authenticate($username, $password)) {
                $userData = $user->getUserData($username);
                // ulozit vybrane informacie o pouzivatelovi do session
                $_SESSION['loggedUser'] = $username;
                $_SESSION['loggedUserRole'] = $userData['role'];
                // prejdem na kontroler index a jeho akciu indexAction
                return \App\Request::executeAction('index', 'index');
            } else {
                return Template::getTwig()->render('user/login.twig', ['errorMessage' => 'Chybné meno alebo heslo!']);
            }
        }
    }

    /**
     * Odhlasenie pouzivatela (zrusenie session)
     * @return string
     * @throws \Exception
     */
    public function logoutAction()
    {
        // Zrusit vsetky udaje asociovane so session
        $_SESSION = array();

        // Vymazat session cookie z browsera pouzivatela
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // zrusit session
        session_destroy();

        return Template::getTwig()->render('user/login.twig', ['successMessage' => 'Boli ste úspešne odhlásený.']);
    }

    public function classroomsAction(){
        Security::isGranted('admin');
        $classroom = new Classroom();
        $classrooms = $classroom->getClassrooms();

        return Template::getTwig()->render('classroom/index.twig', ['classrooms' => $classrooms]);
    }

    public function editClassAction()
    {
        $classroom = new Classroom();
        $errorMessage = null;
        if (\App\Request::isPost()) {
            $classData = \App\Request::getParams();
            try {
                $classroom->save($classData);
                Template::getTwig()->addGlobal('successMessage', "Trieda {$classData['name']} bola uložena.");
                return \App\Request::executeAction('user', 'classrooms');
            } catch (\Exception $e) {
                $errorMessage = "Chyba: {$e->getMessage()}";
            }
        } else {
            $editClass = \App\Request::getParam('classname', false);
            if ($editClass) {
                $classData = $classroom->getClassData($editClass);
            } else {
                $classData = [];
            }
        }

        return Template::getTwig()->render('classroom/edit.twig', [
            'classData' => $classData,
            'errorMessage' => $errorMessage
        ]);
    }

    public function deleteClassAction()
    {
        $classroom = new Classroom();
        $classid = \App\Request::getParam('classid');
        $classroom->deleteClass($classid);
        return \App\Request::executeAction('user', 'classrooms');
    }

    public function showClassStudentsAction(){
        $classroom = new Classroom();
        $classId = \App\Request::getParam('classid');
        $students = $classroom->getClassStudents($classId);
        //print_r($students);
        return Template::getTwig()->render('classroom/students.twig', ['users' => $students]);
    }

    public function addStudentAction(){
        $users = new Users();
        $userlist = $users->getUsers();
        //print_r($students);
        return Template::getTwig()->render('classroom/addStudent.twig', ['students' => $userlist]);
    }

}