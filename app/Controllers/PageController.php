<?php
namespace App\Controllers;

use App\Core\View;
use App\Helpers\Helpers;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

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
    $reviewModel = new Review();

    $product = $productModel->getProduct($productId);
    $reviews = $reviewModel->getReviewsByProductId($productId);

    if (!$product) {
      http_response_code(404);
      echo "404 - Sản phẩm không tồn tại";
      return;
    }

    View::render('pages/product-details', [
      'title' => $product['product_name'] ?? 'Sản phẩm',
      'product' => $product,
      'reviews' => $reviews
    ], 'layouts/main');
  }

  public function profile() {
    $userSession = Helpers::getCurrentUser();
    if (!$userSession) {
      Helpers::redirect('/login');
    }

    $userModel = new User();
    $orderModel = new Order();

    $user = $userModel->findUserById($userSession['id']);
    $orders = $orderModel->getOrdersByUserId($userSession['id']);

    View::render('pages/profile', [
      'title' => 'Hồ sơ của bạn',
      'user' => $user,
      'orders' => $orders
    ], 'layouts/main');
  }
}
