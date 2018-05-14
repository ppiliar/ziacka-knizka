<?php
/**
 * Created by PhpStorm.
 * User: mrppi
 * Date: 14.5.2018
 * Time: 10:01
 */

namespace App\Data;

use App\Db;

class Teacher
{
    public function getTeacherSubjects($teacherLogin)
    {
        $db = Db::get();
        $teacherId = $db->fetchOne("SELECT user_id FROM users WHERE login =?",[$teacherLogin]);
        $subjects = $db->fetchAll("SELECT * FROM subjects 
                                        JOIN class_subjects ON class_subjects.subject_id = subjects.id
                                        WHERE teacher_id =?", [$teacherId]);
        $classrooms = $db->fetchAll("SELECT * FROM classrooms");
        foreach ($subjects as &$subject) {
            $key = array_search($subject['classroom_id'],array_column($classrooms, 'id'),true);
            $subject['classroom_name'] = $classrooms[$key]['name'];
        }
        //print_r($subjects);
        return $subjects;
    }

    /**
     * @param $classId
     * @param $subjectId
     * @param $teacherLogin
     * @throws \Exception
     */
    public function getStudents($classId, $subjectId, $teacherLogin){
        $db = Db::get();
        $teacherId = $db->fetchOne("SELECT user_id FROM users WHERE login =?",[$teacherLogin]);
        $bondExist = $db->fetchRow("SELECT * FROM subjects WHERE id =? AND teacher_id =?",[$subjectId, $teacherId]);
        if($bondExist!=null){
            $students = $db->fetchAll("SELECT meno,priezvisko,user_id FROM users 
                      LEFT JOIN class_students ON class_students.student_id = users.user_id 
                      WHERE class_students.classroom_id = ?", [$classId]);
        }else $students=null;

        return $students;
    }

    public function getGrades($studentId, $subjectId){
        $db =Db::get();
        $grades = $db->fetchAll("SELECT * FROM grades WHERE student_id =? AND subject_id =?",
                                [$studentId, $subjectId]);
        foreach ($grades as &$grade){
            $grade['date'] = date('d.m.Y',strtotime($grade['date']));
        }

        return $grades;

    }

    public function editGrade($gradeId, $gradeData){
        $db = Db::get();
        $db->update("grades", $gradeData, ["grade_id = ?", $gradeId] );

    }

    public function getGrade($gradeId){
        $db = \App\Db::get();
        $grade = $db->fetchRow("SELECT * FROM grades WHERE grade_id =?", [$gradeId]);

        return $grade;
    }

    public function save($gradeData){
        // kontrola udajov
        if ($gradeData['grade'] == '') {
            throw new \Exception("Známka je povinný údaj!");
        }

        $db = \App\Db::get();
        $classExists = $db->fetchOne("SELECT COUNT(*) FROM grades WHERE grade_id = ?", [$gradeData['grade_id']]);

        if ($classExists) {
            $db->update('grades', $gradeData, ["grade_id = ?", [$gradeData['grade_id']]]);
        } else {
            $db->insert('grades', $gradeData);
        }
    }

    public function deleteGrade($gradeId){
        $db = \App\Db::get();
        $grade = $db->fetchRow("SELECT * FROM grades WHERE grade_id =?", [$gradeId]);
        $teacherId = $db->fetchOne("SELECT user_id FROM users WHERE login =?",[$_SESSION['loggedUser']]);
        $bondExist = $db->fetchRow("SELECT * FROM subjects WHERE id =? AND teacher_id =?",[$grade['subject_id'], $teacherId]);
        if($bondExist){
            $db->delete("grades", ["grade_id =?", [$gradeId]]);
        }
    }

    public function getSubjectName($subjectId){
        $db = \App\Db::get();
        return $db->fetchOne("SELECT name FROM subjects WHERE id=?", [$subjectId]);
    }

    public function getClassName($classId){
        $db = \App\Db::get();
        return $db->fetchOne("SELECT name FROM classrooms WHERE id=?", [$classId]);
    }

    public function getStudentName($studentId){
        $db = \App\Db::get();
        return $db->fetchRow("SELECT meno,priezvisko FROM users WHERE user_id =?", [$studentId]);
    }
}