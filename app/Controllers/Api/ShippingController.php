<?php
namespace App\Controllers\Api;

use App\Helpers\Helpers;
use App\Models\ShippingConfig;

class ShippingController {
  /**
   * Lấy cấu hình phí vận chuyển
   */
  public function getConfig() {
    $shippingConfig = new ShippingConfig();
    $configs = $shippingConfig->getAllConfigs();

    if ($configs) {
      Helpers::sendJsonResponse(true, 'Lấy cấu hình thành công.', [
        'freeShippingThreshold' => floatval($configs['FREE_SHIPPING_THRESHOLD'] ?? 0),
        'standardShippingFee' => floatval($configs['STANDARD_SHIPPING_FEE'] ?? 0)
      ]);
    } else {
      Helpers::sendJsonResponse(false, 'Không thể lấy cấu hình vận chuyển.', null, 500);
    }
  }
}
