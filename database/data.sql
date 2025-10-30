-- Xóa dữ liệu cũ trong bảng products và categories để tránh trùng lặp
DELETE FROM `products`;
DELETE FROM `categories`;

-- Reset auto-increment cho bảng categories và products
ALTER TABLE `categories` AUTO_INCREMENT = 1;
ALTER TABLE `products` AUTO_INCREMENT = 1;

INSERT INTO `roles` (`name`, `description`) VALUES
('admin', 'Người quản trị cao nhất, có toàn quyền hệ thống.'),
('customer', 'Khách hàng.');

-- Thêm các danh mục sản phẩm
INSERT INTO `categories` (`category_name`) VALUES
('Bim Bim và Snack'),
('Bánh Gạo và Cơm Cháy'),
('Đồ Khô và Hạt'),
('Kẹo và Bánh Ngọt'),
('Đồ Uống');

-- Thêm sản phẩm cho danh mục "Bim Bim & Snack" (category_id = 1)
INSERT INTO `products` (`product_name`, `price`, `stock_quantity`, `description`, `category_id`, `image_path`) VALUES
('Bim Bim Oishi Phồng Tôm', 8000, 150, 'Vị phồng tôm đậm đà, giòn tan, là món ăn vặt không thể thiếu của tuổi thơ.', 1, '/uploads/products/placeholder.png'),
('Snack Khoai Tây Lay''s Vị Tảo Biển', 12000, 120, 'Lát khoai tây mỏng giòn, kết hợp hương vị tảo biển Nori đặc trưng của Nhật Bản.', 1, '/uploads/products/placeholder.png'),
('O''Star Phô Mai', 10000, 130, 'Khoai tây tươi cắt lát, tẩm ướp gia vị phô mai béo ngậy, hấp dẫn.', 1, '/uploads/products/placeholder.png'),
('Bim Bim Cua Poca', 7000, 200, 'Hương vị cua thơm lừng, miếng bim bim giòn xốp, ăn là ghiền.', 1, '/uploads/products/placeholder.png');

-- Thêm sản phẩm cho danh mục "Bánh Gạo & Cơm Cháy" (category_id = 2)
INSERT INTO `products` (`product_name`, `price`, `stock_quantity`, `description`, `category_id`, `image_path`) VALUES
('Bánh Gạo One One Vị Mật Ong', 25000, 80, 'Bánh gạo giòn rụm phủ một lớp mật ong ngọt ngào, thơm dịu, tốt cho sức khỏe.', 2, '/uploads/products/placeholder.png'),
('Cơm Cháy Chà Bông Siêu Ruốc', 35000, 60, 'Miếng cơm cháy giòn tan, phủ đầy chà bông heo đậm đà và mỡ hành thơm phức.', 2, '/uploads/products/placeholder.png'),
('Bánh Gạo An Vị Tự Nhiên', 22000, 90, 'Bánh gạo nướng không chiên qua dầu, giữ trọn vị ngọt tự nhiên của hạt gạo.', 2, '/uploads/products/placeholder.png');

-- Thêm sản phẩm cho danh mục "Đồ Khô & Hạt" (category_id = 3)
INSERT INTO `products` (`product_name`, `price`, `stock_quantity`, `description`, `category_id`, `image_path`) VALUES
('Khô Gà Lá Chanh', 45000, 50, 'Thịt ức gà xé sợi, sấy khô cùng lá chanh và ớt. Vị cay ngọt đậm đà, thơm nồng.', 3, '/uploads/products/placeholder.png'),
('Khô Bò Sợi Mềm', 55000, 40, 'Sợi thịt bò mềm, tẩm ướp gia vị ngũ vị hương, cay nhẹ, ăn hoài không ngán.', 3, '/uploads/products/placeholder.png'),
('Hạt Điều Rang Muối', 60000, 70, 'Hạt điều Bình Phước loại 1, rang giòn với muối tinh, béo ngậy và dinh dưỡng.', 3, '/uploads/products/placeholder.png'),
('Đậu Phộng Da Cá', 20000, 100, 'Hạt đậu phộng được bọc lớp bột giòn tan vị nước cốt dừa, thơm ngon khó cưỡng.', 3, '/uploads/products/placeholder.png');

-- Thêm sản phẩm cho danh mục "Kẹo & Bánh Ngọt" (category_id = 4)
INSERT INTO `products` (`product_name`, `price`, `stock_quantity`, `description`, `category_id`, `image_path`) VALUES
('Kẹo Dẻo Chupa Chups', 15000, 110, 'Kẹo dẻo hình con sâu với nhiều hương vị trái cây chua ngọt, dẻo dai vui miệng.', 4, '/uploads/products/placeholder.png'),
('Bánh Chocopie Hộp 12 Cái', 52000, 60, 'Lớp bánh bông lan mềm xốp, kẹp giữa là lớp marshmallow dẻo và phủ socola.', 4, '/uploads/products/placeholder.png'),
('Bánh Quy Bơ Danisa', 90000, 30, 'Hộp bánh quy bơ cao cấp từ Đan Mạch với nhiều hình dạng, thơm lừng vị bơ sữa.', 4, '/uploads/products/placeholder.png'),
('Kẹo Mút Alpenliebe', 2000, 300, 'Vị caramen sữa ngọt ngào, béo ngậy tan chảy trong miệng.', 4, '/uploads/products/placeholder.png');

-- Thêm sản phẩm cho danh mục "Đồ Uống" (category_id = 5)
INSERT INTO `products` (`product_name`, `price`, `stock_quantity`, `description`, `category_id`, `image_path`) VALUES
('Nước Ngọt Coca-Cola', 10000, 200, 'Nước ngọt có ga vị cola truyền thống, sảng khoái tức thì.', 5, '/uploads/products/placeholder.png'),
('Trà Ô Long Tea+ Plus', 12000, 150, 'Trà ô long nguyên chất giúp giảm hấp thụ chất béo, cho cảm giác nhẹ nhàng.', 5, '/uploads/products/placeholder.png'),
('Sữa Chua Uống Yakult', 25000, 80, 'Lốc 5 chai sữa chua uống lên men tự nhiên, tốt cho hệ tiêu hóa.', 5, '/uploads/products/placeholder.png'),
('Nước Suối Aquafina', 6000, 250, 'Nước tinh khiết, mát lành, giúp bạn bù nước và thanh lọc cơ thể.', 5, '/uploads/products/placeholder.png');

-- Thêm một số khu vực phục vụ mẫu (Ví dụ: TP.HCM)
INSERT INTO
  `serviceable_locations` (`city`, `district`)
VALUES
  ('Hồ Chí Minh', 'Quận 1'),
  ('Hồ Chí Minh', 'Quận 3'),
  ('Hồ Chí Minh', 'Quận 10'),
  ('Hồ Chí Minh', 'Quận Gò Vấp'),
  ('Hồ Chí Minh', 'Quận Bình Thạnh');

-- Thêm một số cấu hình mặc định
INSERT INTO
  `shipping_config` (`config_key`, `config_value`, `description`)
VALUES
  (
    'FREE_SHIPPING_THRESHOLD',
    '300000',
    'Ngưỡng miễn phí vận chuyển (VND). Đặt 0 nếu không miễn phí.'
  ),
  (
    'STANDARD_SHIPPING_FEE',
    '25000',
    'Phí vận chuyển tiêu chuẩn (VND) nếu không đủ điều kiện miễn phí.'
  );

-- NOTE: TEST DATA DO NOT RUN THIS
INSERT INTO `role_user` (`user_id`, `role_id`) VALUES (1, 1);
