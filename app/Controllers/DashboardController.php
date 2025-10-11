<?php
namespace App\Controllers;

use App\Core\View;

class DashboardController {
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
    View::render('pages/dashboard/products', [
      'title' => 'Sản phẩm'
    ], 'layouts/dashboard');
  }

  public function users() {
    View::render('pages/dashboard/users', [
      'title' => 'Khách hàng'
    ], 'layouts/dashboard');
  }
}