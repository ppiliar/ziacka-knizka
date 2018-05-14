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
        $studentId = $db->fetchOne("SELECT user_id FROM users WHERE login =?",  [$studentLogin]);
        $classId = $db->fetchOne("SELECT classroom_id FROM class_students WHERE student_id =?", [$studentId]);
        $subjects = $db->fetchAll("SELECT * FROM subjects 
                                        JOIN class_subjects ON class_subjects.subject_id = subjects.id
                                        WHERE classroom_id =?", [$classId]);
        //print_r($subjects);
        return $subjects;
    }

    public function getGrades($subjectId, $studentLogin){
        $db = Db::get();
        $studentId = $db->fetchOne("SELECT user_id FROM users WHERE login =?",  [$studentLogin]);
        $grades = $db->fetchAll("SELECT * FROM grades 
                                       LEFT JOIN subjects ON subjects.id = grades.subject_id 
                                       WHERE student_id =? AND subject_id =?", [$studentId, $subjectId]);
        foreach ($grades as &$grade){
            $grade['date'] = date('d.m.Y',strtotime($grade['date']));
        }
        //print_r($grades);
        return $grades;
    }
}