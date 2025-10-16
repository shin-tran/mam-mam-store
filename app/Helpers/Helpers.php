<?php
namespace App\Helpers;

use App\Models\RefreshToken;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Helpers {
  public static function isPost() {
    return $_SERVER["REQUEST_METHOD"] === 'POST';
  }

  public static function isGet() {
    return $_SERVER["REQUEST_METHOD"] === 'GET';
  }

  public static function filterData($method = '') {
    $filterArr = [];
    $inputData = [];

    $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
    $method = empty($method) ? $requestMethod : strtoupper($method);

    if ($method === 'GET') {
      $inputData = $_GET;
    } else if ($method === 'POST') {
      $inputData = $_POST;
    }

    if (!empty($inputData)) {
      foreach ($inputData as $key => $value) {
        // loại bỏ các thẻ HTML, XML và PHP
        $cleanKey = strip_tags($key);
        if (is_array($value)) {
          $sanitizedValue = [];
          foreach ($value as $k => $v) {
            $sanitizedValue[strip_tags($k)] = trim(filter_var($v, FILTER_SANITIZE_SPECIAL_CHARS));
          }
          $filterArr[$cleanKey] = $sanitizedValue;
        } else {
          $filterArr[$cleanKey] = trim(filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS));
        }
      }
    }
    return $filterArr;
  }

  public static function validateEmail($email) {
    if (!empty($email)) {
      return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    return false;
  }

  public static function validateInt($number) {
    if (!empty($number)) {
      return filter_var($number, FILTER_VALIDATE_INT);
    }
    return false;
  }

  public static function isPhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (strpos($phone, '+84') === 0) {
      $phone = '0'.substr($phone, 3);
    }
    $regex = '/^(0)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-6|8|9]|9[0-4|6-9])[0-9]{7}$/';
    return preg_match($regex, $phone) > 0;
  }

  public static function getMsg($msg, $type) {
    echo "<div class=\"alert alert-{$type}\" role=\"alert\">{$msg}</div>";
  }

  public static function removePathFolder($requestPath) {
    if ($_ENV['BASE_PROJECT_NAME'] != '') {
      if (strpos($requestPath, $_ENV['BASE_PROJECT_NAME']) === 0) {
        $requestPath = substr($requestPath, strlen($_ENV['BASE_PROJECT_NAME']));
      }
    }

    if (empty($requestPath)) {
      $requestPath = '/';
    }

    return $requestPath;
  }

  public static function sendMail($emailTo, $subject, $content) {
    $mail = new PHPMailer(true);

    try {
      //Server settings
      $mail->SMTPDebug = SMTP::DEBUG_OFF;
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = $_ENV["EMAIL_SENDER"];
      $mail->Password = $_ENV["EMAIL_PASSWORD"];
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port = 465;

      //Recipients
      $mail->setFrom($_ENV["EMAIL_SENDER"], 'Măm Măm Store');
      $mail->addAddress($emailTo);

      //Content
      $mail->CharSet = "UTF-8";
      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body = $content;

      return $mail->send();
    } catch (Exception $ex) {
      echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      return false;
    }
  }

  // ?array: ? == Nullable Types
  public static function sendJsonResponse(bool $success, string $message, ?array $payload = null, int $httpCode = 200) {
    header('Content-Type: application/json');
    http_response_code($httpCode);

    $response = [
      'success' => $success,
      'message' => $message,
    ];

    if ($payload !== null) {
      if ($success)
        $response['data'] = $payload;
      else
        $response['errors'] = $payload;
    }

    echo json_encode($response);
    exit();
  }

  public static function redirect(string $path = '') {
    $url = _HOST_URL.$path;
    header("Location: {$url}");
    exit();
  }

  public static function isLoggedIn() {
    return isset($_COOKIE['refresh_token']);
  }

  public static function getCurrentUser() {
    return $_SESSION['user'] ?? null;
  }

  public static function userHasRole($role) {
    $user = self::getCurrentUser();
    if (!$user || !isset($user['roles'])) {
      return false;
    }
    if (is_array($role)) {
      // Check if user has any of the roles in the array
      return !empty(array_intersect($role, $user['roles']));
    }
    return in_array($role, $user['roles']);
  }

  public static function initializeUserSession() {
    // Nếu session đã có thông tin user, không cần làm gì cả
    if (isset($_SESSION['user'])) {
      return;
    }

    // Nếu không có refresh token trong cookie, user chưa đăng nhập
    $refreshToken = $_COOKIE['refresh_token'] ?? null;
    if (!$refreshToken) {
      return;
    }

    // Xác thực refresh token và lấy thông tin user
    $tokenModel = new RefreshToken();
    $refreshTokenHash = hash('sha256', $refreshToken);
    $tokenData = $tokenModel->findValidTokenByHash($refreshTokenHash);

    if ($tokenData) {
      $userModel = new User();
      $user = $userModel->findUserById($tokenData['user_id']);
      if ($user) {
        $roles = array_column($userModel->getRolesUser($user['id']), 'name');
        // Khôi phục thông tin vào session
        $_SESSION['user'] = [
          'id' => $user['id'],
          'full_name' => $user['full_name'],
          'email' => $user['email'],
          'roles' => $roles,
        ];
      }
    }
  }
}
