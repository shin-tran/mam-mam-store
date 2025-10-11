<?php
namespace App\Core;

class View {
  public static function render(string $view, array $data = [], ?string $layout = null) {
    extract($data);

    $viewFile = _PATH_URL_VIEWS."/{$view}.php";

    if (!file_exists($viewFile)) {
      echo "Lỗi: File view không tồn tại tại đường dẫn: {$viewFile}";
      return;
    }

    ob_start();
    require_once $viewFile;
    $content = ob_get_clean();

    if ($layout) {
      $layoutFile = _PATH_URL_VIEWS."/{$layout}.php";
      if (file_exists($layoutFile)) {
        require_once $layoutFile;
      } else {
        echo "Lỗi: File layout không tồn tại tại đường dẫn: {$layoutFile}";
      }
    } else {
      echo $content;
    }
  }

  public static function layout($layoutName, $data = []) {
    $name = _PATH_URL_VIEWS."/layouts/{$layoutName}.php";
    if (file_exists($name)) {
      ob_start();
      require_once $name;
      echo ob_get_clean();
    }
  }
}