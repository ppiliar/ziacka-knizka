<?php
/**
 * Trieda pre pracu s databazou s vyuzitim PDO.
 */

namespace App;

class Db {
    /**
     * V tejto statickej premennej bude ulozena instancia triedy po prvom volani get()
     * @var Db
     */
    private static $instance = null;

    /**
     * @var \PDO
     */
    private $dbConn = null;

    private function __construct() {
        // konfiguracia pripojenia k DB
        $dbConfig = Config::get('database');

        // retazec definujuci pripojenie k DB
        $connectionString = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}";
        // pri spojeni sa bude pouzivat utf8
        $options = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        // vytvorenie objektu, ktory bude reprezentovat spojenie s DB
        $this->dbConn = new \PDO($connectionString, $dbConfig['username'], $dbConfig['password'], $options);

        $this->dbConn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->dbConn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * Stacicka funckia, ktora vrati instanciu triedy Db. Ak instancia este neexistuje tak ju vytvori.
     * @return Db
     */
    public static function get(): Db {
        if (self::$instance == null) {
            self::$instance = new Db();
        }

        return self::$instance;
    }

    /**
     * Ziskat vsetky riadky vysledku
     * @param string $sql
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function fetchAll(string $sql, array $params = []) {
        $stmt = $this->dbConn->prepare($sql);
        $stmt->execute($params);
        Logger::get()->debug("Db::fetchAll ({$sql})", $params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Vykonat SQL prikaz. Navratova hodnota je true/false podla toho, ci bolo vykonavanie uspesne
     * @param string $sql
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function execQuery(string $sql, array $params = []) {
        $stmt = $this->dbConn->prepare($sql);
        // pre pripad, ze by v premennej $params bolo asociativne pole pouzijem len hodnoty v poli
        Logger::get()->debug("Db::execQuery ({$sql})", $params);
        $result = $stmt->execute($params);
        if (!$result) {
            print_r($stmt->errorInfo());
        }
        return $result;
    }

    /**
     * Ziskat jeden riadok vysledku
     * @param string $sql
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function fetchRow(string $sql, array $params = []) {
        $stmt = $this->dbConn->prepare($sql);
        Logger::get()->debug("Db::fetchRow ({$sql})", $params);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Ziskat jednu hodnotu
     * @param string $sql
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function fetchOne(string $sql, array $params = []) {
        $stmt = $this->dbConn->prepare($sql);
        Logger::get()->debug("Db::fetchOne ({$sql})", $params);
        $stmt->execute($params);
        $result = $stmt->fetchColumn(0); // ziskat len hodnotu z nulteho riadku z prveho stlpca

        return $result;
    }

    /**
     * Vlozit data do tabulky
     * @param string $tableName
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function insert(string $tableName, array $params) {
        // nazvy stlpcov, do ktorych vkladam data budu v apostrofoch (pre istotu)
        $colNames = "`" . implode('`, `', array_keys($params)) . "`";
        // vytvorim si potrebny pocet "otaznikov do SQL prikazu"
        $poleOtaznikov = array_fill(0, count($params), '?');
        $otazniky = implode(', ', $poleOtaznikov);

        $sql = "INSERT INTO `{$tableName}` ({$colNames}) VALUES ($otazniky)";
        Logger::get()->debug("Db::insert ({$sql})", $params);
        // kedze pouziva SQL prikaz s otazniky, posielam len hodnoty parametrov nie aj ich indexy
        return $this->execQuery($sql, array_values($params));
    }

    /**
     * Upravit zaznam v tabulke
     * @param string $tableName
     * @param array $params
     * @param array $condition Podmienka vo forme pola ["id = ?", [1]]
     * @return bool
     * @throws \Exception
     */
    public function update(string $tableName, array $params, array $condition) {
        // pripravim si dvojice na updatovanie
        $updateValues = [];
        foreach ($params as $colName => $value) {
            $updateValues []= "`{$colName}` = ?";
        }
        $updates = implode(', ',$updateValues );

        $params = array_merge($params, $condition[1]);

        $sql = "UPDATE `{$tableName}` SET {$updates} WHERE {$condition[0]}";
        Logger::get()->debug("Db::update ({$sql})", $params);
        // kedze pouziva SQL prikaz s otazniky, posielam len hodnoty parametrov nie aj ich indexy
        return $this->execQuery($sql, array_values($params));
    }

    /**
     * Vymazat zaznam z tabulky
     * @param string $tableName
     * @param array $condition Podmienka vo forme pola ["id = ?", [1]]
     * @return bool
     * @throws \Exception
     */
    public function delete(string $tableName, array $condition) {
        $sql = "DELETE FROM `{$tableName}` WHERE {$condition[0]}";
        //echo "{$sql}}\n"; // vysledny SQL prikaz si mozem vypisat
        Logger::get()->debug("Db::delete ({$sql})", $condition);
        return $this->execQuery($sql, $condition[1]);
    }

    /**
     * Ziskat stlpec
     * @param string $sql
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function fetchColumn(string $sql, array $params = []) {
        $stmt = $this->dbConn->prepare($sql);
        Logger::get()->debug("Db::fetchColumn ({$sql})", $params);
        $stmt->execute($params);
        $result = $stmt->fetchAll(\PDO::FETCH_NUM);
        if (!$result) {
            return null;
        } else {
            return array_column($result, 0); // php funkcia vrati 0-ty stlpec pola
        }
    }
}