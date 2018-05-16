<?php
/**
 * Created by PhpStorm.
 * User: mrppi
 * Date: 12.5.2018
 * Time: 14:11
 */

namespace App\Data;

use App\Db;
use PHPUnit\Runner\Exception;

class Classroom
{
    /**
     * Ziskat vsetky triedy
     * @return array
     * @throws \Exception
     */
    public function getClassrooms() {
        $db = Db::get();

        $classrooms = $db->fetchAll("SELECT * FROM classrooms");
        $students = $db->fetchAll("SELECT COUNT(student_login) AS nostudents FROM classrooms
                                        LEFT JOIN class_students ON class_students.classroom_id = classrooms.id
                                        GROUP BY classrooms.id");
        $subjects = $db->fetchAll("SELECT COUNT(subject_id) AS nosubjects FROM classrooms
                                        LEFT JOIN class_subjects ON class_subjects.classroom_id = classrooms.id
                                        GROUP BY classrooms.id");

        foreach ($classrooms as $key => &$classroom){
            $classroom['nostudents'] = $students[$key]['nostudents'];
            $classroom['nosubjects'] = $subjects[$key]['nosubjects'];
        }

        return $classrooms;
    }

    /**
     * Ziskat data o triede z DB
     * @param $classname
     * @return array
     * @throws \Exception
     */
    public function getClassData($classname) {
        $db = Db::get();
        $classData = $db->fetchRow("SELECT * FROM classrooms WHERE name = ?", [$classname]);

        return $classData;
    }

    public function getClassStudents($classId){
        $db = Db::get();
        /*$students = $db->fetchAll("SELECT login,meno,priezvisko,classrooms.name FROM users
                  LEFT JOIN class_students ON class_students.student_login = users.login
                  LEFT JOIN classrooms ON class_students.classroom_id = classrooms.id
                  WHERE class_students.classroom_id = ?", [$classId]);*/

        $students = $db->fetchAll("SELECT * FROM users 
                  LEFT JOIN class_students ON class_students.student_login = users.login
                  WHERE class_students.classroom_id = ?", [$classId]);
        return $students;
    }

    /**
     * Ulozit udaje o novej triede
     * @param array $classData
     * @throws \Exception
     */
    public function save(array $classData) {
        // kontrola udajov
        if ($classData['name'] == '') {
            throw new \Exception("Meno je povinný údaj!");
        }

        $db = \App\Db::get();
        $classExists = $db->fetchOne("SELECT COUNT(*) FROM classrooms WHERE id = ?", [$classData['id']]);

        if ($classExists) {
            $db->update('classrooms', $classData, ["id = ?", [$classData['id']]]);
        } else {
            $db->insert('classrooms', $classData);
        }
    }

    /**
     * @param $classid
     * @throws \Exception
     */
    public function deleteClass($classid) {
        $db = \App\Db::get();
        $db->delete('classrooms', ["id = ?", [$classid]]);
    }

    public function getUsers(){
        $db = \App\Db::get();
        $result = $db->fetchAll("SELECT * FROM users 
                                    LEFT JOIN class_students ON users.login = class_students.student_login
                                    LEFT JOIN classrooms ON class_students.classroom_id = classrooms.id");
        return $result;
    }

    public function getClassName($classId){
        $db = \App\Db::get();
        $result = $db->fetchOne("SELECT name FROM classrooms WHERE id =?", [$classId]);
        return $result;
    }

    public function getSubjects($classId){
        $db = \App\Db::get();
        $result = $db->fetchAll("SELECT * FROM subjects 
                                                LEFT JOIN class_subjects ON class_subjects.subject_id = subjects.id
                                                WHERE class_subjects.classroom_id = ?",[$classId]);
        foreach ($result as &$sub){
            $login = $sub['teacher_login'];
            $tname = $db->fetchRow("SELECT meno,priezvisko,login FROM users WHERE login =?", [$login]);
            $sub['teacher_name']=$tname['login'];
        }
        return $result;
    }

    public function getSelectableSubject($classId){
        $db = \App\Db::get();
        $subjects = $db->fetchAll("SELECT * FROM subjects");

        $result = $db->fetchAll("SELECT * FROM subjects
                                               LEFT JOIN class_subjects ON class_subjects.subject_id = id
                                              WHERE class_subjects.classroom_id =?", [$classId]);
        foreach ($subjects as $key => $subject) {
            foreach ($result as $res){
                if($res['id']==$subject['id']){
                    unset($subjects[$key]);
                }
            }
        }
        foreach ($subjects as &$sub){
            $login = $sub['teacher_name'];
            $name = $db->fetchRow("SELECT meno,priezvisko,login FROM users WHERE login =?", [$login]);
            $sub['teacher_name']=$name['login'];
        }

        return $subjects;
    }

    public function getStudents(){
        $db = \App\Db::get();
        $result = $db->fetchAll("SELECT * FROM users 
                                    LEFT JOIN class_students ON users.login = class_students.student_login
                                    LEFT JOIN classrooms ON class_students.classroom_id = classrooms.id
                                    WHERE role=?", ["student"]);
        return $result;
    }
    /**
     * Pridat ziaka do triedy
     * @param $studentLogin
     * @param $classId
     * @throws \Exception
     */
    public function addStudent($studentLogin, $classId){
        $db = \App\Db::get();
        $tableData['student_login'] = $studentLogin;
        $tableData['classroom_id'] = $classId;
        //print_r($tableData);
        $role = $db->fetchOne("SELECT role FROM users WHERE login =?", [$studentLogin]);
        if($role == 'student') {
            $studentExists = $db->fetchOne("SELECT COUNT(*) FROM class_students WHERE student_login = ?", [$studentLogin]);
            if ($studentExists) {
                $db->update('class_students', $tableData, ["student_login = ?", [$tableData['student_login']]]);
            } else {
                $db->insert("class_students", $tableData);
            }
        }
    }

    /**
     * Odobrat ziaka z triedy
     * @param $studentLogin
     * @param $classId
     * @throws \Exception
     */
    public function removeStudent($studentLogin, $classId){
        $db = \App\Db::get();
        $db->delete("class_students", ["student_login = ? AND classroom_id = ?", [$studentLogin,$classId]]);
    }

    /**
     * @param $subjectId
     * @param $classId
     * @throws \Exception
     */
    public function addSubject($subjectId, $classId){
        $db = \App\Db::get();
        $tableData['subject_id'] = $subjectId;
        $tableData['classroom_id'] = $classId;
        $subjectExists = $db->fetchOne("SELECT COUNT(*) FROM class_subjects 
                                                WHERE classroom_id = ? AND subject_id = ? ", [$classId, $subjectId]);
        if ($subjectExists) {
            $db->update('class_subjects', $tableData,
                ["subject_id = ? AND classroom_id = ?", [$tableData['subject_id'], $tableData['classroom_id']]]);
        }else {
            $db->insert("class_subjects", $tableData);
        }
    }

    /**
     * @param $subjectId
     * @param $classId
     * @throws \Exception
     */
    public function removeSubject($subjectId, $classId){
        $db = \App\Db::get();
        $db->delete("class_subjects", ["subject_id = ? AND classroom_id = ?", [$subjectId,$classId]]);
    }
}