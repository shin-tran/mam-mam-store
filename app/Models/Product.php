<?php
namespace App\Models;

use App\Core\Database;

class Product {
  private $db;

  public function __construct() {
    $this->db = new Database();
  }

  /**
   * Lấy tất cả sản phẩm cùng với hình ảnh đại diện của chúng.
   * Hình ảnh đại diện là hình ảnh có `display_order` nhỏ nhất.
   */
  public function getAllProductsWithImages() {
    $sql = "
      SELECT
        p.*,
        c.category_name,
        pi.image_url
      FROM products p
      LEFT JOIN categories c ON p.category_id = c.id
      LEFT JOIN (
          SELECT
              product_id,
              image_url
          FROM product_images
          WHERE (product_id, display_order) IN (
              SELECT
                  product_id,
                  MIN(display_order)
              FROM product_images
              GROUP BY product_id
          )
      ) AS pi ON p.id = pi.product_id
      ORDER BY p.created_at DESC
    ";
    return $this->db->getAll($sql);
  }
}
