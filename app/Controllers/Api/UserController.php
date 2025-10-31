<?php
namespace App\Controllers\Api;

use App\Helpers\Helpers;
use App\Helpers\Validator;
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

    $validator = Validator::make($_POST);
    $validator->required('full_name', 'Họ tên không được để trống.')
      ->phone('phone_number');

    if ($validator->fails()) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', $validator->getErrors(), 422);
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

    $validator = Validator::make($_POST);
    $validator->required('full_name', 'Họ tên không được để trống.')
      ->phone('phone_number');

    if ($validator->fails()) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', $validator->getErrors(), 422);
    }

    $userModel = new User();
    $isUpdated = $userModel->updateProfile($userId, $_POST);

    if ($isUpdated) {
      Helpers::sendJsonResponse(true, 'Cập nhật thông tin thành công.');
    } else {
      Helpers::sendJsonResponse(false, 'Cập nhật thất bại hoặc không có gì thay đổi.', null, 400);
    }
  }

  public function updateDetails($userData) {
    $userId = $userData->data->userId;
    $postData = $_POST;

    $validator = Validator::make($_POST);
    $validator->required('full_name', 'Họ tên không được để trống.')
      ->phone('phone_number');

    if ($validator->fails()) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', $validator->getErrors(), 422);
    }

    $userModel = new User();
    $isUpdated = $userModel->updateProfile($userId, $postData);

    if ($isUpdated) {
      // Cập nhật session nếu cần
      if (!empty($_SESSION['user'])) {
        $_SESSION['user']['full_name'] = $postData['full_name'];
        if (isset($postData['phone_number'])) {
          $_SESSION['user']['phone_number'] = $postData['phone_number'];
        }
        if (isset($postData['address'])) {
          $_SESSION['user']['address'] = $postData['address'];
        }
      }
      Helpers::sendJsonResponse(true, 'Cập nhật thông tin thành công.');
    } else {
      Helpers::sendJsonResponse(false, 'Cập nhật thất bại hoặc không có gì thay đổi.', null, 400);
    }
  }

  public function updateAvatar($userData) {
    if (!isset($_FILES['avatar'])) {
      Helpers::sendJsonResponse(false, 'Không có tệp nào được tải lên.', null, 400);
    }

    $userId = $userData->data->userId;
    $file = $_FILES['avatar'];

    // --- Xử lý tải lên tệp ---
    $uploadDir = '/uploads/avatars/';
    $fullUploadDir = _PROJECT_ROOT.'/public'.$uploadDir;

    if (!is_dir($fullUploadDir)) {
      mkdir($fullUploadDir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid('avatar_', true).'.'.$imageFileType;
    $targetPath = "$fullUploadDir$newFileName";
    $dbPath = "$uploadDir$newFileName";

    // Kiểm tra file ảnh
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
      Helpers::sendJsonResponse(false, 'Tệp không phải là hình ảnh.', null, 400);
    }

    // Kiểm tra kích thước file
    if ($file["size"] > 2000000) { // 2MB
      Helpers::sendJsonResponse(false, 'Kích thước ảnh quá lớn (tối đa 2MB).', null, 400);
    }

    // Cho phép các định dạng nhất định
    if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'webp'])) {
      Helpers::sendJsonResponse(false, 'Chỉ cho phép các định dạng JPG, JPEG, PNG & WEBP.', null, 400);
    }

    $userModel = new User();
    $currentUser = $userModel->findUserById($userId);
    $oldAvatarPath = $currentUser['avatar_path'] ?? null;

    if (move_uploaded_file($file["tmp_name"], $targetPath)) {
      // Cập nhật CSDL
      if ($userModel->updateAvatarPath($userId, $dbPath)) {
        // Xóa ảnh cũ nếu tồn tại
        if ($oldAvatarPath && file_exists(_PROJECT_ROOT.'/public'.$oldAvatarPath)) {
          unlink(_PROJECT_ROOT.'/public'.$oldAvatarPath);
        }

        // CẬP NHẬT SESSION
        $_SESSION['user']['avatar_path'] = $dbPath;

        Helpers::sendJsonResponse(true, 'Cập nhật ảnh đại diện thành công.', ['avatar_path' => $dbPath]);
      } else {
        // Xóa file mới tải lên nếu cập nhật DB thất bại
        unlink($targetPath);
        Helpers::sendJsonResponse(false, 'Lỗi khi cập nhật cơ sở dữ liệu.', null, 500);
      }
    } else {
      Helpers::sendJsonResponse(false, 'Lỗi khi tải tệp lên.', null, 500);
    }
  }

  public function changePassword($userData) {
    $userId = $userData->data->userId;

    $validator = Validator::make($_POST);
    $validator->required('current_password', 'Mật khẩu hiện tại không được trống.')
    ->required('new_password', 'Mật khẩu mới không được trống.')
    ->minLength('new_password', 6, 'Mật khẩu mới phải có ít nhất 6 ký tự.')
    ->matches($_POST['new_password'], $_POST['confirm_password'], 'Mật khẩu xác nhận không khớp.');

    if ($validator->fails()) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', $validator->getErrors(), 422);
    }

    $userModel = new User();
    $user = $userModel->findUserById($userId);

    if (!$user || !password_verify($_POST['current_password'], $user['password'])) {
      Helpers::sendJsonResponse(false, 'Mật khẩu hiện tại không chính xác.', null, 401);
    }

    $isChanged = $userModel->changePassword($userId, $_POST['new_password']);
    if ($isChanged) {
      Helpers::sendJsonResponse(true, 'Đổi mật khẩu thành công.');
    } else {
      Helpers::sendJsonResponse(false, 'Đổi mật khẩu thất bại, vui lòng thử lại.', null, 500);
    }
  }

  public function updatePassword($userData) {
    $userId = $userData->data->userId;

    $validator = Validator::make($_POST);
    $validator->required('current_password', 'Mật khẩu hiện tại không được trống.')
    ->required('new_password', 'Mật khẩu mới không được trống.')
    ->minLength('new_password', 6, 'Mật khẩu mới phải có ít nhất 6 ký tự.')
    ->matches($_POST['new_password'], $_POST['confirm_password'], 'Mật khẩu xác nhận không khớp.');

    if ($validator->fails()) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', $validator->getErrors(), 422);
    }

    $userModel = new User();
    $user = $userModel->findUserById($userId);

    if (!$user || !password_verify($_POST['current_password'], $user['password'])) {
      Helpers::sendJsonResponse(false, 'Mật khẩu hiện tại không chính xác.', null, 401);
    }

    $isChanged = $userModel->changePassword($userId, $_POST['new_password']);
    if ($isChanged) {
      Helpers::sendJsonResponse(true, 'Đổi mật khẩu thành công.');
    } else {
      Helpers::sendJsonResponse(false, 'Đổi mật khẩu thất bại, vui lòng thử lại.', null, 500);
    }
  }
}
