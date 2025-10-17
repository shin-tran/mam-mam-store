<?php
namespace App\Controllers\Api;

use App\Helpers\Helpers;
use App\Models\Order;
use App\Models\Product;
use Exception;

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
    if (empty($cartData) || empty($shippingInfo['address']) || empty($shippingInfo['phone'])) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ. Vui lòng điền đủ thông tin.', null, 422);
      return;
    }
    if (!Helpers::isPhone($shippingInfo['phone'])) {
      Helpers::sendJsonResponse(false, 'Số điện thoại giao hàng không hợp lệ.', null, 422);
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
          throw new Exception("Sản phẩm '{$product['product_name']}' không đủ số lượng trong kho.");
        }

        $totalAmount += $product['price'] * $quantity;
        $validatedCartItems[] = [
          'id' => $product['id'],
          'quantity' => $quantity,
          'price' => $product['price'],
          'stock_quantity' => $product['stock_quantity']
        ];
      }

      $orderModel = new Order();
      $orderId = $orderModel->createOrder($userId, $validatedCartItems, $shippingInfo, $totalAmount);

      if ($orderId) {
        Helpers::sendJsonResponse(true, 'Đặt hàng thành công!', ['orderId' => $orderId], 201);
      } else {
        throw new Exception('Không thể tạo đơn hàng do lỗi hệ thống.');
      }

    } catch (Exception $e) {
      error_log("Order creation error: ".$e->getMessage());
      Helpers::sendJsonResponse(false, $e->getMessage(), null, 500);
    }
  }
}
