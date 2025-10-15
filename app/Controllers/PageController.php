<?php
namespace App\Controllers;

use App\Core\View;
use App\Models\Category;
use App\Models\Product;

class PageController {
  public function home() {
    $productModel = new Product();
    $categoryModel = new Category();

    $products = $productModel->getAllProducts();
    $categories = $categoryModel->getAll();

    View::render('pages/home', [
      'title' => 'Trang chủ',
      'products' => $products,
      'categories' => $categories
    ], 'layouts/main');
  }

  public function orders() {
    View::render('pages/orders', [
      'title' => 'Giỏ hàng'
    ], 'layouts/main');
  }

  public function productDetail($productId) {
    $productModel = new Product();

    $product = $productModel->getProduct($productId);

    if (!$product) {
      http_response_code(404);
      echo "404 - Sản phẩm không tồn tại";
      return;
    }

    View::render('pages/product-details', [
      'title' => $product['product_name'] ?? 'Sản phẩm',
      'product' => $product
    ], 'layouts/main');
  }
}