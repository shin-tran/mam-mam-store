<?php

use App\Controllers\Api\UserController;
use App\Controllers\DashboardController;
use App\Controllers\Api\CategoryController;
use App\Controllers\Api\OrderController as ApiOrderController;

date_default_timezone_set("Asia/Ho_Chi_Minh");
session_start();
ob_start(); // lưu toàn bộ vào bộ nhớ đệm

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require_once __DIR__ . '/../app/Configs/configs.php';

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\PageController;
use App\Controllers\Api\AuthController as ApiAuthController;
use App\Controllers\Api\ProductController as ApiProductController;
use App\Helpers\Helpers;

Helpers::initializeUserSession();

$router = new Router();

// --- WEB ROUTES ---
$router->get('/', PageController::class, 'home');
$router->get('/product/{id}', PageController::class, 'productDetail');

// Guest
$router->get('/activate', AuthController::class, 'showActivatePage', ['guest']);
$router->get('/login', AuthController::class, 'showLoginPage', ['guest']);
$router->get('/register', AuthController::class, 'showRegisterPage', ['guest']);
$router->get('/forgot-password', AuthController::class, 'showForgotPasswordPage', ['guest']);
$router->get('/reset-password', AuthController::class, 'showResetPasswordPage', ['guest']);

// Auth
$router->get('/orders', PageController::class, 'orders', ['auth']);
$router->get('/profile', PageController::class, 'profile', ['auth']);
$router->get('/dashboard', DashboardController::class, 'index', ['auth']);
$router->get('/dashboard/orders', DashboardController::class, 'orders', ['auth']);
$router->get('/dashboard/products', DashboardController::class, 'products', ['auth']);
$router->get('/dashboard/users', DashboardController::class, 'users', ['auth']);
$router->get('/dashboard/categories', DashboardController::class, 'categories', ['auth']);


// --- API ROUTES ---
// Public
$router->post('/api/check-email', ApiAuthController::class, 'checkEmail');
$router->post('/api/check-phone-number', ApiAuthController::class, 'checkPhoneNumber');
$router->post('/api/refresh-token', ApiAuthController::class, 'handleRefreshToken');
$router->post('/api/logout', ApiAuthController::class, 'handleLogout');

// Guest
$router->post('/api/activate', ApiAuthController::class, 'activateAccount', ['sanitize', 'guest']);
$router->post('/api/register', ApiAuthController::class, 'handleRegister', ['sanitize', 'guest']);
$router->post('/api/login', ApiAuthController::class, 'handleLogin', ['sanitize', 'guest']);
$router->post('/api/forgot-password', ApiAuthController::class, 'handleForgotPassword', ['sanitize', 'guest']);
$router->post('/api/reset-password', ApiAuthController::class, 'handleResetPassword', ['sanitize', 'guest']);

// Auth
$router->post('/api/users/update-details', UserController::class, 'updateDetails', ['auth', 'sanitize']);
$router->post('/api/users/update-password', UserController::class, 'updatePassword', ['auth', 'sanitize']);
$router->post('/api/users/update-avatar', UserController::class, 'updateAvatar', ['auth']);
$router->post('/api/products/cart', ApiProductController::class, 'getCartProducts', ['auth']);
$router->post('/api/orders/create', ApiOrderController::class, 'create', ['auth', 'sanitize']);

// Auth + Admin
$router->post('/api/products/create', ApiProductController::class, 'create', ['auth', 'admin', 'sanitize']);
$router->post('/api/users/delete/{id}', UserController::class, 'delete', ['auth', 'admin']);
$router->post('/api/users/update/{id}', UserController::class, 'update', ['auth', 'admin', 'sanitize']);
$router->post('/api/categories/create', CategoryController::class, 'create', ['auth', 'admin', 'sanitize']);
$router->post('/api/categories/update/{id}', CategoryController::class, 'update', ['auth', 'admin', 'sanitize']);
$router->post('/api/categories/delete/{id}', CategoryController::class, 'delete', ['auth', 'admin']);
$router->get('/api/orders/{id}', ApiOrderController::class, 'getDetails', ['auth', 'admin']);
$router->post('/api/orders/update-status/{id}', ApiOrderController::class, 'updateStatus', ['auth', 'admin', 'sanitize']);


// --- DISPATCH ROUTER ---
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$finalPath = Helpers::removePathFolder($requestPath);

$router->dispatch($finalPath, $requestMethod);

ob_end_flush();

