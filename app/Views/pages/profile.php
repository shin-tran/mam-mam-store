<main class="mx-auto w-full max-w-7xl px-4 py-8">
  <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
    <!-- Sidebar -->
    <div class="col-span-1">
      <!-- Avatar Card -->
      <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body items-center text-center">
          <div class="avatar relative group">
            <div class="w-32 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
              <img
                id="avatar-preview"
                src="<?php echo $user['avatar_path'] ? _HOST_URL_PUBLIC.$user['avatar_path'] : 'https://placehold.co/128x128?text=Avatar'; ?>"
              />
            </div>
            <label
              for="avatar-upload"
              class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 cursor-pointer rounded-full transition-opacity"
            >
              Đổi ảnh
            </label>
          </div>
          <input
            type="file"
            id="avatar-upload"
            class="hidden"
            accept="image/png, image/jpeg, image/webp"
          />
          <h2 class="card-title mt-4"><?php echo htmlspecialchars($user['full_name']); ?></h2>
          <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
      </div>

      <div
        role="tablist"
        class="tabs tabs-lifted"
      >
        <input
          type="radio"
          name="profile_tabs"
          role="tab"
          class="tab"
          aria-label="Thông tin"
          checked
        />
        <div
          role="tabpanel"
          class="tab-content bg-base-100 border-base-300 rounded-box p-6"
        >
          <!-- Profile Info Form -->
          <form id="profile-info-form">
            <div class="space-y-4">
              <div class="form-control">
                <label class="label"><span class="label-text">Họ và tên</span></label>
                <input
                  type="text"
                  name="full_name"
                  value="<?php echo htmlspecialchars($user['full_name']); ?>"
                  class="input input-bordered"
                  required
                />
              </div>
              <div class="form-control">
                <label class="label"><span class="label-text">Email</span></label>
                <input
                  type="email"
                  value="<?php echo htmlspecialchars($user['email']); ?>"
                  class="input input-bordered"
                  disabled
                />
              </div>
              <div class="form-control">
                <label class="label"><span class="label-text">Số điện thoại</span></label>
                <input
                  type="tel"
                  name="phone_number"
                  value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>"
                  class="input input-bordered"
                />
              </div>
              <div class="form-control">
                <label class="label"><span class="label-text">Địa chỉ</span></label>
                <textarea
                  name="address"
                  class="textarea textarea-bordered h-24"
                ><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
              </div>
            </div>
            <button
              type="submit"
              class="btn btn-primary mt-6"
            >Lưu thay đổi</button>
          </form>
        </div>

        <input
          type="radio"
          name="profile_tabs"
          role="tab"
          class="tab"
          aria-label="Mật khẩu"
        />
        <div
          role="tabpanel"
          class="tab-content bg-base-100 border-base-300 rounded-box p-6"
        >
          <!-- Change Password Form -->
          <form id="change-password-form">
            <div class="space-y-4">
              <div class="form-control">
                <label class="label"><span class="label-text">Mật khẩu hiện tại</span></label>
                <input
                  type="password"
                  name="current_password"
                  class="input input-bordered"
                  required
                />
              </div>
              <div class="form-control">
                <label class="label"><span class="label-text">Mật khẩu mới</span></label>
                <input
                  type="password"
                  name="new_password"
                  class="input input-bordered"
                  required
                />
              </div>
              <div class="form-control">
                <label class="label"><span class="label-text">Xác nhận mật khẩu mới</span></label>
                <input
                  type="password"
                  name="confirm_password"
                  class="input input-bordered"
                  required
                />
              </div>
            </div>
            <button
              type="submit"
              class="btn btn-primary mt-6"
            >Đổi mật khẩu</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-span-1 md:col-span-3">
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">Lịch sử đơn hàng</h2>
          <div class="overflow-x-auto">
            <table class="table">
              <thead>
                <tr>
                  <th>Mã Đơn</th>
                  <th>Ngày Đặt</th>
                  <th>Tổng Tiền</th>
                  <th>Trạng Thái</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($orders)): ?>
                  <?php foreach ($orders as $order): ?>
                    <tr>
                      <td class="font-semibold">#<?php echo $order['id']; ?></td>
                      <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                      <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</td>
                      <td>
                        <?php
                        $status_text = '';
                        $badge_class = '';
                        switch ($order['status']) {
                          case 'pending':
                            $status_text = 'Đang xử lý';
                            $badge_class = 'badge-warning';
                            break;
                          case 'shipping':
                            $status_text = 'Đang giao';
                            $badge_class = 'badge-info';
                            break;
                          case 'completed':
                            $status_text = 'Hoàn thành';
                            $badge_class = 'badge-success';
                            break;
                          case 'cancelled':
                            $status_text = 'Đã hủy';
                            $badge_class = 'badge-error';
                            break;
                        }
                        ?>
                        <div class="badge <?php echo $badge_class; ?> badge-sm"><?php echo $status_text; ?></div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td
                      colspan="4"
                      class="text-center py-4"
                    >Bạn chưa có đơn hàng nào.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/profile.js"
></script>
