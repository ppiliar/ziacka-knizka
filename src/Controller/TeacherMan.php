<?php
/**
 * Created by PhpStorm.
 * User: mrppi
 * Date: 14.5.2018
 * Time: 9:57
 */

namespace App\Controller;

use App\Security;
use App\Data\Teacher;
use App\Template;

class TeacherMan
{
    public function indexAction(){
        Security::isGranted(['admin', 'teacher']);
        $teacherLogin = $_SESSION['loggedUser'];
        $teacherDO = new Teacher();
        $subjects = $teacherDO->getTeacherSubjects($teacherLogin);

        //print_r($subjects);
        return Template::getTwig()->render('teacher/index.twig', ['subjects' => $subjects]);
    }

    public function subjectAction(){
        $subjectId = \App\Request::getParam('subjectid');
        $classId = \App\Request::getParam('classid');
        $teacherLogin = $_SESSION['loggedUser'];
        $teacherDO = new Teacher();
        $subjectName = $teacherDO->getSubjectName($subjectId);
        $className = $teacherDO->getClassName($classId);
        $students = $teacherDO->getStudents($classId,$subjectId,$teacherLogin);

        return Template::getTwig()->render('teacher/subject.twig',
            ['students' => $students, 'subjectId' => $subjectId,
                'subjectName' => $subjectName, 'className' => $className, 'classId' => $classId]);
    }

    public function studentGradesAction(){
        $subjectId = \App\Request::getParam('subjectid');
        $studentLogin = \App\Request::getParam('studentlogin');
        $classId = \App\Request::getParam('classid');

        $teacherDO = new Teacher();
        $grades = $teacherDO->getGrades($studentLogin, $subjectId);
        $studentName = $teacherDO->getStudentName($studentLogin);
        $studentName = $studentName['meno']." ".$studentName['priezvisko'];
        $subjectName = $teacherDO->getSubjectName($subjectId);
        return Template::getTwig()->render('teacher/grades.twig',
            ['grades' => $grades, 'studentLogin' => $studentLogin, 'subjectId' => $subjectId,
                'studentName' => $studentName, 'subjectName' => $subjectName, 'classId' => $classId]);
    }

    public function editGradeAction(){
        $teacherDO = new Teacher();

        $errorMessage = null;
        if (\App\Request::isPost()) {
            $classId = \App\Request::getParam('classid');
            $gradeData = \App\Request::getParams();
            unset($gradeData['classid']);
            //print_r($gradeData);
            try {
                $teacherDO->save($gradeData);
                Template::getTwig()->addGlobal('successMessage', "Znamka bola uložena.");
                return \App\Request::executeAction('teacherMan', 'studentGrades',
                    ['studentlogin' => $gradeData['student_login'], 'subjectid' => $gradeData['subject_id'], 'classid' => $classId]);
            } catch (\Exception $e) {
                $errorMessage = "Chyba: {$e->getMessage()}";
            }
        } else {
            $studentLogin = \App\Request::getParam('studentlogin');
            $subjectId = \App\Request::getParam('subjectid');
            $classId = \App\Request::getParam('classid');
            $editGrade = \App\Request::getParam('gradeid', false);
            if ($editGrade) {
                $gradeData = $teacherDO->getGrade($editGrade, $_SESSION['loggedUser']);
            } else {
                $gradeData = [];
                $gradeData['student_login'] = $studentLogin;
                $gradeData['subject_id'] = $subjectId;
                $gradeData['class_id'] = $classId;
            }
        }
        return Template::getTwig()->render('teacher/edit.twig', [
            'gradeData' => $gradeData,
            'errorMessage' => $errorMessage,
            'classId' => $classId]);
    }


    public function deleteGradeAction()
    {
        $errorMessage = null;
        $teacherDO = new Teacher();
        $gradeId = \App\Request::getParam('gradeid');
        $classId = \App\Request::getParam('classid');
        $grade = $teacherDO->getGrade($gradeId);
        $studentLogin = $grade["student_login"];
        $subjectId = $grade["subject_id"];
        try {
            $teacherDO->deleteGrade($gradeId);
        }catch (\Exception $e){
            $errorMessage = "Chyba: {$e->getMessage()}";
        }
        return \App\Request::executeAction('teacherMan', 'studentGrades',
            ['studentlogin' => $studentLogin, 'subjectid' => $subjectId, 'classid' => $classId]);

    }


}