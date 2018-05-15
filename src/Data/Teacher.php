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
        $subjects = $db->fetchAll("SELECT * FROM subjects 
                                        JOIN class_subjects ON class_subjects.subject_id = subjects.id
                                        WHERE teacher_login =?", [$teacherLogin]);
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
        $bondExist = $db->fetchRow("SELECT * FROM subjects WHERE id =? AND teacher_login =?",[$subjectId, $teacherLogin]);
        if($bondExist!=null){
            $students = $db->fetchAll("SELECT meno,priezvisko,login FROM users
                      LEFT JOIN class_students ON class_students.student_login = users.login
                      WHERE class_students.classroom_id = ?", [$classId]);
        }else $students=null;

        return $students;
    }

    public function getGrades($studentLogin, $subjectId){
        $db =Db::get();
        $grades = $db->fetchAll("SELECT * FROM grades WHERE student_login =? AND subject_id =?",
                                [$studentLogin, $subjectId]);
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
        $bondExist = $db->fetchRow("SELECT * FROM subjects WHERE id =? AND teacher_login =?",[$grade['subject_id'], $_SESSION['loggedUser']]);
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

    public function getStudentName($studentLogin){
        $db = \App\Db::get();
        return $db->fetchRow("SELECT meno,priezvisko FROM users WHERE login =?", [$studentLogin]);
    }

    public function getTeachers(){
        $db = Db::get();
        $teachers = $db->fetchAll("SELECT login,meno,priezvisko FROM users WHERE role = ?", ['teacher']);
        return $teachers;
    }
}