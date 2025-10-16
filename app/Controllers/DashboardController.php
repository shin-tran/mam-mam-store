<?php
namespace App\Controllers;

use App\Core\View;
use App\Helpers\Helpers;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class DashboardController {
  public function __construct() {
    // Kiểm tra vai trò của người dùng ngay khi controller được khởi tạo.
    // Nếu là 'customer', họ sẽ không có quyền truy cập và bị chuyển hướng.
    if (Helpers::userHasRole('customer')) {
      Helpers::redirect('/');
    }
  }
  public function index() {
    View::render('pages/dashboard/index', [
      'title' => 'Dashboard'
    ], 'layouts/dashboard');
  }

  public function orders() {
    View::render('pages/dashboard/orders', [
      'title' => 'Đơn hàng'
    ], 'layouts/dashboard');
  }

  public function products() {
    $productModel = new Product();
    $categoryModel = new Category();
    // lấy các thông tin và sẽ được sử dụng trong pages/dashboard/products
    $products = $productModel->getAllProducts();
    $categories = $categoryModel->getAll();

    View::render('pages/dashboard/products', [
      'title' => 'Sản phẩm',
      'products' => $products,
      'categories' => $categories,
    ], 'layouts/dashboard');
  }

  public function users() {
    $userModel = new User();

    $users = $userModel->getAllUsersWithRole();
    $allRoles = $userModel->getRoles();

    View::render('pages/dashboard/users', [
      'title' => 'Khách hàng',
      'users' => $users,
      'allRoles' => $allRoles
    ], 'layouts/dashboard');
  }
}
