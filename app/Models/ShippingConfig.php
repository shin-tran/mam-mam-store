<?php
namespace App\Models;

use App\Core\Database;
use function floatval;

class ShippingConfig {
  private $db;

  public function __construct() {
    $this->db = new Database();
  }

  /**
   * Lấy giá trị cấu hình theo key
   */
  public function getConfigValue($key) {
    $sql = "SELECT `config_value`
            FROM `shipping_config`
            WHERE `config_key` = :key";
    $result = $this->db->getOne($sql, ['key' => $key]);
    return $result ? $result['config_value'] : null;
  }

  /**
   * Lấy tất cả cấu hình vận chuyển
   */
  public function getAllConfigs() {
    $sql = "SELECT `config_key`, `config_value`
            FROM `shipping_config`";
    $results = $this->db->getAll($sql);

    // Convert to associative array
    $configs = [];
    foreach ($results as $row) {
      $configs[$row['config_key']] = $row['config_value'];
    }
    return $configs;
  }

  /**
   * Tính phí vận chuyển dựa trên tổng tiền đơn hàng
   */
  public function calculateShippingFee($subtotal) {
    $freeThreshold = floatval($this->getConfigValue('FREE_SHIPPING_THRESHOLD') ?? 0);
    $standardFee = floatval($this->getConfigValue('STANDARD_SHIPPING_FEE') ?? 0);

    // Nếu subtotal >= ngưỡng miễn phí, return 0
    if ($subtotal >= $freeThreshold && $freeThreshold > 0) {
      return 0;
    }

    // Ngược lại return phí cố định
    return $standardFee;
  }
}
