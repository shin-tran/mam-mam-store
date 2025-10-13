<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Helpers\Helpers;

class GuestMiddleware {
  public static function handle() {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

    if (isset($_COOKIE['refresh_token'])) {
      if (self::isApiRequest()) {
        Helpers::sendJsonResponse(false, 'Bạn đã đăng nhập.', null, 403);
      } else {
        header("Location: /");
        exit();
      }
    }

    if (!$authHeader)
      return;

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
      $token = $matches[1];
      try {
        $secretKey = $_ENV['ACCESS_TOKEN_SECRET'];
        JWT::decode($token, new Key($secretKey, 'HS256'));

        Helpers::sendJsonResponse(false, 'Bạn đã đăng nhập.', null, 403);
      } catch (\Exception $e) {
        return;
      }
    }
  }
  private static function isApiRequest() {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return strpos($requestUri, '/api/') === 0;
  }
}