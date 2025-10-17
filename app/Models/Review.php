<?php
namespace App\Models;

use App\Core\Database;

class Review {
  private $db;

  public function __construct() {
    $this->db = new Database();
  }

  public function getReviewsByProductId(int $productId) {
    $sql = "SELECT r.*, u.full_name, u.avatar_path
            FROM `reviews` r
            JOIN `users` u ON r.user_id = u.id
            WHERE r.product_id = :product_id
            ORDER BY r.review_date DESC";
    return $this->db->getAll($sql, ['product_id' => $productId]);
  }

  public function createReview(int $productId, int $userId, int $rating, string $comment) {
    $data = [
      'product_id' => $productId,
      'user_id' => $userId,
      'rating' => $rating,
      'comment' => $comment
    ];
    return $this->db->insert('reviews', $data);
  }
}
