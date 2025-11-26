<?php
namespace App\Controllers\Api;

use App\Helpers\Helpers;
use App\Models\Category;
use Exception;
use function intval;

class CategoryController {
  public function create() {
    $categoryName = $_POST['category_name'] ?? '';

    if (empty($categoryName)) {
      Helpers::sendJsonResponse(
        false,
        'Tên danh mục không được để trống.',
        null,
        422
      );
    }

    $categoryModel = new Category();
    if ($categoryModel->categoryNameExists($categoryName)) {
      Helpers::sendJsonResponse(
        false,
        'Tên danh mục này đã tồn tại.',
        null,
        409
      );
    }

    try {
      $categoryId = $categoryModel->createCategory($categoryName);
      if ($categoryId) {
        Helpers::sendJsonResponse(
          true,
          'Thêm danh mục thành công.',
          ['id' => $categoryId],
          201
        );
      } else {
        throw new Exception("Không thể tạo danh mục.");
      }
    } catch (Exception $e) {
      error_log("Category creation error: ".$e->getMessage());
      Helpers::sendJsonResponse(
        false,
        'Thêm danh mục thất bại do lỗi hệ thống.',
        null,
        500
      );
    }
  }

  public function update($categoryId) {
    $idToUpdate = intval($categoryId);
    $categoryName = $_POST['category_name'] ?? '';

    if (empty($categoryName)) {
      Helpers::sendJsonResponse(
        false,
        'Tên danh mục không được để trống.',
        null,
        422
      );
    }

    $categoryModel = new Category();
    if (!$categoryModel->exists($idToUpdate)) {
      Helpers::sendJsonResponse(false, 'Danh mục không tồn tại.', null, 404);
    }

    // Kiểm tra xem tên mới có bị trùng với tên của một danh mục khác không
    $existingCategory = $categoryModel->categoryNameExists($categoryName);
    if ($existingCategory && $existingCategory['id'] != $idToUpdate) {
      Helpers::sendJsonResponse(
        false,
        'Tên danh mục này đã tồn tại.',
        null,
        409
      );
    }

    try {
      $isSuccess = $categoryModel->updateCategory($idToUpdate, $categoryName);
      if ($isSuccess) {
        Helpers::sendJsonResponse(true, 'Cập nhật danh mục thành công.');
      } else {
        throw new Exception("Không thể cập nhật danh mục.");
      }
    } catch (Exception $e) {
      error_log("Category update error: ".$e->getMessage());
      Helpers::sendJsonResponse(
        false,
        'Cập nhật danh mục thất bại do lỗi hệ thống.',
        null,
        500
      );
    }
  }

  public function delete($categoryId) {
    $idToDelete = intval($categoryId);

    $categoryModel = new Category();
    if (!$categoryModel->exists($idToDelete)) {
      Helpers::sendJsonResponse(false, 'Danh mục không tồn tại.', null, 404);
    }

    // Kiểm tra xem danh mục có đang được sử dụng bởi sản phẩm nào không
    if ($categoryModel->isCategoryInUse($idToDelete)) {
      Helpers::sendJsonResponse(
        false,
        'Không thể xóa danh mục đang có sản phẩm.',
        null,
        400
      );
    }

    try {
      $isSuccess = $categoryModel->deleteCategory($idToDelete);
      if ($isSuccess) {
        Helpers::sendJsonResponse(true, 'Xóa danh mục thành công.');
      } else {
        throw new Exception("Không thể xóa danh mục.");
      }
    } catch (Exception $e) {
      error_log("Category delete error: ".$e->getMessage());
      Helpers::sendJsonResponse(
        false,
        'Xóa danh mục thất bại do lỗi hệ thống.',
        null,
        500
      );
    }
  }
}
