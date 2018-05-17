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
     * @param string|array $roleNames Nazov pozadovanej roly, alebo pole rol, ktore su povolene
     */
    public static function isGranted($roleNames) {
        if (is_string($roleNames)) {
            $roleNames = [$roleNames];
        }

        if (!isset($_SESSION['loggedUserRole']) || !in_array($_SESSION['loggedUserRole'], $roleNames)) {
            die("Neoprávnený prístup. Je vyžadovaná jedna z rôl " . implode(', ', $roleNames));
        }
    }

    public static function hasAccess($controller){

        if($controller instanceof \App\Controller\Index){
            return true;
        }
        if($controller instanceof \App\Controller\UserMan){
            if(!isset($_SESSION['loggedUserRole']) || $_SESSION['loggedUserRole'] != 'admin'){
                return false;
            }else return true;
        }
        if($controller instanceof \App\Controller\User){
            if(!isset($_SESSION['loggedUserRole'])){
                return false;
            }else return true;
        }
        if($controller instanceof \App\Controller\TeacherMan){
            if(!isset($_SESSION['loggedUserRole']) || $_SESSION['loggedUserRole'] != 'teacher'){
                return false;
            }else return true;
        }
        if($controller instanceof \App\Controller\SubjectMan){
            if(!isset($_SESSION['loggedUserRole']) || $_SESSION['loggedUserRole'] != 'admin'){
                return false;
            }else return true;
        }
        if($controller instanceof \App\Controller\StudentMan){
            if(!isset($_SESSION['loggedUserRole']) || $_SESSION['loggedUserRole'] != 'student'){
                return false;
            }else return true;
        }
        if($controller instanceof \App\Controller\ClassMan){
            if(!isset($_SESSION['loggedUserRole']) || $_SESSION['loggedUserRole'] != 'admin'){
                return false;
            }else return true;
        }

    }
}