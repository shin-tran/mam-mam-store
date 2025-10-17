<?php
namespace App\Controllers\Api;

use App\Helpers\Helpers;
use App\Models\User;
use Exception;

class UserController {

  public function getProfileInfo($userData) {
    $userId = $userData->data->userId;
    $userModel = new User();
    $user = $userModel->findUserById($userId);

    if ($user) {
      // Hủy các trường nhạy cảm trước khi gửi đi
      unset($user['password']);
      unset($user['email_verification_token']);
      unset($user['verification_expires_at']);
      unset($user['forgot_password_token']);
      unset($user['forgot_password_expires_at']);

      Helpers::sendJsonResponse(true, 'Lấy thông tin thành công.', $user);
    } else {
      Helpers::sendJsonResponse(false, 'Không tìm thấy người dùng.', null, 404);
    }
  }

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

  public function updateProfile($userData) {
    $userId = $userData->data->userId;
    $postData = $_POST;

    $errors = [];
    if (empty($postData['full_name'])) {
      $errors['full_name'][] = 'Họ tên không được để trống.';
    }
    if (!empty($postData['phone_number']) && !Helpers::isPhone($postData['phone_number'])) {
      $errors['phone_number'][] = 'Số điện thoại không hợp lệ.';
    }

    if (!empty($errors)) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', ['errors' => $errors], 422);
    }

    $userModel = new User();
    $isUpdated = $userModel->updateProfile($userId, $postData);

    if ($isUpdated) {
      Helpers::sendJsonResponse(true, 'Cập nhật thông tin thành công.');
    } else {
      Helpers::sendJsonResponse(false, 'Cập nhật thất bại hoặc không có gì thay đổi.', null, 400);
    }
  }

  public function updateAvatar($userData) {
    $userId = $userData->data->userId;

    if (empty($_FILES['avatar'])) {
      Helpers::sendJsonResponse(false, 'Vui lòng chọn một file ảnh.', null, 400);
    }

    $file = $_FILES['avatar'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
      Helpers::sendJsonResponse(false, 'Chỉ chấp nhận file ảnh (JPEG, PNG, WEBP).', null, 415);
    }

    // Handle file upload
    $uploadDir = '/uploads/avatars/';
    $fullUploadDir = _PROJECT_ROOT.'/public'.$uploadDir;
    if (!is_dir($fullUploadDir)) {
      mkdir($fullUploadDir, 0777, true);
    }
    $fileName = uniqid().'-'.basename($file['name']);
    $targetPath = $fullUploadDir.$fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
      $imagePath = $uploadDir.$fileName;
      $userModel = new User();
      $userModel->updateAvatar($userId, $imagePath);
      Helpers::sendJsonResponse(true, 'Cập nhật ảnh đại diện thành công.', ['avatar_path' => $imagePath]);
    } else {
      Helpers::sendJsonResponse(false, 'Tải ảnh lên thất bại.', null, 500);
    }
  }

  public function changePassword($userData) {
    $userId = $userData->data->userId;
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    $errors = [];
    if (empty($currentPassword))
      $errors['current_password'][] = 'Mật khẩu hiện tại không được trống.';
    if (empty($newPassword))
      $errors['new_password'][] = 'Mật khẩu mới không được trống.';
    if (strlen($newPassword) < 6)
      $errors['new_password'][] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    if ($newPassword !== $confirmPassword)
      $errors['confirm_password'][] = 'Mật khẩu xác nhận không khớp.';

    if (!empty($errors)) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', $errors, 422);
    }

    $userModel = new User();
    $user = $userModel->findUserById($userId);

    if (!$user || !password_verify($currentPassword, $user['password'])) {
      Helpers::sendJsonResponse(false, 'Mật khẩu hiện tại không chính xác.', null, 401);
    }

    $isChanged = $userModel->changePassword($userId, $newPassword);
    if ($isChanged) {
      Helpers::sendJsonResponse(true, 'Đổi mật khẩu thành công.');
    } else {
      Helpers::sendJsonResponse(false, 'Đổi mật khẩu thất bại, vui lòng thử lại.', null, 500);
    }
  }
}
