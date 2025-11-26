<?php
namespace App\Controllers;

use App\Core\View;
use App\Helpers\Helpers;
use App\Models\User;

class AuthController {
  public function showLoginPage() {
    View::render('pages/auth/login', [
      'title' => 'Đăng nhập'
    ], 'layouts/auth');
  }

  public function showRegisterPage() {
    View::render('pages/auth/register', [
      'title' => 'Đăng ký'
    ], 'layouts/auth');
  }

  public function showActivatePage() {
    if (empty($_GET['token']))
      Helpers::redirect();

    View::render('pages/auth/activate', [
      'title' => 'Kích hoạt tài khoản'
    ], 'layouts/auth');
  }

  public function showForgotPasswordPage() {
    View::render('pages/auth/forgot-password', [
      'title' => 'Quên mật khẩu'
    ], 'layouts/auth');
  }

  public function showResetPasswordPage() {
    if (empty($_GET['token']))
      Helpers::redirect();

    $userModel = new User();
    $user = $userModel->findUserIdByForgotPasswordToken($_GET['token']);

    View::render('pages/auth/reset-password', [
      'title' => 'Đặt lại mật khẩu',
      'user' => $user
    ], 'layouts/auth');
  }
}
