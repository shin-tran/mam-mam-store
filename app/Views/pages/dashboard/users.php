<div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100">
  <table
    class="table"
    id="users-table"
  >
    <!-- head -->
    <thead>
      <tr>
        <th>Họ Tên</th>
        <th>Thông tin liên hệ</th>
        <th>Vai trò</th>
        <th>Trạng thái</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>
          <tr data-user-id="<?php echo $user['id']; ?>">
            <td>
              <div class="flex items-center gap-3">
                <div class="avatar w-12">
                  <img
                    alt="User avatar"
                    src="https://placehold.co/40x40?text=<?php
                    $split = explode(" ", $user['full_name']);
                    echo $split[count($split) - 1];
                    ?>"
                    class="rounded-full"
                  />
                </div>
                <div>
                  <div class="font-bold"><?php echo htmlspecialchars($user['full_name']); ?></div>
                </div>
              </div>
            </td>
            <td>
              <?php echo htmlspecialchars($user['email']); ?>
            </td>
            <td><?php echo htmlspecialchars($user['role'] ?? 'Chưa có vai trò'); ?></td>
            <td>
              <?php if ($user['is_activated']): ?>
                <span class="badge badge-success badge-sm">Đã kích hoạt</span>
              <?php else: ?>
                <span class="badge badge-warning badge-sm">Chưa kích hoạt</span>
              <?php endif; ?>
            </td>
            <th>
              <button
                class="btn btn-ghost btn-edit"
                data-user-id="<?php echo $user['id']; ?>"
                data-user-name="<?php echo htmlspecialchars($user['full_name']); ?>"
                data-user-email="<?php echo htmlspecialchars($user['email']); ?>"
                data-user-role="<?php echo htmlspecialchars($user['role'] ?? ''); ?>"
                data-user-activated="<?php echo $user['is_activated'] ? '1' : '0'; ?>"
              >Sửa</button>
              <button
                class="btn btn-ghost text-error btn-delete"
                data-user-id="<?php echo $user['id']; ?>"
                data-user-name="<?php echo htmlspecialchars($user['full_name']); ?>"
              >Xóa</button>
            </th>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td
            colspan="5"
            class="text-center"
          >Không tìm thấy người dùng nào.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Edit User Modal -->
<dialog
  id="edit_user_modal"
  class="modal"
>
  <div class="modal-box max-w-2xl">
    <h3 class="font-bold text-lg mb-4">Chỉnh sửa thông tin người dùng</h3>
    <form id="edit-user-form">
      <input
        type="hidden"
        name="user_id"
        id="edit-user-id"
      >
      <div class="space-y-4">
        <!-- Row 1: Họ tên & Email -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="form-control">
            <label class="label">
              <span class="label-text">Họ Tên <span class="text-error">*</span></span>
            </label>
            <input
              type="text"
              name="full_name"
              id="edit-full-name"
              class="input input-bordered"
              placeholder="Nguyễn Văn A"
              required
            />
          </div>

          <div class="form-control">
            <label class="label">
              <span class="label-text">Email <span class="text-error">*</span></span>
            </label>
            <input
              type="email"
              name="email"
              id="edit-email"
              class="input input-bordered"
              placeholder="example@email.com"
              required
            />
          </div>
        </div>

        <!-- Row 2: Vai trò & Trạng thái -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="form-control">
            <label class="label">
              <span class="label-text">Vai trò <span class="text-error">*</span></span>
            </label>
            <select
              id="edit-role"
              name="role_id"
              class="select select-bordered"
              required
            >
              <option
                disabled
                selected
              >Chọn vai trò</option>
              <?php foreach ($allRoles as $role): ?>
                <option value="<?php echo $role['id']; ?>">
                  <?php echo htmlspecialchars($role['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-control">
            <label class="label">
              <span class="label-text">Trạng thái</span>
            </label>
            <select
              id="edit-is-activated"
              name="is_activated"
              class="select select-bordered"
            >
              <option value="1">Đã kích hoạt</option>
              <option value="0">Chưa kích hoạt</option>
            </select>
          </div>
        </div>

        <div class="divider my-2">Thay đổi mật khẩu (tùy chọn)</div>

        <!-- Row 3: Mật khẩu mới & Xác nhận -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="form-control">
            <label class="label">
              <span class="label-text">Mật khẩu mới</span>
            </label>
            <input
              type="password"
              name="new_password"
              id="edit-new-password"
              class="input input-bordered"
              placeholder="Tối thiểu 6 ký tự"
            />
            <label class="label">
              <span class="label-text-alt text-gray-500">Để trống nếu không đổi</span>
            </label>
          </div>

          <div class="form-control">
            <label class="label">
              <span class="label-text">Xác nhận mật khẩu</span>
            </label>
            <input
              type="password"
              name="confirm_password"
              id="edit-confirm-password"
              class="input input-bordered"
              placeholder="Nhập lại mật khẩu"
            />
          </div>
        </div>
      </div>

      <div class="modal-action mt-6">
        <button
          type="button"
          class="btn btn-ghost"
          onclick="edit_user_modal.close()"
        >Hủy</button>
        <button
          type="submit"
          class="btn btn-primary"
        >Lưu thay đổi</button>
      </div>
    </form>
  </div>
</dialog>

<!-- Delete Confirmation Modal -->
<dialog
  id="delete_user_modal"
  class="modal"
>
  <div class="modal-box">
    <h3 class="font-bold text-lg text-error">Xác nhận xóa người dùng!</h3>
    <p class="py-4">Bạn có chắc chắn muốn xóa người dùng "<strong id="user-name-to-delete"></strong>"? Hành động này không thể hoàn tác.</p>
    <div class="modal-action">
      <form
        method="dialog"
        class="w-full flex justify-end gap-2"
      >
        <button class="btn">Hủy</button>
        <button
          id="confirm-delete-btn"
          class="btn btn-error"
        >Xóa</button>
      </form>
    </div>
  </div>
</dialog>

<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/dashboard/users.js"
></script>
