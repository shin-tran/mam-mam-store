<?php
namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\SanitizeInputMiddleware;

class Router {
  protected $routes = [
    'GET' => [],
    'POST' => []
  ];

  /**
   * Chuyển đổi một URI có tham số thành một mẫu regex.
   * Ví dụ: /product/{id} -> #^/product/([a-zA-Z0-9_-]+)$#
   */
  private function convertToRegex($uri) {
    // Thay thế các placeholder {param} bằng một nhóm bắt giữ trong regex
    $pattern = preg_replace('/\{([a-zA-Z0-9_-]+)\}/', '([a-zA-Z0-9_-]+)', $uri);
    // Thêm các ký tự bắt đầu, kết thúc và delimiter cho regex
    return "#^$pattern\$#";
  }

  protected function add($method, $uri, $controller, $action, $middlewares = []) {
    /**
     * [
     *  '$method' => [
     *   '$uri' => [
     *      'controller' => $controller,
     *      'method' => $action,
     *      'middlewares' => [ '', '',... ]
     *     ]
     *   ]
     * ]
     */
    $pattern = $this->convertToRegex($uri);
    $this->routes[$method][$pattern] = [
      'controller' => $controller,
      'method' => $action,
      'middlewares' => $middlewares
    ];
  }

  public function get($uri, $controller, $action, $middlewares = []) {
    $this->add('GET', $uri, $controller, $action, $middlewares);
  }


  public function post($uri, $controller, $action, $middlewares = []) {
    $this->add('POST', $uri, $controller, $action, $middlewares);
  }

  public function dispatch($uri, $method) {
    // Lặp qua tất cả các route đã đăng ký cho phương thức hiện tại
    if (isset($this->routes[$method])) {
      foreach ($this->routes[$method] as $pattern => $route) {
        // Kiểm tra xem URI hiện tại có khớp với mẫu regex của route không
        if (preg_match($pattern, $uri, $matches)) {
          // Loại bỏ phần tử đầu tiên của mảng $matches (là toàn bộ chuỗi khớp)
          // để chỉ giữ lại các tham số đã bắt được.
          $params = array_slice($matches, 1);

          $middlewares = $route['middlewares'] ?? [];
          $userData = null;

          // Xử lý middleware
          foreach ($middlewares as $middleware) {
            $parts = explode(':', $middleware);
            $middlewareName = $parts[0];
            $param = $parts[1] ?? null;

            switch ($middlewareName) {
              case 'sanitize':
                SanitizeInputMiddleware::handle();
                break;
              case 'guest':
                GuestMiddleware::handle();
                break;
              case 'auth':
                $userData = AuthMiddleware::handle();
                break;
              case 'permission':
                if (!$userData) {
                  die('Lỗi cấu hình router: Middleware "auth" phải được gọi trước "permission".');
                }
                PermissionMiddleware::handle($userData, $param);
                break;
            }
          }

          $controllerName = $route['controller'];
          $methodName = $route['method'];
          $controller = new $controllerName();

          // Gọi phương thức của controller và truyền các tham số đã trích xuất từ URI
          // cùng với dữ liệu người dùng từ middleware (nếu có).
          $allParams = array_merge($params, [$userData]);
          call_user_func_array([$controller, $methodName], $allParams);
          return;
        }
      }
    }

    // Nếu không có route nào khớp
    http_response_code(404);
    echo "404 - Page Not Found";
  }
}
