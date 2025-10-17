<?php
namespace App\Controllers\Api;

use App\Helpers\Helpers;
use App\Models\Order;

class OrderController {
  public function getDetails($orderId) {
    $orderModel = new Order();
    $details = $orderModel->getOrderDetailsById($orderId);

    if ($details !== false) {
      Helpers::sendJsonResponse(true, 'Lấy chi tiết đơn hàng thành công.', $details);
    } else {
      Helpers::sendJsonResponse(false, 'Không thể lấy chi tiết đơn hàng.', null, 500);
    }
  }

  public function updateStatus($orderId) {
    $status = $_POST['status'] ?? '';

    $orderModel = new Order();
    $isSuccess = $orderModel->updateStatus($orderId, $status);

    if ($isSuccess) {
      Helpers::sendJsonResponse(true, 'Cập nhật trạng thái đơn hàng thành công.');
    } else {
      Helpers::sendJsonResponse(false, 'Cập nhật trạng thái thất bại.', null, 400);
    }
  }
}
