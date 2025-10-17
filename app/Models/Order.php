<?php
namespace App\Models;

use App\Core\Database;
use Exception;

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

  public function createOrder(int $userId, array $cartItems, array $shippingInfo, float $totalAmount) {
    $this->db->beginTransaction();
    try {
      // 1. Chèn vào bảng orders
      $orderData = [
        'user_id' => $userId,
        'total_amount' => $totalAmount,
        'shipping_address' => $shippingInfo['address'],
        'shipping_phone' => $shippingInfo['phone'],
        'note' => $shippingInfo['note'] ?? null,
        'status' => 'pending'
      ];
      $orderId = $this->db->insert('orders', $orderData);
      if (!$orderId) {
        throw new Exception("Không thể tạo đơn hàng.");
      }

      // 2. Chèn vào order_details và cập nhật số lượng tồn kho
      foreach ($cartItems as $item) {
        $orderDetailData = [
          'order_id' => $orderId,
          'product_id' => $item['id'],
          'quantity' => $item['quantity'],
          'price_at_purchase' => $item['price']
        ];
        if (!$this->db->insert('order_details', $orderDetailData)) {
          throw new Exception("Không thể lưu chi tiết đơn hàng cho sản phẩm ID: ".$item['id']);
        }

        // 3. Cập nhật số lượng sản phẩm
        $sql = "UPDATE `products` SET `stock_quantity` = `stock_quantity` - ? WHERE `id` = ? AND `stock_quantity` >= ?";
        $updateSuccess = $this->db->query($sql, [$item['quantity'], $item['id'], $item['quantity']]);

        if ($updateSuccess->rowCount() == 0) {
          throw new Exception("Không đủ số lượng cho sản phẩm ID: ".$item['id']);
        }
      }

      $this->db->commit();
      return $orderId;
    } catch (Exception $e) {
      $this->db->rollBack();
      $this->db->writeErrorLog($e);
      return false;
    }
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
                LIMIT $limit";
    return $this->db->getAll($sql);
  }

  public function getOrdersByUserId(int $userId) {
    $sql = "SELECT * FROM `orders` WHERE `user_id` = :user_id ORDER BY `order_date` DESC";
    return $this->db->getAll($sql, ['user_id' => $userId]);
  }
}
