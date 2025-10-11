<?php
namespace App\Middleware;

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
        $value = trim(filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS));
      }
    });

    // xử lý key vì array_walk_recursive không giúp xử lý key
    $sanitizedArray = [];
    foreach ($array as $key => $value) {
      $cleanKey = strip_tags($key);
      $sanitizedArray[$cleanKey] = $value;
    }

    $array = $sanitizedArray;
  }
}
