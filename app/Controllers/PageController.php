<?php
namespace App\Controllers;

use App\Core\View;
use App\Models\Category;
use App\Models\Product;

class PageController {
  public function home() {
    $productModel = new Product();
    $categoryModel = new Category();

    $products = $productModel->getAllProductsWithImages();
    $categories = $categoryModel->getAll();

    View::render('pages/home', [
      'title' => 'Trang chủ',
      'products' => $products,
      'categories' => $categories
    ]);
  }

  public function orders() {
    View::render('pages/orders', [
      'title' => 'Giỏ hàng'
    ]);
  }
}