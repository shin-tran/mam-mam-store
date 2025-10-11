<?php
namespace App\Middleware;

use App\Helpers\Helpers;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthMiddleware {
  public static function handle() {
    if (self::isApiRequest()) {
      return self::handleApiAuth();
    } else {
      self::handlePageAuth();
      return null;
    }
  }

  private static function handleApiAuth() {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
      Helpers::sendJsonResponse(false, 'Yêu cầu thiếu hoặc sai định dạng token.', null, 401);
    }

    $token = $matches[1];
    try {
      $secretKey = $_ENV['ACCESS_TOKEN_SECRET'];
      return JWT::decode($token, new Key($secretKey, 'HS256'));
    } catch (ExpiredException $e) {
      Helpers::sendJsonResponse(false, 'Token đã hết hạn.', ['code' => 'TOKEN_EXPIRED'], 401);
    } catch (SignatureInvalidException $e) {
      Helpers::sendJsonResponse(false, 'Token không hợp lệ (chữ ký sai).', null, 401);
    } catch (Exception $e) {
      Helpers::sendJsonResponse(false, 'Token không hợp lệ.', null, 401);
    }
  }

  private static function handlePageAuth() {
    if (!isset($_COOKIE['refresh_token'])) {
      Helpers::redirect('/login');
    }
  }

  private static function isApiRequest(): bool {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return strpos($requestUri, '/api/') === 0 ||
      (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
  }
}