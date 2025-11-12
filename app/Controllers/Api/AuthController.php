<?php
namespace App\Controllers\Api;

use Exception, App\Models\User;
use App\Helpers\Helpers;
use App\Helpers\Validator;
use DateTime, DateInterval;
use Firebase\JWT\JWT;
use App\Models\RefreshToken;
use DateTimeImmutable;

class AuthController {
  public function checkEmail() {
    $email = $_POST['email'];

    if (empty($email)) {
      Helpers::sendJsonResponse(false, 'Email không được để trống', null, 400);
    }

    try {
      $userModel = new User();
      $exists = $userModel->emailExists($email);
      Helpers::sendJsonResponse(true, 'Kiểm tra email thành công', ['exists' => $exists]);
    } catch (Exception $e) {
      error_log("Check email failed: ".$e->getMessage());
      Helpers::sendJsonResponse(false, 'Lỗi hệ thống', null, 500);
    }

    exit();
  }

  public function handleRegister() {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    $validator = Validator::make($_POST);
    $validator->required('full_name', 'Họ tên không được bỏ trống!')
      ->minLength('full_name', 5, 'Họ tên phải có ít nhất 5 ký tự!')
      ->required('email', 'Email không được bỏ trống!')
      ->email('email')
      ->emailUnique('email')
      ->required('password', 'Mật khẩu không được để trống!')
      ->minLength('password', 6, 'Mật khẩu phải lớn hơn 6 ký tự!')
      ->required('confirm_password', 'Hãy nhập lại mật khẩu!')
      ->matches('confirm_password', 'password', 'Mật khẩu không khớp!');

    if ($validator->fails()) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', $validator->getErrors(), 422);
    }

    $userModel = new User();
    $emailVerificationToken = bin2hex(random_bytes(32));
    $userId = $userModel->createUser($_POST, $emailVerificationToken);
    $isSetRole = $userModel->setRoleUser($_ENV['DEFAULT_USER_ROLE'], $userId);
    if (!$userId && !$isSetRole) {
      Helpers::sendJsonResponse(false, 'Đăng ký thất bại do lỗi hệ thống. Vui lòng thử lại!', null, 500);
    }

    $activationLink = _HOST_URL."/activate?token=$emailVerificationToken";
    $subject = "Xác nhận email và kích hoạt tài khoản - Măm Măm Store";
    ob_start();
    require_once _PATH_URL_VIEWS.'/emails/activate-email-content.php';
    $content = ob_get_clean();

    Helpers::sendMail($_POST['email'], $subject, $content);

    // Mã 201 (Created) là mã chuẩn cho việc tạo thành công một tài nguyên mới
    Helpers::sendJsonResponse(true, 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.', null, 201);
  }

  public function handleLogin() {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    $validator = Validator::make($_POST);
    $validator->required('email', 'Email không được bỏ trống!')
      ->email('email')
      ->required('password', 'Mật khẩu không được để trống!');

    if ($validator->fails()) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', $validator->getErrors(), 422);
    }

    $userModel = new User();
    $user = $userModel->findUserByEmail($_POST['email']);

    if (!$user || !password_verify($_POST['password'], $user['password'])) {
      Helpers::sendJsonResponse(false, 'Email hoặc mật khẩu không chính xác.', null, 401);
    }

    if ($user['is_activated'] == 0) {
      Helpers::sendJsonResponse(
        false,
        'Tài khoản của bạn chưa được kích hoạt. Vui lòng kiểm tra email.',
        null,
        403
      );
    }

    $tokenModel = new RefreshToken();

    $deviceLimit = intval($_ENV['DEVICE_LOGIN_LIMIT']) ?? 5;
    $currentTokenCount = $tokenModel->getTokenCountForUser($user['id']);
    if ($currentTokenCount >= $deviceLimit) {
      $tokenModel->deleteOldestTokenForUser($user['id']);
    }

    $refreshToken = bin2hex(random_bytes(32));
    $refreshTokenHash = hash('sha256', $refreshToken);
    $refreshTokenExpiresAt = (new DateTime())->add(new DateInterval($_ENV['REFRESH_TOKEN_LIFETIME']));

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ipAddress = $userModel->getUserIpAddress();

