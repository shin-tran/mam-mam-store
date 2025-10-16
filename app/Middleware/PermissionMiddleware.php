<?php
namespace App\Middleware;

use App\Helpers\Helpers;

class PermissionMiddleware {
  public static function handle(object $userData, string $requiredPermission) {
    $userRoles = $userData->data->role ?? [];

    // admin truy cập mọi chức năng
    if (in_array('admin', $userRoles))
      return;

    // Kiểm tra user có quyền hạn không
    if (!in_array($requiredPermission, $userRoles)) {
      Helpers::sendJsonResponse(false, 'Bạn không có quyền thực hiện hành động này.', null, 403);
    }
  }
}
