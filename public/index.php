<?php

use App\Controllers\DashboardController;
date_default_timezone_set("Asia/Ho_Chi_Minh");
session_start();
ob_start(); // lưu toàn bộ vào bộ nhớ đệm

require_once __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

require_once __DIR__.'/../app/Configs/configs.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\PageController;
use App\Controllers\Api\AuthController as ApiAuthController;
use App\Helpers\Helpers;

$router = new Router();

// Ai cũng vào được
$router->get('/', PageController::class, 'home');

// Route chưa login
$router->get('/activate', AuthController::class, 'showActivatePage', ['guest']);
$router->get('/login', AuthController::class, 'showLoginPage', ['guest']);
$router->get('/register', AuthController::class, 'showRegisterPage', ['guest']);
$router->get('/forgot-password', AuthController::class, 'showForgotPasswordPage', ['guest']);
$router->get('/reset-password', AuthController::class, 'showResetPasswordPage', ['guest']);

$router->get('/orders', PageController::class, 'orders', ['auth']);

// Route đã login
$router->get('/dashboard', DashboardController::class, 'index', ['auth']);
$router->get('/dashboard/orders', DashboardController::class, 'orders', ['auth']);
$router->get('/dashboard/products', DashboardController::class, 'products', ['auth']);
$router->get('/dashboard/users', DashboardController::class, 'users', ['auth']);

// API không cần middleware
$router->post('/api/check-email', ApiAuthController::class, 'checkEmail');
$router->post('/api/check-phone-number', ApiAuthController::class, 'checkPhoneNumber');
$router->post('/api/refresh-token', ApiAuthController::class, 'handleRefreshToken');
$router->post('/api/logout', ApiAuthController::class, 'handleLogout');

// API chưa login
$router->post('/api/activate', ApiAuthController::class, 'activateAccount', ['sanitize', 'guest']);
$router->post('/api/register', ApiAuthController::class, 'handleRegister', ['sanitize', 'guest']);
$router->post('/api/login', ApiAuthController::class, 'handleLogin', ['sanitize', 'guest']);
$router->post('/api/forgot-password', ApiAuthController::class, 'handleForgotPassword', ['sanitize', 'guest']);
$router->post('/api/reset-password', ApiAuthController::class, 'handleResetPassword', ['sanitize', 'guest']);

// API yêu cầu phải đăng nhập
// $router->get('/api/user/profile', [Api\UserController::class, 'getProfile'], ['auth']);

// API yêu cầu phải đăng nhập VÀ CÓ QUYỀN HẠN
// $router->post('/api/products/create', [Api\ProductController::class, 'create'], ['auth', 'permission:products.create']);

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$finalPath = Helpers::removePathFolder($requestPath);

$router->dispatch($finalPath, $requestMethod);

ob_end_flush(); // gửi (flush) toàn bộ ra trình duyệt và dọn dẹp bộ nhớ đệm