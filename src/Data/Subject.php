<?php
/**
 * Created by PhpStorm.
 * User: mrppi
 * Date: 13.5.2018
 * Time: 11:03
 */

namespace App\Data;

use App\Db;

class Subject
{
    /**
     * Ziskat vsetky predmety
     * @return array
     * @throws \Exception
     */
    public function getSubjects() {
        $db = Db::get();
        $result = $db->fetchAll("SELECT * FROM subjects");
        foreach ($result as &$uc){
            $id = $uc['teacher_login'];
            $tname = $db->fetchRow("SELECT meno,priezvisko,login FROM users WHERE login =?", [$id]);
            $uc['teacher_name']=$tname['login'];
        }
        return $result;
    }

    public function getSubject($subjectId){
        $db = Db::get();
        $result = $db->fetchRow("SELECT * FROM subjects WHERE id =?", [$subjectId]);
        return $result;
    }

    /**
     * Ulozit udaje o novom predmete
     * @param array $subjectData
     * @throws \Exception
     */
    public function save(array $subjectData) {
        // kontrola udajov
        if ($subjectData['id'] == '') {
            throw new \Exception("Id je povinný údaj!");
        }

        $db = \App\Db::get();
        $subjectExists = $db->fetchOne("SELECT COUNT(*) FROM subjects WHERE id = ?", [$subjectData['id']]);

        if ($subjectExists) {
            $db->update('subjects', $subjectData, ["id = ?", [$subjectData['id']]]);
        } else {
            $db->insert('subjects', $subjectData);
        }
    }

    public function deleteSubject($subjectId){
        $db = \App\Db::get();
        $db->delete('grades', ["subject_id = ?", [$subjectId]]);
        $db->delete('class_subjects', ["subject_id = ?", [$subjectId]]);
        $db->delete('subjects', ["id = ?", [$subjectId]]);
    }
}