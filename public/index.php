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
use App\Controllers\AuthController;
use App\Controllers\PageController;
use App\Controllers\Api\AuthController as ApiAuthController;
use App\Controllers\Api\UserController as ApiUserController;
use App\Controllers\Api\ProductController as ApiProductController;
use App\Controllers\Api\CategoryController as ApiCategoryController;
use App\Controllers\Api\OrderController as ApiOrderController;
use App\Helpers\Helpers;

Helpers::initializeUserSession();

$router = new Router();

// --- WEB ROUTES ---
// Ai cũng vào được
$router->get('/', PageController::class, 'home');
$router->get('/product/{id}', PageController::class, 'productDetail');
$router->get('/profile', PageController::class, 'profile', ['auth']);


// Route chưa login (Guest)
$router->get('/activate', AuthController::class, 'showActivatePage', ['guest']);
$router->get('/login', AuthController::class, 'showLoginPage', ['guest']);
$router->get('/register', AuthController::class, 'showRegisterPage', ['guest']);
$router->get('/forgot-password', AuthController::class, 'showForgotPasswordPage', ['guest']);
$router->get('/reset-password', AuthController::class, 'showResetPasswordPage', ['guest']);


// Route đã login (Auth)
$router->get('/orders', PageController::class, 'orders', ['auth']);
$router->get('/dashboard', DashboardController::class, 'index', ['auth']);
$router->get('/dashboard/orders', DashboardController::class, 'orders', ['auth']);
$router->get('/dashboard/products', DashboardController::class, 'products', ['auth']);
$router->get('/dashboard/categories', DashboardController::class, 'categories', ['auth']);
$router->get('/dashboard/users', DashboardController::class, 'users', ['auth']);


// --- API ROUTES ---
// API không cần middleware
$router->post('/api/check-email', ApiAuthController::class, 'checkEmail');
$router->post('/api/check-phone-number', ApiAuthController::class, 'checkPhoneNumber');
$router->post('/api/refresh-token', ApiAuthController::class, 'handleRefreshToken');
$router->post('/api/logout', ApiAuthController::class, 'handleLogout');

// API cho Guest
$router->post('/api/activate', ApiAuthController::class, 'activateAccount', ['sanitize', 'guest']);
$router->post('/api/register', ApiAuthController::class, 'handleRegister', ['sanitize', 'guest']);
$router->post('/api/login', ApiAuthController::class, 'handleLogin', ['sanitize', 'guest']);
$router->post('/api/forgot-password', ApiAuthController::class, 'handleForgotPassword', ['sanitize', 'guest']);
$router->post('/api/reset-password', ApiAuthController::class, 'handleResetPassword', ['sanitize', 'guest']);

// API yêu cầu Auth và Permission
$router->post('/api/products/create', ApiProductController::class, 'create', [
  'sanitize', 'auth', 'admin'
]);
$router->post('/api/users/delete/{id}', ApiUserController::class, 'delete', [
  'sanitize', 'auth', 'admin'
]);
$router->post('/api/users/update/{id}', ApiUserController::class, 'update', [
  'sanitize', 'auth', 'admin'
]);

// Category API Routes
$router->post('/api/categories/create', ApiCategoryController::class, 'create', [
  'sanitize', 'auth', 'admin'
]);
$router->post('/api/categories/update/{id}', ApiCategoryController::class, 'update', [
  'sanitize', 'auth', 'admin'
]);
$router->post('/api/categories/delete/{id}', ApiCategoryController::class, 'delete', [
  'sanitize', 'auth', 'admin'
]);

// Order API Routes
$router->get('/api/orders/{id}', ApiOrderController::class, 'getDetails', [
  'sanitize', 'auth', 'admin'
]);
$router->post('/api/orders/update-status/{id}', ApiOrderController::class, 'updateStatus', [
  'sanitize', 'auth', 'admin'
]);

$router->post('/api/profile/update', ApiUserController::class, 'updateProfile', ['auth', 'sanitize']);
$router->post('/api/profile/avatar', ApiUserController::class, 'updateAvatar', ['auth']);
$router->post('/api/profile/change-password', ApiUserController::class, 'changePassword', ['auth', 'sanitize']);

// --- DISPATCH ROUTER ---
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$finalPath = Helpers::removePathFolder($requestPath);

$router->dispatch($finalPath, $requestMethod);

ob_end_flush(); // gửi (flush) toàn bộ ra trình duyệt và dọn dẹp bộ nhớ đệm

