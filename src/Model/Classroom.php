<?php
/**
 * Created by PhpStorm.
 * User: mrppi
 * Date: 12.5.2018
 * Time: 14:11
 */

namespace App\Model;

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
        $result = $db->fetchAll("SELECT *,
                                            COUNT(student_id) AS nostudents 
                                      FROM classrooms 
                                      LEFT JOIN class_students ON class_students.classroom_id = classrooms.id
                                      GROUP BY classrooms.id");
        return $result;
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
                  LEFT JOIN class_students ON class_students.student_id = users.user_id 
                  LEFT JOIN classrooms ON class_students.classroom_id = classrooms.id
                  WHERE class_students.classroom_id = ?", [$classId]);*/

        $students = $db->fetchAll("SELECT * FROM users 
                  LEFT JOIN class_students ON class_students.student_id = users.user_id 
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

    public function deleteClass($classid) {
        $db = \App\Db::get();
        $db->delete('classrooms', ["id = ?", [$classid]]);
    }

}