    $isSaved = $tokenModel->saveToken($user['id'], $refreshTokenHash, $refreshTokenExpiresAt->format('Y-m-d H:i:s'), $userAgent, $ipAddress);

    if (!$isSaved) {
      Helpers::sendJsonResponse(false, 'Lỗi hệ thống, không thể tạo phiên đăng nhập.', null, 500);
    }

    setcookie(
      'refresh_token',
      $refreshToken,
      [
        'expires' => $refreshTokenExpiresAt->getTimestamp(),
        'path' => '/',
        'secure' => $_ENV['APP_ENV'] === 'production', // Tự động bật HTTPS khi production
        'httponly' => true,
        'samesite' => 'Strict'
      ]
    );

    $secretKey = $_ENV['ACCESS_TOKEN_SECRET'];
    $issuedAt = new DateTimeImmutable();
    $expire = $issuedAt->modify("+{$_ENV['ACCESS_TOKEN_LIFETIME']}")->getTimestamp();
    $role = array_column($userModel->findRoleUser($user['id']), 'name');

    // Lưu thông tin người dùng vào Session
    $_SESSION['user'] = [
      'id' => $user['id'],
      'full_name' => $user['full_name'],
      'email' => $user['email'],
      'role' => $role,
      'avatar_path' => $user['avatar_path']
    ];

    $payload = [
      'iat' => $issuedAt->getTimestamp(),     // Issued At
      'exp' => $expire,                       // Expiration Time
      'data' => [
        'userId' => $user['id'],
        'role' => $role,
      ]
    ];

    $accessToken = JWT::encode($payload, $secretKey, 'HS256');

    $responseData = [
      'access_token' => $accessToken,
      'expires_in' => $expire,
    ];

