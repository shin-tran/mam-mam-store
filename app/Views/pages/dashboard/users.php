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
                data-user-address="<?php echo htmlspecialchars($user['address'] ?? ''); ?>"
                data-user-role="<?php echo htmlspecialchars($user['role'] ?? ''); ?>"
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
  <div class="modal-box">
    <h3 class="font-bold text-lg">Chỉnh sửa thông tin người dùng</h3>
    <form id="edit-user-form">
      <input
        type="hidden"
        name="user_id"
        id="edit-user-id"
      >
      <div class="py-4 space-y-4 [&>div]:w-full">
        <div class="form-control flex justify-between">
          <label class="label"><span class="label-text required">Họ Tên</span></label>
          <input
            type="text"
            name="full_name"
            id="edit-full-name"
            class="input input-bordered"
            required
          />
        </div>

        <div class="form-control flex justify-between">
          <label class="label"><span class="label-text">Địa chỉ</span></label>
          <textarea
            name="address"
            id="edit-address"
            class="textarea textarea-bordered"
          ></textarea>
        </div>

        <div class="form-control flex justify-between">
          <label class="label"><span class="label-text required">Vai trò</span></label>
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
      </div>
      <div class="modal-action">
        <button
          type="button"
          class="btn"
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
