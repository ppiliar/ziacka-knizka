<?php
/**
 * Akcia tykajuce sa pouzivatelov
 */

namespace App\Controller;

use App\Db;
use App\Security;
use App\Template;
use http\Env\Request;

class User {
    /**
     * @return string
     * @throws \Exception
     */
    public function loginAction() {
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
                $_SESSION['loggedUserRole'] = $userData['rola'];
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
    public function logoutAction() {
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
}