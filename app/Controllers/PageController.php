<?php
namespace App\Controllers;

use App\Core\View;
use App\Helpers\Helpers;

class PageController {
  public function home() {
    View::render('pages/home', [
      'title' => 'Trang chủ'
    ]);
  }

  public function orders() {
    View::render('pages/orders', [
      'title' => 'Giỏ hàng'
    ]);
  }
}