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

    $imagePath = $this->handleFileUpload($_FILES['image']);
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
      if ($imagePath && file_exists(_PROJECT_ROOT.'/public'.$imagePath)) {
        unlink(_PROJECT_ROOT.'/public'.$imagePath);
      }
      error_log("Product creation error: ".$e->getMessage());
      Helpers::sendJsonResponse(false, 'Tạo sản phẩm thất bại do lỗi hệ thống.', null, 500);
    }
  }

  public function update($productId) {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    $id = intval($productId);
    $productModel = new Product();
    $product = $productModel->getProduct($id);

    if (!$product) {
      Helpers::sendJsonResponse(false, 'Sản phẩm không tồn tại.', null, 404);
    }

    $errors = $this->validateProductData($_POST, $_FILES, false);
    if (!empty($errors)) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', $errors, 422);
    }

    $imagePath = $product['image_path']; // Giữ lại ảnh cũ mặc định
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $newImagePath = $this->handleFileUpload($_FILES['image']);
      if ($newImagePath) {
        // Xóa ảnh cũ nếu upload ảnh mới thành công
        if ($imagePath && file_exists(_PROJECT_ROOT.'/public'.$imagePath)) {
          unlink(_PROJECT_ROOT.'/public'.$imagePath);
        }
        $imagePath = $newImagePath;
      } else {
        Helpers::sendJsonResponse(false, 'Tải lên hình ảnh mới thất bại.', null, 500);
      }
    }

    try {
      $success = $productModel->updateProduct($id, $_POST, $imagePath);
      if ($success) {
        Helpers::sendJsonResponse(true, 'Cập nhật sản phẩm thành công.');
      } else {
        throw new Exception('Không thể cập nhật sản phẩm.');
      }
    } catch (Exception $e) {
      error_log("Product update error: ".$e->getMessage());
      Helpers::sendJsonResponse(false, 'Cập nhật sản phẩm thất bại do lỗi hệ thống.', null, 500);
    }
  }


  public function delete($productId) {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    $id = intval($productId);
    $productModel = new Product();
    $product = $productModel->getProduct($id);

    if (!$product) {
      Helpers::sendJsonResponse(false, 'Sản phẩm không tồn tại.', null, 404);
    }

    try {
      $isDeleted = $productModel->deleteProduct($id);
      if ($isDeleted) {
        // Xóa file ảnh
        if ($product['image_path'] && file_exists(_PROJECT_ROOT.'/public'.$product['image_path'])) {
          unlink(_PROJECT_ROOT.'/public'.$product['image_path']);
        }
        Helpers::sendJsonResponse(true, 'Xóa sản phẩm thành công.');
      } else {
        throw new Exception('Không thể xóa sản phẩm.');
      }
    } catch (Exception $e) {
      error_log("Product delete error: ".$e->getMessage());
      Helpers::sendJsonResponse(false, 'Xóa sản phẩm thất bại do lỗi hệ thống.', null, 500);
    }
  }


  public function getCartProducts() {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $productIds = $data['productIds'] ?? [];

    if (empty($productIds) || !is_array($productIds)) {
      Helpers::sendJsonResponse(true, 'Không có sản phẩm nào trong giỏ hàng.', []);
      return;
    }

    $sanitizedIds = array_map('intval', $productIds);

    try {
      $productModel = new Product();
      $products = $productModel->getProductsByIds($sanitizedIds);
      Helpers::sendJsonResponse(true, 'Lấy thông tin sản phẩm thành công.', $products);
    } catch (Exception $e) {
      error_log("Get cart products error: ".$e->getMessage());
      Helpers::sendJsonResponse(false, 'Lỗi hệ thống khi lấy thông tin sản phẩm.', null, 500);
    }
  }

  private function validateProductData($post, $files, $isCreating = true) {
    $errors = [];

    if (empty($post['product_name']))
      $errors['product_name'][] = "Tên sản phẩm không được để trống.";
    elseif (strlen($post['product_name']) < 3)
      $errors['product_name'][] = "Tên sản phẩm phải có ít nhất 3 ký tự.";

    if (empty($post['price']) || !is_numeric($post['price']) || $post['price'] < 0)
      $errors['price'][] = "Giá sản phẩm không hợp lệ.";

    if (!isset($post['stock_quantity']) || !is_numeric($post['stock_quantity']) || $post['stock_quantity'] < 0)
      $errors['stock_quantity'][] = "Số lượng không hợp lệ.";

    if (empty($post['category_id']))
      $errors['category_id'][] = "Vui lòng chọn danh mục.";
    else {
      $categoryModel = new Category();
      if (!$categoryModel->exists($post['category_id']))
        $errors['category_id'][] = "Danh mục không tồn tại.";
    }

    if ($isCreating) {
      if (empty($files['image']['name']))
        $errors['image'][] = "Cần một hình ảnh cho sản phẩm.";
      elseif ($files['image']['error'] !== UPLOAD_ERR_OK)
        $errors['image'][] = "Lỗi tải lên hình ảnh.";
    }

    if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
      $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
      if (!in_array($files['image']['type'], $allowedTypes))
        $errors['image'][] = 'Chỉ chấp nhận file ảnh (JPEG, PNG, WEBP).';

      $maxFileSize = 5 * 1024 * 1024; // 5MB
      if ($files['image']['size'] > $maxFileSize)
        $errors['image'][] = "Hình ảnh không được vượt quá 5MB.";
    }

    return $errors;
  }


  private function handleFileUpload($file) {
    $uploadDir = '/uploads/products/';
    $fullUploadDir = _PROJECT_ROOT.'/public'.$uploadDir;

    if (!is_dir($fullUploadDir)) {
      mkdir($fullUploadDir, 0777, true);
    }

    $fileName = uniqid().'-'.basename($file['name']);
    $targetPath = $fullUploadDir.$fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
      return $uploadDir.$fileName;
    } else {
      return false;
    }
  }
}

