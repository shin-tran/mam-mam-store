<?php
namespace App\Controllers\Api;

use App\Helpers\Helpers;
use App\Models\User;

class UserController {
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
    $rolesOfUserToDelete = $userModel->getRolesUser($idToDelete);
    if (in_array('admin', array_column($rolesOfUserToDelete, 'name'))) {
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
