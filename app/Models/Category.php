<?php
namespace App\Models;

use App\Core\Database;

class Category {
  private $db;

  public function __construct() {
    $this->db = new Database();
  }

  /**
   * Lấy tất cả các danh mục.
   */
  public function getAll() {
    $sql = "SELECT * FROM categories ORDER BY category_name ASC"; // sort ABC...
    return $this->db->getAll($sql);
  }

  public function exists($categoryId) {
    $sql = "SELECT * FROM `categories` WHERE `id` = :id";
    return $this->db->getOne($sql, ['id' => $categoryId]);
  }
}