    Helpers::sendJsonResponse(true, 'Đăng nhập thành công!', $responseData);
  }

  public function handleLogout() {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    $refreshToken = $_COOKIE['refresh_token'] ?? null;

    if (!$refreshToken)
      Helpers::sendJsonResponse(false, 'Không tìm thấy phiên đăng nhập.', null, 400);


    $tokenModel = new RefreshToken();
    $refreshTokenHash = hash('sha256', $refreshToken);
    $isDeleted = $tokenModel->deleteTokenByHash($refreshTokenHash);

    if (!$isDeleted) {
      error_log("Không thể xóa refresh token có hash: $refreshTokenHash");
    }

    self::clearRefreshTokenCookie();

    // Xóa dữ liệu session
    session_unset();
    session_destroy();

    Helpers::sendJsonResponse(true, 'Đăng xuất thành công.');
  }

  public function activateAccount() {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    if (!$_POST['token'])
      Helpers::sendJsonResponse(false, 'Token xác thực không được cung cấp.', null, 400);// 400 bad request

    $userModel = new User();
    $user = $userModel->findUserIdByEmailVeriToken($_POST['token']);
    if (!$user)
      Helpers::sendJsonResponse(false, 'Token kích hoạt không hợp lệ hoặc đã hết hạn.', null, 400);

    $isActivated = $userModel->activateAccount($user['id']);
    if (!$isActivated)
      Helpers::sendJsonResponse(false, 'Có lỗi xảy ra trong quá trình kích hoạt.', null, 500); // 500 internal server error

    Helpers::sendJsonResponse(true, 'Tài khoản đã được kích hoạt thành công.');
  }

  public function handleForgotPassword() {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    $validator = Validator::make($_POST);
    $validator->required('email', 'Email không được bỏ trống!');

    if ($validator->fails()) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', $validator->getErrors(), 422);
    }

    $userModel = new User();
    $user = $userModel->findUserByEmail($_POST['email']);

    if (!$user)
      Helpers::sendJsonResponse(false, 'Email chưa được đăng ký hoặc không chính xác.', null, 401); // 401 Unauthorized => lỗi xác thực

    $forgotPasswordToken = bin2hex(random_bytes(32));
    $isTokenSet = $userModel->setForgotPasswordToken($user['id'], $forgotPasswordToken);
    if (!$isTokenSet)
      Helpers::sendJsonResponse(false, 'Có lỗi hệ thống xảy ra, xin vui lòng thử lại sau!', null, 500);

    $forgotPasswordLink = _HOST_URL."/reset-password?token=$forgotPasswordToken";
    $subject = "Yêu cầu đặt lại mật khẩu - Măm Măm Store";
    ob_start();
    require_once _PATH_URL_VIEWS.'/emails/forgot-pw-email-content.php';
    $content = ob_get_clean();

    Helpers::sendMail($user['email'], $subject, $content);

    Helpers::sendJsonResponse(true, 'Vui lòng kiểm tra email để đặt lại mật khẩu.', null, 200);
  }

  public function handleResetPassword() {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    if (!$_POST['token'])
      Helpers::sendJsonResponse(false, 'Token xác thực không được cung cấp.', null, 400);// 400 bad request

    $validator = Validator::make($_POST);
    $validator->required('new_password', 'Mật khẩu không được để trống!')
      ->minLength('new_password', 6, 'Mật khẩu phải lớn hơn 6 ký tự!')
      ->required('confirm_password', 'Hãy nhập lại mật khẩu!')
      ->matches('confirm_password', 'new_password', 'Mật khẩu không khớp!');

    if ($validator->fails()) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', $validator->getErrors(), 422);
    }

    $userModel = new User();
    $user = $userModel->findUserIdByForgotPasswordToken($_POST['token']);
    if (!$user) {
      Helpers::sendJsonResponse(false, 'Token đã hết hạn hoặc không chính xác.', null, 401); // 401 Unauthorized => lỗi xác thực
    }

    $isResetPassword = $userModel->resetPassword($user['id'], $_POST['new_password']);
    if (!$isResetPassword)
      Helpers::sendJsonResponse(false, 'Có lỗi xảy ra trong quá trình đặt lại mật khẩu.', null, 500); // 500 internal server error

    Helpers::sendJsonResponse(true, 'Tài khoản đã được đặt lại mật khẩu thành công.');
  }

  public function handleRefreshToken() {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    $refreshToken = $_COOKIE['refresh_token'] ?? null;

    if (!$refreshToken)
      Helpers::sendJsonResponse(false, 'Không tìm thấy phiên đăng nhập.', null, 400);

    $tokenModel = new RefreshToken();
    $refreshTokenHash = hash('sha256', $refreshToken);
    $tokenData = $tokenModel->findValidTokenByHash($refreshTokenHash);

    if (!$tokenData) {
      self::clearRefreshTokenCookie();
      Helpers::sendJsonResponse(false, 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', null, 401);
    }

    $userModel = new User();
    $user = $userModel->findUserById($tokenData['user_id']);

    if (!$user) {
      Helpers::sendJsonResponse(false, 'Không tìm thấy người dùng.', null, 404);
    }

    $newRefreshTokenExpiresAt = (new DateTime())->add(new DateInterval($_ENV['REFRESH_TOKEN_LIFETIME']))->format('Y-m-d H:i:s');
    $tokenModel->updateTokenExpiresAt($tokenData['id'], $newRefreshTokenExpiresAt);

    $secretKey = $_ENV['ACCESS_TOKEN_SECRET'];
    $issuedAt = new DateTimeImmutable();
    $expire = $issuedAt->modify("+{$_ENV['ACCESS_TOKEN_LIFETIME']}")->getTimestamp();

    $role = array_column($userModel->findRoleUser($user['id']), 'name');

    // Cập nhật lại session
    $_SESSION['user'] = [
      'id' => $user['id'],
      'full_name' => $user['full_name'],
      'email' => $user['email'],
      'role' => $role,
      'avatar_path' => $user['avatar_path']
    ];

    $payload = [
      'iat' => $issuedAt->getTimestamp(),
      'exp' => $expire,
      'data' => [
        'userId' => $user['id'],
        'role' => $role,
      ]
    ];
    $accessToken = JWT::encode($payload, $secretKey, 'HS256');

    $responseData = [
      'access_token' => $accessToken,
      'expires_in' => $expire,
    ];

    Helpers::sendJsonResponse(true, 'Token đã được làm mới thành công.', $responseData);
  }

  private static function clearRefreshTokenCookie() {
    setcookie(
      'refresh_token',
      '',
      [
        'expires' => time() - 3600, // Hết hạn 1 giờ trước
        'path' => '/',
        'secure' => $_ENV['APP_ENV'] === 'production', // Tự động bật HTTPS khi production
        'httponly' => true,
        'samesite' => 'Strict'
      ]
    );
  }
}
