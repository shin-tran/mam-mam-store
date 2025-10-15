<?php
namespace App\Models;

use App\Core\Database;
use PDOException;

class Product {
  private $db;

  public function __construct() {
    $this->db = new Database();
  }

  public function getAllProducts() {
    $sql = "SELECT
              p.*,
              c.category_name
            FROM products p
            INNER JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC";
    return $this->db->getAll($sql);
  }

  public function getProduct($productId) {
    $sql = "SELECT
              p.*,
              c.category_name
            FROM products p
            INNER JOIN categories c ON p.category_id = c.id
            WHERE p.id = :id";
    return $this->db->getOne($sql, ['id' => $productId]);
  }

  public function createProduct($postData, $imagePath) {
    $this->db->beginTransaction();
    try {
      $data = array_merge($postData, ['image_path' => $imagePath]);

      $productId = $this->db->insert('products', $data);
      if (!$productId) {
        $this->db->rollBack();
        return false;
      }

      $this->db->commit();
      return true;
    } catch (PDOException $ex) {
      $this->db->rollBack();
      $this->db->writeErrorLog($ex);
      return false;
    }
  }
}
