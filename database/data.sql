INSERT INTO `roles` (`name`, `description`) VALUES
('admin', 'Người quản trị cao nhất, có toàn quyền hệ thống.'),
('customer', 'Khách hàng.');

-- NOTE: TEST DATA DO NOT RUN THIS
INSERT INTO `role_user` (`user_id`, `role_id`) VALUES
-- Gán vai trò "Store Manager" (role_id=2) cho người dùng có user_id=10
(10, 2),
-- Gán vai trò "Customer" (role_id=4) cho người dùng có user_id=11
(11, 4);
