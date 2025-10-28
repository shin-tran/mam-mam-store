CREATE DATABASE IF NOT EXISTS `mam_mam_store_dev` CHARACTER
SET
  utf8mb4 COLLATE utf8mb4_unicode_ci;

-- edit my.cnf: default_time_zone = +07:00 after [mysqld]
USE mam_mam_store_dev;

CREATE TABLE
  `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `full_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone_number` VARCHAR(15),
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT,
    `avatar_path` VARCHAR(255),
    `email_verification_token` VARCHAR(255),
    `verification_expires_at` DATETIME NULL,
    `forgot_password_token` VARCHAR(255),
    `forgot_password_expires_at` DATETIME NULL,
    `is_activated` BOOLEAN NOT NULL DEFAULT 0,
    `email_verified_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_email` (`email`),
    UNIQUE KEY `uq_phone_number` (`phone_number`),
    UNIQUE KEY `uq_email_verification_token` (`email_verification_token`),
    UNIQUE KEY `uq_forgot_password_token` (`forgot_password_token`)
  );

CREATE TABLE
  `refresh_tokens` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token_hash` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `user_agent` VARCHAR(255),
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_token_hash` (`token_hash`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
  );

CREATE TABLE
  `roles` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `description` TEXT,
    UNIQUE KEY `uq_name` (`name`)
  );

CREATE TABLE
  `role_user` (
    `user_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
  );

CREATE TABLE
  `categories` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `category_name` VARCHAR(255) NOT NULL,
    `image_path` VARCHAR(255),
    UNIQUE KEY `uq_category_name` (`category_name`)
  );

CREATE TABLE
  `products` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `product_name` VARCHAR(255) NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `category_id` INT,
    `description` TEXT,
    `image_path` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
    INDEX `idx_product_name` (`product_name`)
  );

CREATE TABLE
  `orders` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT,
    `total_amount` DECIMAL(12, 2) NOT NULL,
    `status` ENUM (
      'pending',
      'packing',
      'shipping',
      'completed',
      'cancelled'
    ) DEFAULT 'pending',
    `shipping_address` TEXT NOT NULL,
    `shipping_phone` VARCHAR(15) NOT NULL,
    `note` TEXT,
    `cancellation_reason` TEXT NULL,
    `cancelled_by` ENUM ('customer', 'admin', 'system') NULL,
    `cancelled_at` TIMESTAMP NULL,
    `order_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`)
  );

CREATE TABLE
  `order_details` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `order_id` INT,
    `product_id` INT,
    `product_name` VARCHAR(255) NOT NULL DEFAULT 'Unknown Product',
    `product_image_path` VARCHAR(255),
    `quantity` INT NOT NULL,
    `price_at_purchase` DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
  );

CREATE TABLE
  `reviews` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `product_id` INT,
    `user_id` INT,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment` TEXT,
    `review_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
  );
