<?php
namespace App\Middleware;

use function is_string;

class SanitizeInputMiddleware {
  public static function handle() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      self::sanitizeArrayRecursive($_POST);
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      self::sanitizeArrayRecursive($_GET);
    }
  }

  private static function sanitizeArrayRecursive(array &$array) {
    // duyệt các mảng lồng nhau (nested arrays)
    array_walk_recursive($array, function (&$value) {
      if (is_string($value)) {
        $value = trim($value);
      }
    });

    // xử lý key vì array_walk_recursive không giúp xử lý key
    $sanitizedArray = [];
    foreach ($array as $key => $value) {
      // Loại bỏ hoàn toàn các thẻ HTML khỏi key
      $cleanKey = strip_tags($key);
      $sanitizedArray[$cleanKey] = $value;
    }

    $array = $sanitizedArray;
  }
}
