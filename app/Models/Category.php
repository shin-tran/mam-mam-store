<?php
namespace App\Models;

use App\Core\Database;

class Category {
  private $db;

  public function __construct() {
    $this->db = new Database();
  }

  public function getAll() {
    $sql = "SELECT * FROM `categories` ORDER BY `category_name` ASC";
    return $this->db->getAll($sql);
  }

  public function exists($categoryId) {
    $sql = "SELECT `id` FROM `categories` WHERE `id` = :id";
    return $this->db->getOne($sql, ['id' => $categoryId]);
  }

  public function categoryNameExists(string $name, $excludeId = null) {
    $sql = "SELECT `id` FROM `categories` WHERE `category_name` = :name";
    $params = ['name' => $name];
    if ($excludeId) {
      $sql .= " AND id != :id";
      $params['id'] = $excludeId;
    }
    return $this->db->getOne($sql, $params);
  }

  public function createCategory(string $name) {
    return $this->db->insert('categories', ['category_name' => $name]);
  }

  public function updateCategory(int $id, string $name) {
    return $this->db->update('categories', ['category_name' => $name], 'id = :id', ['id' => $id]);
  }

  public function deleteCategory(int $id) {
    return $this->db->delete('categories', 'id = :id', ['id' => $id]);
  }

  public function isCategoryInUse(int $categoryId) {
    $sql = "SELECT COUNT(*) as `count` FROM `products` WHERE `category_id` = :category_id";
    $result = $this->db->getOne($sql, ['category_id' => $categoryId]);
    return $result && $result['count'] > 0;
  }
}

