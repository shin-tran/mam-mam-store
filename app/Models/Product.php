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
              c.`category_name`
            FROM `products` p
            LEFT JOIN `categories` c ON p.`category_id` = c.`id`
            ORDER BY p.`created_at` DESC";
    return $this->db->getAll($sql);
  }

  public function getProduct($productId) {
    $sql = "SELECT
              p.*,
              c.`category_name`
            FROM `products` p
            LEFT JOIN `categories` c ON p.`category_id` = c.`id`
            WHERE p.`id` = :id";
    return $this->db->getOne($sql, ['id' => $productId]);
  }

  public function getProductsByIds(array $productIds) {
    if (empty($productIds)) {
      return [];
    }
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $sql = "SELECT p.*, c.`category_name`
              FROM `products` p
              LEFT JOIN `categories` c ON p.`category_id` = c.`id`
              WHERE p.id IN ($placeholders)";
    return $this->db->getAll($sql, $productIds);
  }

  public function createProduct($postData, $imagePath) {
    $data = [
      'product_name' => $postData['product_name'],
      'price' => $postData['price'],
      'stock_quantity' => $postData['stock_quantity'],
      'category_id' => $postData['category_id'],
      'description' => $postData['description'] ?? null,
      'image_path' => $imagePath,
    ];
    return $this->db->insert('products', $data);
  }

  public function updateProduct(int $id, array $postData, string $imagePath) {
    $data = [
      'product_name' => $postData['product_name'],
      'price' => $postData['price'],
      'stock_quantity' => $postData['stock_quantity'],
      'category_id' => $postData['category_id'],
      'description' => $postData['description'] ?? null,
      'image_path' => $imagePath,
    ];
    return $this->db->update(
      'products',
      $data,
      'id = :id',
      ['id' => $id]
    );
  }

  public function deleteProduct(int $id) {
    return $this->db->delete(
      'products',
      'id = :id',
      ['id' => $id]
    );
  }
}
