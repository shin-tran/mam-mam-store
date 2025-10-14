<?php
namespace App\Middleware;

use App\Helpers\Helpers;

class PermissionMiddleware {
  public static function handle(object $userData, string $requiredPermission) {
    $userPermissions = $userData->data->permissions ?? [];
    $userRoles = $userData->data->roles ?? [];

    // admin truy cập mọi chức năng
    if (in_array('Super Admin', $userRoles))
      return;

    // Kiểm tra user có quyền hạn không
    if (!in_array($requiredPermission, $userPermissions)) {
      Helpers::sendJsonResponse(false, 'Bạn không có quyền thực hiện hành động này.', null, 403);
    }
  }
}