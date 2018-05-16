<?php
/**
 * Created by PhpStorm.
 * User: mrppi
 * Date: 13.5.2018
 * Time: 10:20
 */

namespace App\Controller;

use App\Data\Subject;
use App\Security;
use App\Data\Classroom;
use App\Data\Users;
use App\Template;

class ClassMan
{
    public function indexAction(){
        Security::isGranted('admin');
        $classroomDO = new Classroom();
        $classrooms = $classroomDO->getClassrooms();

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
                return \App\Request::executeAction('classMan', 'index');
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
        $errorMessage = null;
        $classroomDO = new Classroom();
        $classid = \App\Request::getParam('classid');
        try {
            $classroomDO->deleteClass($classid);
        }catch (\Exception $e){
            if($e->getCode()== '23000'){
                $errorMessage = "Trieda ma stale ziakov alebo predmety";
            }else{
                $errorMessage = "Chyba: {$e->getMessage()}";
            }
        }
        $classrooms = $classroomDO->getClassrooms();
        return Template::getTwig()->render('classroom/index.twig',
            ['classrooms' => $classrooms, 'errorMessage' => $errorMessage]);
    }

    public function studentsAction(){
        $classroom = new Classroom();
        $classId = \App\Request::getParam('classid');
        $students = $classroom->getClassStudents($classId);
        $classname = $classroom->getClassName($classId);
        //print_r($students);
        return Template::getTwig()->render('classroom/students.twig',
            ['students' => $students, 'classId' => $classId, 'className' => $classname]);
    }

    public function addStudentAction(){

        $classId = \App\Request::getParam('classid');
        $studentLogin = \App\Request::getParam('studentlogin');
        $classroom = new Classroom();
        if($studentLogin!=null){
            $classroom->addStudent($studentLogin, $classId);
        }

        $userlist = $classroom->getStudents();
        $className = $classroom->getClassName($classId);
        //print_r($userlist);
        return Template::getTwig()->render('classroom/addStudent.twig',
            ['users' => $userlist, 'classId' => $classId, 'className' => $className]);
    }


    public function removeStudentAction(){
        $errorMessage = null;
        $classId = \App\Request::getParam('classid');
        $studentLogin = \App\Request::getParam('studentlogin');
        $classroomDO = new Classroom();
        try {
            $classroomDO->removeStudent($studentLogin, $classId);
        }catch (\Exception $e) {
            $errorMessage = "Chyba: {$e->getMessage()}";
        }

        $students = $classroomDO->getClassStudents($classId);
        $className = $classroomDO->getClassName($classId);

        return Template::getTwig()->render('classroom/students.twig',
            ['students' => $students, 'classId' => $classId, 'className' => $className, 'errorMessage' => $errorMessage]);
    }

    public function subjectsAction(){
        $classId = \App\Request::getParam('classid');

        $classroomDO = new Classroom();
        $subjects = $classroomDO->getSubjects($classId);
        $className = $classroomDO->getClassName($classId);
        //print_r($subjects);

        return Template::getTwig()->render('classroom/subjects.twig',
            ['subjects' => $subjects, 'classId' => $classId, 'className' => $className]);
    }

    public function addSubjectAction(){
        $classId = \App\Request::getParam('classid');
        $subjectId = \App\Request::getParam('subjectid');

        $errorMessage = null;
        $classroomDO = new Classroom();

        if(\App\Request::getParam('subjectid')!=null){
            $classroomDO->addSubject($subjectId, $classId);
        }

        $className = $classroomDO->getClassName($classId);
        $subjects = $classroomDO->getSelectableSubject($classId);

        return Template::getTwig()->render('classroom/addSubject.twig',
            ['subjects' => $subjects, 'classId' => $classId, 'className' => $className, 'errorMessage' => $errorMessage]);
    }


    public function removeSubjectAction(){
        $errorMessage = null;
        $classId = \App\Request::getParam('classid');
        $subjectId = \App\Request::getParam('subjectid');
        $classroomDO = new Classroom();
        try {
            $classroomDO->removeSubject($subjectId, $classId);
        }catch (\Exception $e) {
            $errorMessage = "Chyba: {$e->getMessage()}";
        }

        $subjects = $classroomDO->getSubjects($classId);
        $className = $classroomDO->getClassName($classId);

        return Template::getTwig()->render('classroom/subjects.twig',
            ['subjects' => $subjects, 'classId' => $classId, 'className' => $className, 'errorMessage' => $errorMessage]);
    }
}