<?php

namespace App\Models;

use App\Core\Database;
use Exception;

class UserAddress {
  private $db;

  public function __construct() {
    $this->db = new Database();
  }

  /**
   * Lấy tất cả địa chỉ của user
   */
  public function getAddressesByUserId($userId) {
    $sql = "SELECT *
            FROM `user_addresses`
            WHERE `user_id` = :user_id
            ORDER BY `is_default` DESC,
                     `created_at` DESC";
    return $this->db->getAll($sql, ['user_id' => $userId]);
  }

  /**
   * Lấy địa chỉ mặc định của user
   */
  public function getDefaultAddress($userId) {
    $sql = "SELECT *
            FROM `user_addresses`
            WHERE `user_id` = :user_id
            AND `is_default` = 1
            LIMIT 1";
    return $this->db->getOne($sql, ['user_id' => $userId]);
  }

  /**
   * Lấy thông tin địa chỉ theo ID
   */
  public function getAddressById($addressId, $userId = null) {
    $sql = "SELECT *
            FROM `user_addresses`
            WHERE `id` = :id";
    $params = ['id' => $addressId];

    if ($userId) {
      $sql .= " AND `user_id` = :user_id";
      $params['user_id'] = $userId;
    }

    return $this->db->getOne($sql, $params);
  }

  /**
   * Tạo địa chỉ mới
   */
  public function createAddress($data) {
    try {
      $this->db->beginTransaction();

      // Nếu đây là địa chỉ mặc định, bỏ mặc định của các địa chỉ khác
      if (!empty($data['is_default'])) {
        $this->removeDefaultAddress($data['user_id']);
      }

      $addressData = [
        'user_id' => $data['user_id'],
        'recipient_name' => $data['recipient_name'],
        'phone_number' => $data['phone_number'],
        'street_address' => $data['street_address'],
        'ward' => $data['ward'],
        'city' => $data['city'],
        'is_default' => !empty($data['is_default']) ? 1 : 0
      ];

      $result = $this->db->insert('user_addresses', $addressData);
      $this->db->commit();

      return $result;
    } catch (Exception $ex) {
      $this->db->rollBack();
      $this->db->writeErrorLog($ex);
    }
  }

  /**
   * Cập nhật địa chỉ
   */
  public function updateAddress($addressId, $userId, $data) {
    try {
      $this->db->beginTransaction();

      // Nếu đây là địa chỉ mặc định, bỏ mặc định của các địa chỉ khác
      if (!empty($data['is_default'])) {
        $this->removeDefaultAddress($userId);
      }

      $addressData = [
        'recipient_name' => $data['recipient_name'],
        'phone_number' => $data['phone_number'],
        'street_address' => $data['street_address'],
        'ward' => $data['ward'],
        'city' => $data['city'],
        'is_default' => !empty($data['is_default']) ? 1 : 0
      ];

      $condition = "`id` = :id AND `user_id` = :user_id";
      $params = ['id' => $addressId, 'user_id' => $userId];

      $result = $this->db->update('user_addresses', $addressData, $condition, $params);
      $this->db->commit();

      return $result;
    } catch (Exception $ex) {
      $this->db->rollBack();
      $this->db->writeErrorLog($ex);
    }
  }

  /**
   * Xóa địa chỉ
   */
  public function deleteAddress($addressId, $userId) {
    $condition = "`id` = :id AND `user_id` = :user_id";
    $params = ['id' => $addressId, 'user_id' => $userId];
    return $this->db->delete('user_addresses', $condition, $params);
  }

  /**
   * Set địa chỉ mặc định
   */
  public function setDefaultAddress($addressId, $userId) {
    try {
      $this->db->beginTransaction();

      // Bỏ mặc định của các địa chỉ khác
      $this->removeDefaultAddress($userId);

      // Set địa chỉ mới làm mặc định
      $condition = "`id` = :id AND `user_id` = :user_id";
      $params = ['id' => $addressId, 'user_id' => $userId];
      $result = $this->db->update('user_addresses', ['is_default' => 1], $condition, $params);

      $this->db->commit();
      return $result;
    } catch (Exception $ex) {
      $this->db->rollBack();
      $this->db->writeErrorLog($ex);
    }
  }

  /**
   * Bỏ mặc định của tất cả địa chỉ của user
   */
  private function removeDefaultAddress($userId) {
    $condition = "`user_id` = :user_id";
    $params = ['user_id' => $userId];
    return $this->db->update(
      'user_addresses',
      ['is_default' => 0],
      $condition,
      $params
    );
  }

  /**
   * Đếm số lượng địa chỉ của user
   */
  public function countUserAddresses($userId) {
    $sql = "SELECT COUNT(*) as count
            FROM `user_addresses`
            WHERE `user_id` = :user_id";
    $result = $this->db->getOne($sql, ['user_id' => $userId]);
    return $result['count'] ?? 0;
  }
}
