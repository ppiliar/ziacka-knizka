<?php
/**
 * Created by PhpStorm.
 * User: mrppi
 * Date: 13.5.2018
 * Time: 10:58
 */

namespace App\Controller;

use App\Data\Users;
use App\Security;
use App\Data\Subject;
use App\Template;

class SubjectMan
{
    public function indexAction(){
        Security::isGranted('admin');
        $subject = new Subject();
        $subjects= $subject->getSubjects();

        return Template::getTwig()->render('subject/index.twig', ['subjects' => $subjects]);
    }

    public function editAction(){

        $subjectDO = new Subject();
        $errorMessage = null;
        if (\App\Request::isPost()) {
            $subjectData = \App\Request::getParams();
            //print_r($subjectData);
            try {
                $subjectDO->save($subjectData);
                Template::getTwig()->addGlobal('successMessage', "Predmet {$subjectData['name']} bol uloženy.");
                return \App\Request::executeAction('subjectMan', 'index');
            } catch (\Exception $e) {
                $errorMessage = "Chyba: {$e->getMessage()}";
            }
        } else {
            $editSubject = \App\Request::getParam('id', false);
            if ($editSubject) {
                $subjectData = $subjectDO->getSubject($editSubject);
            } else {
                $subjectData = [];
            }
        }

        $users = new Users();
        $teachers = $users->getTeachers();
        return Template::getTwig()->render('subject/edit.twig', [
            'subject' => $subjectData,
            'teachers' => $teachers,
            'errorMessage' => $errorMessage
        ]);
    }


    public function deleteAction(){
        $subject = new Subject();
        $subjectId = \App\Request::getParam('id');
        $subject->deleteSubject($subjectId);
        return \App\Request::executeAction('subjectMan', 'index');
    }

}