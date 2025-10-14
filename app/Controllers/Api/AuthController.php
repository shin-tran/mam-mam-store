<?php
namespace App\Controllers\Api;

use Exception, App\Models\User;
use App\Helpers\Helpers;
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

  public function checkPhoneNumber() {
    $phoneNumber = $_POST['phone_number'];

    if (empty($phoneNumber)) {
      Helpers::sendJsonResponse(false, 'Số điện thoại không được để trống', null, 400);
    }

    try {
      $userModel = new User();
      $exists = $userModel->phoneNumberExists($phoneNumber);
      Helpers::sendJsonResponse(true, 'Kiểm tra số điện thoại thành công', ['exists' => $exists]);
    } catch (Exception $e) {
      error_log("Check phone number failed: ".$e->getMessage());
      Helpers::sendJsonResponse(false, 'Lỗi hệ thống', null, 500);
    }

    exit();
  }

  public function handleRegister() {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    $errors = [];
    $userModel = new User();

    // Validate full_name
    if (empty($_POST['full_name']))
      $errors['full_name'][] = "Họ tên không được bỏ trống!";
    else if (strlen($_POST['full_name']) < 5)
      $errors['full_name'][] = "Họ tên phải có ít nhất 5 ký tự!";

    // Validate email
    if (empty($_POST['email']))
      $errors['email'][] = "Email không được bỏ trống!";
    else if (!Helpers::validateEmail($_POST['email']))
      $errors['email'][] = "Email không hợp lệ!";
    else if ($userModel->emailExists($_POST['email'])) {
      $errors['email'][] = "Email đã tồn tại!";
    }

    // Validate phone_number
    if (!empty($_POST['phone_number'])) {
      if (!Helpers::isPhone($_POST['phone_number']))
        $errors['phone_number'][] = "Số điện thoại không hợp lệ!";
      else if ($userModel->phoneNumberExists($_POST['phone_number']))
        $errors['phone_number'][] = "Số điện thoại đã tồn tại!";
    }

    // Validate password
    if (empty($_POST['password']))
      $errors['password'][] = "Mật khẩu không được để trống!";
    else if (strlen($_POST['password']) < 6)
      $errors['password'][] = "Mật khẩu phải lớn hơn 6 ký tự!";

    // Validate confirm_password
    if (empty($_POST['confirm_password']))
      $errors['confirm_password'][] = "Hãy nhập lại mật khẩu!";
    else if ($_POST['confirm_password'] !== $_POST['password'])
      $errors['confirm_password'][] = "Mật khẩu không khớp!";

    if (!empty($errors)) {
      // Mã 422 (Unprocessable Entity) là mã chuẩn cho lỗi validation
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', $errors, 422);
    }

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

    $errors = [];

    if (!empty($_POST['email_phone_number'])) {
      if (is_numeric($_POST['email_phone_number'])) {
        if (!Helpers::isPhone($_POST['email_phone_number']))
          $errors['email_phone_number'][] = "Số điện thoại không hợp lệ!";
      } else if (!Helpers::validateEmail($_POST['email_phone_number'])) {
        $errors['email_phone_number'][] = "Email không hợp lệ!";
      }
    } else
      $errors['email_phone_number'][] = "Email / Số điện thoại không được bỏ trống!";

    // Validate password
    if (empty($_POST['password']))
      $errors['password'][] = "Mật khẩu không được để trống!";

    if (!empty($errors)) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', $errors, 422);
    }

    $userModel = new User();
    $user = (is_numeric($_POST['email_phone_number']))
      ? $userModel->findUserByPhoneNumber($_POST['email_phone_number'])
      : $userModel->findUserByEmail($_POST['email_phone_number']);

    if (!$user || !password_verify($_POST['password'], $user['password'])) {
      Helpers::sendJsonResponse(false, 'Email hoặc mật khẩu không chính xác.', null, 401);
    }

    if ($user['is_activated'] == 0) {
      Helpers::sendJsonResponse(false, 'Tài khoản của bạn chưa được kích hoạt. Vui lòng kiểm tra email.', null, 403);
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
        // 'secure' => true, //NOTE: Uncomment if the website have https
        'httponly' => true,
        'samesite' => 'Strict'
      ]
    );

    $secretKey = $_ENV['ACCESS_TOKEN_SECRET'];
    $issuedAt = new DateTimeImmutable();
    $expire = $issuedAt->modify("+{$_ENV['ACCESS_TOKEN_LIFETIME']}")->getTimestamp();
    $roles = array_column($userModel->getRolesUser($user['id']), 'name');
    $permissions = array_column($userModel->getPermissionsUser($user['id']), 'name');

    $payload = [
      'iat' => $issuedAt->getTimestamp(),     // Issued At
      'exp' => $expire,                       // Expiration Time
      'data' => [
        'userId' => $user['id'],
        'roles' => $roles,
        'permissions' => $permissions
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

    Helpers::sendJsonResponse(true, 'Đăng xuất thành công.');
  }

  public function activateAccount() {
    if (!Helpers::isPost()) {
      Helpers::sendJsonResponse(false, 'Phương thức không hợp lệ.', null, 405);
    }

    if (!$_POST['token'])
      Helpers::sendJsonResponse(false, 'Token xác thực không được cung cấp.', null, 400);// 400 bad request

    $userModel = new User();
    $user = $userModel->findUserByEmailVeriToken($_POST['token']);
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

    if (empty($_POST['email_phone_number']))
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', ['email_phone_number' => 'Email / Số điện thoại không được bỏ trống!'], 422);

    $errors = [];

    if (is_numeric($_POST['email_phone_number'])) {
      if (!Helpers::isPhone($_POST['email_phone_number']))
        $errors['email_phone_number'][] = "Số điện thoại không hợp lệ!";
    } else if (!Helpers::validateEmail($_POST['email_phone_number'])) {
      $errors['email_phone_number'][] = "Email không hợp lệ!";
    }

    if (!empty($errors)) {
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', $errors, 422);
    }

    $userModel = new User();
    $user = (is_numeric($_POST['email_phone_number']))
      ? $userModel->findUserByPhoneNumber($_POST['email_phone_number'])
      : $userModel->findUserByEmail($_POST['email_phone_number']);

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

    $errors = [];

    if (!$_POST['token'])
      Helpers::sendJsonResponse(false, 'Token xác thực không được cung cấp.', null, 400);// 400 bad request

    // Validate password
    if (empty($_POST['new_password']))
      $errors['new_password'][] = "Mật khẩu không được để trống!";
    else if (strlen($_POST['new_password']) < 6)
      $errors['new_password'][] = "Mật khẩu phải lớn hơn 6 ký tự!";

    // Validate confirm_password
    if (empty($_POST['confirm_password']))
      $errors['confirm_password'][] = "Hãy nhập lại mật khẩu!";
    else if ($_POST['confirm_password'] !== $_POST['new_password'])
      $errors['confirm_password'][] = "Mật khẩu không khớp!";

    if (!empty($errors)) {
      // Mã 422 (Unprocessable Entity) là mã chuẩn cho lỗi validation
      Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', $errors, 422);
    }

    $userModel = new User();
    $user = $userModel->findUserByForgotPasswordToken($_POST['token']);
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

    $roles = array_column($userModel->getRolesUser($user['id']), 'name');
    $permissions = array_column($userModel->getPermissionsUser($user['id']), 'name');

    $payload = [
      'iat' => $issuedAt->getTimestamp(),
      'exp' => $expire,
      'data' => [
        'userId' => $user['id'],
        'roles' => $roles,
        'permissions' => $permissions
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
        // 'secure' => true, //NOTE: Uncomment if the website have https
        'httponly' => true,
        'samesite' => 'Strict'
      ]
    );
  }
}