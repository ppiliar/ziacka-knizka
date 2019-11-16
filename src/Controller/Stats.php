<?php
/**
 * Created by PhpStorm.
 * User: mrppi
 * Date: 25.5.2018
 * Time: 9:14
 */

namespace App\Controller;

use App\Data\Subject;
use App\Security;
use App\Data\Classroom;
use App\Data\Users;
use App\Template;


class Stats
{

    public function indexAction(){
        $classroomDO = new Classroom();
        $classrooms = $classroomDO->getD();
        #$classrooms = $classroomDO->getStatsData();
        $subjects = $classroomDO->getAllSubjects();



        return Template::getTwig()->render('stats/index.twig',
            ['classrooms' => $classrooms, 'subjects' => $subjects]);
    }

}