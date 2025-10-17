<?php
namespace App\Models;

use App\Core\Database;

class Order {
  private $db;

  public function __construct() {
    $this->db = new Database();
  }

  public function getAllOrders() {
    $sql = "SELECT o.*, u.full_name as `customer_name`
                FROM `orders` o
                JOIN `users` u ON o.user_id = u.id
                ORDER BY o.order_date DESC";
    return $this->db->getAll($sql);
  }

  public function getOrderDetailsById($orderId) {
    $sql = "SELECT
                    od.quantity,
                    od.price_at_purchase,
                    p.product_name,
                    p.image_path
                FROM `order_details` od
                JOIN `products` p ON od.product_id = p.id
                WHERE od.order_id = :order_id";
    return $this->db->getAll($sql, ['order_id' => $orderId]);
  }

  public function updateStatus($orderId, $status) {
    $allowed_statuses = ['pending', 'shipping', 'completed', 'cancelled'];
    if (!in_array($status, $allowed_statuses)) {
      return false;
    }
    return $this->db->update('orders', ['status' => $status], 'id = :id', ['id' => $orderId]);
  }

  // --- Dashboard Methods ---

  public function getThisMonthRevenue() {
    $sql = "SELECT SUM(`total_amount`) as `total_revenue`
                FROM `orders`
                WHERE status = 'completed' AND MONTH(`order_date`) = MONTH(CURRENT_DATE()) AND YEAR(`order_date`) = YEAR(CURRENT_DATE())";
    $result = $this->db->getOne($sql);
    return $result['total_revenue'] ?? 0;
  }

  public function getNewOrdersCount() {
    $sql = "SELECT COUNT(`id`) as `new_orders` FROM `orders` WHERE status = 'pending'";
    $result = $this->db->getOne($sql);
    return $result['new_orders'] ?? 0;
  }

  public function getRecentOrders($limit = 5) {
    $sql = "SELECT o.id, u.full_name as `customer_name`, o.order_date, o.total_amount, o.status
                FROM `orders` o
                JOIN `users` u ON o.user_id = u.id
                ORDER BY o.order_date DESC
                LIMIT :limit";
    return $this->db->getAll($sql, ['limit' => $limit]);
  }

  public function getOrdersByUserId(int $userId) {
    $sql = "SELECT * FROM `orders` WHERE `user_id` = :user_id ORDER BY `order_date` DESC";
    return $this->db->getAll($sql, ['user_id' => $userId]);
  }
}
