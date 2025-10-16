<?php
namespace App\Controllers\Api;

use App\Helpers\Helpers;
use App\Models\User;
use Exception;

class UserController {
  public function update($userId, $userData) {
    $idToUpdate = intval($userId);
    $role = $_POST['role_id'] ?? '';

    if (!$role) {
      Helpers::sendJsonResponse(false, 'Dữ liệu vai trò không hợp lệ.', null, 400);
    }

    if ($idToUpdate === $userData->data->userId) {
      Helpers::sendJsonResponse(false, 'Bạn không thể tự sửa thông tin của chính mình.', null, 403);
    }

    $userModel = new User();
    if (!$userModel->findUserById($idToUpdate)) {
      Helpers::sendJsonResponse(false, 'Người dùng không tồn tại.', null, 404);
    }

    // Server-side validation
    $errors = [];
    if (empty($_POST['full_name'])) {
      $errors['full_name'][] = 'Họ tên không được để trống.';
    }
    if (!empty($_POST['phone_number']) && !Helpers::isPhone($_POST['phone_number'])) {
      $errors['phone_number'][] = 'Số điện thoại không hợp lệ.';
    }
    // Check if phone number is already taken by another user
    if (!empty($_POST['phone_number'])) {
      $existingUser = $userModel->findUserByPhoneNumber($_POST['phone_number']);
      if ($existingUser && $existingUser['id'] != $idToUpdate) {
        $errors['phone_number'][] = 'Số điện thoại đã được sử dụng.';
      }
    }

    if (!empty($errors)) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', ['errors' => $errors], 422);
    }

    try {
      $isUpdated = $userModel->updateUserDetailsAndRole($idToUpdate, $_POST, $role);
      if ($isUpdated) {
        Helpers::sendJsonResponse(true, 'Cập nhật thông tin người dùng thành công.');
      } else {
        throw new Exception('Cập nhật thất bại.');
      }
    } catch (Exception $e) {
      error_log("User update error: ".$e->getMessage());
      Helpers::sendJsonResponse(false, 'Cập nhật thông tin thất bại do lỗi hệ thống.', null, 500);
    }
  }

  /**
   * Xử lý yêu cầu xóa người dùng.
   */
  public function delete($userId, $userData) {
    $idToDelete = intval($userId);

    // Không cho phép người dùng tự xóa chính mình
    if ($idToDelete === $userData->data->userId) {
      Helpers::sendJsonResponse(false, 'Bạn không thể tự xóa tài khoản của chính mình.', null, 400);
    }

    $userModel = new User();
    $userToDelete = $userModel->findUserById($idToDelete);

    if (!$userToDelete) {
      Helpers::sendJsonResponse(false, 'Người dùng không tồn tại.', null, 404);
    }

    // Ngăn chặn việc xóa admin
    $roleOfUserToDelete = $userModel->getRoleUser($idToDelete);
    if (in_array('admin', array_column($roleOfUserToDelete, 'name'))) {
      Helpers::sendJsonResponse(false, 'Không thể xóa tài khoản admin.', null, 403);
    }

    $isDeleted = $userModel->deleteUser($idToDelete);

    if ($isDeleted) {
      Helpers::sendJsonResponse(true, 'Đã xóa người dùng thành công.');
    } else {
      Helpers::sendJsonResponse(false, 'Xóa người dùng thất bại do lỗi hệ thống.', null, 500);
    }
  }
}
