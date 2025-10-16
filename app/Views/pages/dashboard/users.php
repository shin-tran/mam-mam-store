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
              <br />
              <span class="badge badge-ghost badge-sm"><?php echo htmlspecialchars($user['phone_number'] ?? 'Chưa có SĐT'); ?></span>
            </td>
            <td><?php echo htmlspecialchars($user['roles'] ?? 'Chưa có vai trò'); ?></td>
            <td>
              <?php if ($user['is_activated']): ?>
                <span class="badge badge-success badge-sm">Đã kích hoạt</span>
              <?php else: ?>
                <span class="badge badge-warning badge-sm">Chưa kích hoạt</span>
              <?php endif; ?>
            </td>
            <th>
              <button class="btn btn-ghost">Sửa</button>
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
