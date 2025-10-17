<?php
namespace App\Models;

use App\Core\Database;

class Order {
  private $db;

  public function __construct() {
    $this->db = new Database();
  }

  public function getAllOrders() {
    $sql = "SELECT o.*, u.full_name as customer_name
                FROM orders o
                JOIN users u ON o.user_id = u.id
                ORDER BY o.order_date DESC";
    return $this->db->getAll($sql);
  }

  public function getOrderDetailsById($orderId) {
    $sql = "SELECT
                    od.quantity,
                    od.price_at_purchase,
                    p.product_name,
                    p.image_path
                FROM order_details od
                JOIN products p ON od.product_id = p.id
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
}
