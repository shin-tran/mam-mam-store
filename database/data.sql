INSERT INTO `roles` (`name`, `description`) VALUES
('Super Admin', 'Người quản trị cao nhất, có toàn quyền hệ thống.'),
('Store Manager', 'Quản lý sản phẩm, danh mục và đơn hàng.'),
('Order Staff', 'Nhân viên xem và xử lý đơn hàng.'),
('Customer', 'Khách hàng.');

INSERT INTO `permissions` (`name`, `description`) VALUES
('products.create', 'Tạo sản phẩm mới'),
('products.edit', 'Chỉnh sửa sản phẩm'),
('products.delete', 'Xóa sản phẩm'),
('products.view', 'Xem danh sách sản phẩm trong admin'),
('orders.view_all', 'Xem tất cả đơn hàng'),
('orders.view_own', 'Chỉ xem đơn hàng của bản thân'),
('orders.update_status', 'Cập nhật trạng thái đơn hàng'),
('users.view', 'Xem danh sách người dùng'),
('users.edit', 'Chỉnh sửa người dùng khác'),
('users.delete', 'Xóa người dùng'),
('users.assign_roles', 'Gán vai trò cho người dùng'),
('reviews.create', 'Viết đánh giá'),
('reviews.delete', 'Xóa bất kỳ đánh giá nào');

-- Gán Quyền cho Vai trò "Store Manager" (ID=2)
INSERT INTO `permission_role` (`permission_id`, `role_id`) VALUES
(1, 2), -- product.create
(2, 2), -- product.edit
(3, 2), -- product.delete
(4, 2), -- product.view
(5, 2), -- orders.view_all
(7, 2), -- orders.update_status
(13, 2); -- reviews.delete

-- Gán Quyền cho Vai trò "Customer" (ID=4)
INSERT INTO `permission_role` (`permission_id`, `role_id`) VALUES
(6, 4), -- orders.view_own
(12, 4); -- reviews.create

-- Gán vai trò "Store Manager" (role_id=2) cho người dùng có user_id=10
INSERT INTO `role_user` (`user_id`, `role_id`) VALUES (10, 2);

-- Gán vai trò "Customer" (role_id=4) cho người dùng có user_id=11
INSERT INTO `role_user` (`user_id`, `role_id`) VALUES (11, 4);