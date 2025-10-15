<?php
namespace App\Controllers\Api;

use App\Helpers\Helpers;
use App\Models\Product;
use App\Models\Category;
use Exception;

class ProductController {
  public function create() {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    $errors = $this->validateProductData($_POST, $_FILES);

    if (!empty($errors)) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', $errors, 422);
    }

    // Validate file types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    foreach ($_FILES['images']['type'] as $type) {
      if (!in_array($type, $allowedTypes)) {
        Helpers::sendJsonResponse(false, 'Chỉ chấp nhận file ảnh (JPEG, PNG, WEBP).', null, 422);
      }
    }

    $imagePath = $this->handleFileUpload($_FILES['images']);
    if ($imagePath === false) {
      Helpers::sendJsonResponse(false, 'Tải lên hình ảnh thất bại.', null, 500);
    }

    $productModel = new Product();
    try {
      $productId = $productModel->createProduct($_POST, $imagePath);
      if (!$productId) {
        throw new Exception('Không thể tạo sản phẩm');
      }

      Helpers::sendJsonResponse(true, 'Sản phẩm đã được tạo thành công.', ['id' => $productId], 201);
    } catch (Exception $e) {
      // Xóa file đã tải lên
      if (file_exists(_PROJECT_ROOT.'/public'.$imagePath)) {
        unlink(_PROJECT_ROOT.'/public'.$imagePath);
      }
      // Log error
      error_log("Product creation error: ".$e->getMessage());
      Helpers::sendJsonResponse(false, 'Tạo sản phẩm thất bại do lỗi hệ thống.', null, 500);
    }
  }

  private function validateProductData($post, $files) {
    $errors = [];

    // Validate product name
    if (empty($post['product_name'])) {
      $errors['product_name'][] = "Tên sản phẩm không được để trống.";
    } elseif (strlen($post['product_name']) < 3) {
      $errors['product_name'][] = "Tên sản phẩm phải có ít nhất 3 ký tự.";
    } elseif (strlen($post['product_name']) > 255) {
      $errors['product_name'][] = "Tên sản phẩm không được quá 255 ký tự.";
    }

    // Validate price
    if (empty($post['price']) || !is_numeric($post['price']) || $post['price'] < 0) {
      $errors['price'][] = "Giá sản phẩm không hợp lệ.";
    } elseif ($post['price'] > 999999999) {
      $errors['price'][] = "Giá sản phẩm không được vượt quá 999,999,999.";
    }

    // Validate stock quantity
    if (empty($post['stock_quantity']) || !is_numeric($post['stock_quantity']) || $post['stock_quantity'] < 0) {
      $errors['stock_quantity'][] = "Số lượng không hợp lệ.";
    } elseif (!ctype_digit($post['stock_quantity'])) {
      $errors['stock_quantity'][] = "Số lượng phải là số nguyên.";
    }

    // Validate category exists
    if (empty($post['category_id'])) {
      $errors['category_id'][] = "Vui lòng chọn danh mục.";
    } else {
      $categoryModel = new Category();
      if (!$categoryModel->exists($post['category_id'])) {
        $errors['category_id'][] = "Danh mục không tồn tại.";
      }
    }

    // Validate images
    if (empty($files['image']['name'])) {
      $errors['image'][] = "Cần một hình ảnh cho sản phẩm.";
    } else {
      $maxFileSize = 5 * 1024 * 1024; // 5MB
      if ($files['image']['size'] > $maxFileSize) {
        $errors['image'][] = "Hình ảnh không được vượt quá 5MB.";
      }
    }

    return $errors;
  }

  private function handleFileUpload($files) {
    $uploadDir = '/uploads/products/';
    $fullUploadDir = _PROJECT_ROOT.'/public'.$uploadDir;

    if (!is_dir($fullUploadDir)) {
      mkdir($fullUploadDir, 0777, true);
    }

    if ($files['error'] !== UPLOAD_ERR_OK) {
      return false;
    }

    $fileName = uniqid().'-'.basename($files['name']);
    $targetPath = "$fullUploadDir$fileName";

    if (move_uploaded_file($files['tmp_name'], $targetPath)) {
      return "$uploadDir$fileName";
    } else {
      return false;
    }
  }
}
