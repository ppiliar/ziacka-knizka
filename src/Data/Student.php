<?php
/**
 * Created by PhpStorm.
 * User: mrppi
 * Date: 14.5.2018
 * Time: 18:14
 */

namespace App\Data;

use App\Db;

class Student
{
    public function getStudentSubjects($studentLogin)
    {
        $db = Db::get();
        $classId = $db->fetchOne("SELECT classroom_id FROM class_students WHERE student_login =?", [$studentLogin]);
        $subjects = $db->fetchAll("SELECT * FROM subjects 
                                        JOIN class_subjects ON class_subjects.subject_id = subjects.id
                                        WHERE classroom_id =?", [$classId]);
        //print_r($subjects);
        return $subjects;
    }

    public function getGrades($subjectId, $studentLogin){
        $db = Db::get();
        $grades = $db->fetchAll("SELECT * FROM grades 
                                       LEFT JOIN subjects ON subjects.id = grades.subject_id 
                                       WHERE student_login =? AND subject_id =?", [$studentLogin, $subjectId]);
        foreach ($grades as &$grade){
            $grade['date'] = date('d.m.Y',strtotime($grade['date']));
        }
        //print_r($grades);
        return $grades;
    }
}