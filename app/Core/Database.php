<?php
namespace App\Core;

use PDO, Exception, PDOException;

class Database {
  private $connect;
  public function __construct() {
    try {
      $options = [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      ];
      $dsn_template = "%s:host=%s;port=%s;dbname=%s";
      $dsn = sprintf($dsn_template, $_ENV["DB_DRIVER"], $_ENV["DB_HOST"], $_ENV["DB_PORT"], $_ENV["DB_DB"]);
      $this->connect = new PDO($dsn, $_ENV["DB_USER"], $_ENV["DB_PASS"], $options);
    } catch (Exception $ex) {
      $this->writeErrorLog($ex);
      exit();
    }
  }

  public function writeErrorLog(Exception $ex) {
    $log_message = "Lỗi: ".$ex->getMessage()."\n";
    $log_message .= "File: ".$ex->getFile()." Dòng: ".$ex->getLine()."\n";
    $log_message .= "Stack Trace: \n".$ex->getTraceAsString();
    error_log($log_message, 0);
  }

  public function getAll($sql, $params = []) {
    try {
      $stm = $this->connect->prepare($sql);
      $stm->execute($params);
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
      $this->writeErrorLog($ex);
      return false;
    }
  }

  public function countRows($sql, $params = []) {
    try {
      $stm = $this->connect->prepare($sql);
      $stm->execute($params);
      return $stm->rowCount();
    } catch (PDOException $ex) {
      $this->writeErrorLog($ex);
      return false;
    }
  }

  public function getOne($sql, $params = []) {
    try {
      $stm = $this->connect->prepare($sql);
      $stm->execute($params);
      return $stm->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
      $this->writeErrorLog($ex);
      return false;
    }
  }

  public function lastID() {
    try {
      return $this->connect->lastInsertId();
    } catch (PDOException $ex) {
      $this->writeErrorLog($ex);
      return false;
    }
  }

  public function insert(string $table, array $data, bool $ignore = false) {
    $keys = array_keys($data);
    $fields = implode(", ", array_map(fn ($key) => "`{$key}`", $keys));
    $places = ":".implode(",:", $keys);

    try {
      $insertS = !$ignore ? 'INSERT INTO' : 'INSERT IGNORE INTO';
      $sql = "$insertS `$table` ($fields) VALUES ($places)";
      $stm = $this->connect->prepare($sql);

      $success = $stm->execute($data);
      if ($success)
        return $this->lastID();
      return false;
    } catch (PDOException $ex) {
      $this->writeErrorLog($ex);
      return false;
    }
  }

  public function update(string $table, array $data, string $condition = "", array $params_condition = []) {
    $fields = implode(", ", array_map(fn ($key) => "`{$key}` = :{$key}", array_keys($data)));
    $sql = $condition ? "UPDATE `$table` SET $fields WHERE $condition" : "UPDATE `$table` SET $fields";

    try {
      $stm = $this->connect->prepare($sql);
      $all_params = array_merge($data, $params_condition);
      return $stm->execute($all_params);
    } catch (PDOException $ex) {
      $this->writeErrorLog($ex);
      return false;
    }
  }

  public function delete($table, $condition = "", $params_condition = []) {
    $sql = $condition ? "DELETE FROM `$table` WHERE $condition" : "DELETE FROM `$table`";

    try {
      $stm = $this->connect->prepare($sql);
      return $stm->execute($params_condition);
    } catch (PDOException $ex) {
      $this->writeErrorLog($ex);
      return false;
    }
  }

  public function beginTransaction() {
    return $this->connect->beginTransaction();
  }

  public function commit() {
    return $this->connect->commit();
  }

  public function rollBack() {
    return $this->connect->rollBack();
  }
}