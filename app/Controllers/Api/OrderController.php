<?php
namespace App\Controllers\Api;

use App\Helpers\Helpers;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingConfig;
use Exception;
use function in_array;

class OrderController {
  public function getDetails($orderId) {
    $orderModel = new Order();
    $details = $orderModel->getOrderDetailsById($orderId);

    if ($details !== false) {
      Helpers::sendJsonResponse(
        true,
        'Lấy chi tiết đơn hàng thành công.',
        $details
      );
    } else {
      Helpers::sendJsonResponse(
        false,
        'Không thể lấy chi tiết đơn hàng.',
        null,
        500
      );
    }
  }

  public function updateStatus($orderId) {
    $status = $_POST['status'] ?? '';
    $cancellationReason = $_POST['cancellation_reason'] ?? null;
    $cancelledBy = $_POST['cancelled_by'] ?? 'admin';

    // Validation
    if (empty($status)) {
      Helpers::sendJsonResponse(
        false,
        'Trạng thái không được để trống.',
        null,
        422
      );
      return;
    }

    // Validate status value
    $validStatuses = [
      'pending',
      'packing',
      'shipping',
      'completed',
      'cancelled'
    ];
    if (!in_array($status, $validStatuses)) {
      Helpers::sendJsonResponse(false, 'Trạng thái không hợp lệ.', null, 422);
      return;
    }

    $orderModel = new Order();

    // Get current order status
    $order = $orderModel->getOrderById($orderId);
    if (!$order) {
      Helpers::sendJsonResponse(false, 'Đơn hàng không tồn tại.', null, 404);
      return;
    }

    $currentStatus = $order['status'];

    // Validate status transition
    $validationResult = $this->validateStatusTransition($currentStatus, $status);
    if (!$validationResult['valid']) {
      Helpers::sendJsonResponse(false, $validationResult['message'], null, 400);
      return;
    }

    // If status is cancelled, require cancellation reason
    if ($status === 'cancelled') {
      if (empty($cancellationReason)) {
        Helpers::sendJsonResponse(
          false,
          'Vui lòng cung cấp lý do hủy đơn hàng.',
          null,
          422
        );
        return;
      }

      // Validate cancelled_by
      $validCancelledBy = ['customer', 'admin', 'system'];
      if (!in_array($cancelledBy, $validCancelledBy)) {
        Helpers::sendJsonResponse(
          false,
          'Người hủy đơn không hợp lệ.',
          null,
          422
        );
        return;
      }
    }

    $updateData = [
      'status' => $status
    ];

    // Add cancellation info if status is cancelled
    if ($status === 'cancelled') {
      $updateData['cancellation_reason'] = $cancellationReason;
      $updateData['cancelled_by'] = $cancelledBy;
      $updateData['cancelled_at'] = date('Y-m-d H:i:s');
    }

    $isSuccess = $orderModel->updateStatus($orderId, $updateData);

    if ($isSuccess) {
      // Restore stock if order is cancelled
      if ($status === 'cancelled' && $currentStatus !== 'cancelled') {
        $orderModel->restoreProductStock($orderId);
      }

      Helpers::sendJsonResponse(true, 'Cập nhật trạng thái đơn hàng thành công.');
    } else {
      Helpers::sendJsonResponse(false, 'Cập nhật trạng thái thất bại.', null, 400);
    }
  }

  /**
   * Validate if status transition is allowed
   * @param string $currentStatus Current order status
   * @param string $newStatus New status to change to
   * @return array ['valid' => bool, 'message' => string]
   */
  private function validateStatusTransition($currentStatus, $newStatus) {
    // If status is the same, no need to update
    if ($currentStatus === $newStatus) {
      return [
        'valid' => false,
        'message' => 'Trạng thái mới giống với trạng thái hiện tại.'
      ];
    }

    // Define valid transitions for each status
    $validTransitions = [
      'pending' => ['packing', 'cancelled'],
      'packing' => ['shipping', 'cancelled'],
      'shipping' => ['completed', 'cancelled'],
      'completed' => [], // Cannot change from completed
      'cancelled' => []  // Cannot change from cancelled
    ];

    // Check if transition is valid
    if (!isset($validTransitions[$currentStatus])) {
      return [
        'valid' => false,
        'message' => 'Trạng thái hiện tại không hợp lệ.'
      ];
    }

    // Check if status transition is not valid
    if (!in_array($newStatus, $validTransitions[$currentStatus])) {
      $statusNames = [
        'pending' => 'Đang xử lý',
        'packing' => 'Đang đóng gói',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy'
      ];

      $currentName = $statusNames[$currentStatus] ?? $currentStatus;
      $newName = $statusNames[$newStatus] ?? $newStatus;

      if ($currentStatus === 'completed') {
        return [
          'valid' => false,
          'message' => 'Không thể thay đổi trạng thái của đơn hàng đã hoàn thành.'
        ];
      }

      if ($currentStatus === 'cancelled') {
        return [
          'valid' => false,
          'message' => 'Không thể thay đổi trạng thái của đơn hàng đã hủy.'
        ];
      }

      return [
        'valid' => false,
        'message' => "Không thể chuyển từ '{$currentName}' sang '{$newName}'. Vui lòng tuân theo quy trình đơn hàng."
      ];
    }

    return ['valid' => true, 'message' => ''];
  }

