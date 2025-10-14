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
    $this->routes[$method][$uri] = [
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
    // kiểm tra có method và uri hay không
    // nếu không thì hiển thị 404 (không tồn tại)
    if (isset($this->routes[$method]) && array_key_exists($uri, $this->routes[$method])) {
      $route = $this->routes[$method][$uri];

      $middlewares = $route['middlewares'] ?? [];
      $userData = null;

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
      $controller->$methodName($userData);
    } else {
      http_response_code(404);
      echo "404 - Page Not Found";
    }
  }
}
