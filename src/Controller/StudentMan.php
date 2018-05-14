<?php
/**
 * Created by PhpStorm.
 * User: mrppi
 * Date: 14.5.2018
 * Time: 18:12
 */

namespace App\Controller;

use App\Security;
use App\Data\Student;
use App\Template;

class StudentMan
{
    public function indexAction(){
        Security::isGranted(['admin', 'student']);
        $studentLogin = $_SESSION['loggedUser'];
        $studentDO = new Student();
        $subjects = $studentDO->getStudentSubjects($studentLogin);

        //print_r($subjects);
        return Template::getTwig()->render('student/index.twig', ['subjects' => $subjects]);
    }

    public function subjectAction(){

        $subjectId = \App\Request::getParam('subjectid');

        $studentLogin = $_SESSION['loggedUser'];

        $studentDO = new Student();
        $grades = $studentDO->getGrades($subjectId, $studentLogin);
        return Template::getTwig()->render('student/subject.twig', ['grades' => $grades]);
    }
}