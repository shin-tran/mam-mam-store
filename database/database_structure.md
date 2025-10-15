# Cấu trúc Database MySQL

## 1. Table `users`

Stores customer account information.

| Name         | Type         | Note             |
| ------------ | ------------ | ---------------- |
| id           | INT, PK, AI  |                  |
| full_name    | VARCHAR(255) | User's full name |
| email        | VARCHAR(255) | Unique not null  |
| phone_number | VARCHAR(10)  | Unique           |
| password     | VARCHAR(255) | Hashed password  |
| address      | TEXT         |                  |
| created_at   | TIMESTAMP    |                  |
| updated_at   | TIMESTAMP    |                  |

## 2. Table `categories`

Classifies the snack products. "Bim Bim", "Bánh Gạo", "Kẹo", "Đồ uống".

| Name          | Type         | Note   |
| ------------- | ------------ | ------ |
| id            | INT, PK, AI  |        |
| category_name | VARCHAR(255) | Unique |
| image_path    | VARCHAR(255) |        |

## 3. Table `products`

Stores snack food info.

| Name           | Type          | Note |
| -------------- | ------------- | ---- |
| id             | INT, PK, AI   |      |
| product_name   | VARCHAR(255)  |      |
| description    | TEXT          |      |
| price          | DECIMAL(10,2) |      |
| image_path     | VARCHAR(255)  |      |
| stock_quantity | INT           |      |
| created_at     | TIMESTAMP     |      |
| updated_at     | TIMESTAMP     |      |

## 4. Table `orders`

Stores information about orders placed by customers.

| Name             | Type          | Note                                                                           |
| ---------------- | ------------- | ------------------------------------------------------------------------------ |
| id               | INT, PK, AI   |                                                                                |
| user_id          | INT, FK       | Foreign key to table users                                                     |
| total_amount     | DECIMAL(10,2) |                                                                                |
| status           | VARCHAR(50)   | Current status of the order ('pending', 'shipping', 'completed', 'cancelled'). |
| shipping_address | VARCHAR(255)  |                                                                                |
| shipping_phone   | VARCHAR(10)   |                                                                                |
| note             | TEXT          |                                                                                |
| order_date       | TIMESTAMP     |                                                                                |

## 5. Table `order_details`

Stores information about the orders.

| Name              | Type          | Note                          |
| ----------------- | ------------- | ----------------------------- |
| id                | INT, PK, AI   |                               |
| order_id          | INT, FK       | Foreign key to table orders   |
| product_id        | INT, FK       | Foreign key to table products |
| quantity          | INT           |                               |
| price_at_purchase | DECIMAL(10,2) |                               |

## 6. Table `reviews`

Phân loại sản phẩm.

| Name        | Type        | Note                          |
| ----------- | ----------- | ----------------------------- |
| id          | INT, PK, AI |                               |
| product_id  | INT, FK     | Foreign key to table products |
| user_id     | INT, FK     | Foreign key to table users    |
| rating      | TINYINT     | from 1 to 5 stars             |
| comment     | TEXT        |                               |
| review_date | TIMESTAMP   |                               |
