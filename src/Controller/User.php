<?php
/**
 * Akcia tykajuce sa pouzivatelov
 */

namespace App\Controller;

use App\Db;
use App\Data\Classroom;
use App\Security;
use App\Template;
use App\Data\Users;
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

        return Template::getTwig()->render('user/index.twig');
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
            $user = new Users();

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

    public function settingsAction()
    {
        $errorMessage = null;
        if (\App\Request::isPost()) {
            $users = new Users();
            $password1 = \App\Request::getParam('heslo1');
            $password2 = \App\Request::getParam('heslo2');
            try {
                if ($password1 != $password2) {
                    throw new \Exception("Heslá sa nezhodujú!");
                }

                $users->setPassword($_SESSION['loggedUser'], $password1);
                Template::getTwig()->addGlobal('successMessage', "Heslo pre používateľa {$_SESSION['loggedUser']} bolo nastavené.");
                return \App\Request::executeAction('index', 'index');
            } catch (\Exception $e) {
                $errorMessage = "Chyba: {$e->getMessage()}";
            }
        }
        return Template::getTwig()->render('settings/psswd.twig', [
            'username' => $_SESSION['loggedUser'],
            'errorMessage' => $errorMessage
        ]);
    }
}