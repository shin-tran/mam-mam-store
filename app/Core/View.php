<?php
namespace App\Core;

class View {
  // require các file trong view và thêm layout nếu có
  public static function render(string $view, array $data = [], ?string $layout = null) {
    // chuyển đổi các key-value pairs của một mảng
    // thành các biến độc lập.
    extract($data);

    $viewFile = _PATH_URL_VIEWS."/{$view}.php";

    if (!file_exists($viewFile)) {
      echo "Lỗi: File view không tồn tại tại đường dẫn: {$viewFile}";
      return;
    }

    // ghi vào bộ nhớ đệm và gán vào $content
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

  // helper require các file trong layouts
  public static function layout($layoutName, $data = []) {
    $name = _PATH_URL_VIEWS."/layouts/{$layoutName}.php";
    if (file_exists($name)) {
      ob_start();
      require_once $name;
      echo ob_get_clean();
    }
  }
}