  public function create($userData) {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    $userId = $userData->data->userId;
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $cartData = $data['cartItems'] ?? [];
    $shippingInfo = $data['shippingInfo'] ?? [];

    // Basic validation
    if (
      empty($cartData) ||
      empty($shippingInfo['address']) ||
      empty($shippingInfo['phone'])
    ) {
      Helpers::sendJsonResponse(
        false,
        'Dữ liệu không hợp lệ. Vui lòng điền đủ thông tin.',
        null,
        422
      );
      return;
    }
    if (!Helpers::isPhone($shippingInfo['phone'])) {
      Helpers::sendJsonResponse(
        false,
        'Số điện thoại giao hàng không hợp lệ.',
        null,
        422
      );
      return;
    }

    try {
      $productModel = new Product();
      $productIds = array_column($cartData, 'productId');
      $productsFromDb = $productModel->getProductsByIds($productIds);

      // Create a map for easy lookup
      $productsMap = [];
      foreach ($productsFromDb as $p) {
        $productsMap[$p['id']] = $p;
      }

      $totalAmount = 0;
      $validatedCartItems = [];

      // Server-side validation and total calculation
      foreach ($cartData as $item) {
        $productId = $item['productId'];
        $quantity = $item['quantity'];

        if (!isset($productsMap[$productId])) {
          throw new Exception("Sản phẩm với ID {$productId} không tồn tại.");
        }

        $product = $productsMap[$productId];

        if ($product['stock_quantity'] < $quantity) {
          throw new Exception(
            "Sản phẩm '{$product['product_name']}' không đủ số lượng trong kho."
          );
        }

        $totalAmount += $product['price'] * $quantity;
        $validatedCartItems[] = [
          'id' => $product['id'],
          'product_name' => $product['product_name'],
          'image_path' => $product['image_path'],
          'quantity' => $quantity,
          'price' => $product['price'],
          'stock_quantity' => $product['stock_quantity']
        ];
      }

      // Calculate shipping fee
      $shippingConfigModel = new ShippingConfig();
      $subtotal = $totalAmount; // Tổng trước khi tính shiping fee
      $shippingFee = $shippingConfigModel->calculateShippingFee($subtotal);
      $totalAmount = $subtotal + $shippingFee; // Tổng sau khi tính shiping fee

      $orderModel = new Order();
      $orderId = $orderModel->createOrder(
        $userId,
        $validatedCartItems,
        $shippingInfo,
        $subtotal,
        $shippingFee,
        $totalAmount
      );

      if ($orderId) {
        Helpers::sendJsonResponse(
          true,
          'Đặt hàng thành công!',
          ['orderId' => $orderId],
          201
        );
      } else {
        throw new Exception('Không thể tạo đơn hàng do lỗi hệ thống.');
      }

    } catch (Exception $e) {
      error_log("Order creation error: ".$e->getMessage());
      Helpers::sendJsonResponse(false, $e->getMessage(), null, 500);
    }
  }

  /**
   * Hủy đơn hàng (dành cho khách hàng)
   */
  public function cancel($orderId, $userData) {
    $userId = $userData->data->userId;
    $json = file_get_contents('php://input'); // Đọc dữ liệu JSON gửi từ client (JavaScript)
    $data = json_decode($json, true); // Chuyển chuỗi JSON thành mảng PHP

    $cancellationReason = $data['cancellation_reason'] ?? '';

    if (empty($cancellationReason)) {
      Helpers::sendJsonResponse(
        false,
        'Vui lòng cung cấp lý do hủy đơn hàng.',
        null,
        422
      );
      return;
    }

    $orderModel = new Order();

    // Verify order belongs to user
    $order = $orderModel->getOrderById($orderId);
    if (!$order) {
      Helpers::sendJsonResponse(false, 'Đơn hàng không tồn tại.', null, 404);
      return;
    }

    if ($order['user_id'] != $userId) {
      Helpers::sendJsonResponse(
        false,
        'Bạn không có quyền hủy đơn hàng này.',
        null,
        403
      );
      return;
    }

    // Only allow cancellation for pending orders
    if ($order['status'] !== 'pending') {
      Helpers::sendJsonResponse(
        false,
        'Chỉ có thể hủy đơn hàng đang chờ xử lý.',
        null,
        400
      );
      return;
    }

    $updateData = [
      'status' => 'cancelled',
      'cancellation_reason' => $cancellationReason,
      'cancelled_by' => 'customer',
      'cancelled_at' => date('Y-m-d H:i:s')
    ];

    $isSuccess = $orderModel->updateStatus($orderId, $updateData);

    if ($isSuccess) {
      // Restore product stock
      $orderModel->restoreProductStock($orderId);
      Helpers::sendJsonResponse(true, 'Hủy đơn hàng thành công.');
    } else {
      Helpers::sendJsonResponse(false, 'Không thể hủy đơn hàng.', null, 500);
    }
  }
}
