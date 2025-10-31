<main class="mx-auto w-full max-w-7xl px-4 py-8">
  <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
    <!-- Sidebar -->
    <div class="col-span-1">
      <!-- Avatar Card -->
      <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body items-center text-center">
          <form
            id="avatar-form"
            class="flex flex-col items-center"
          >
            <div class="avatar relative group">
              <div class="w-32 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                <img
                  id="avatar-preview"
                  src="<?php echo $user['avatar_path'] ? _HOST_URL_PUBLIC.$user['avatar_path'] : 'https://placehold.co/128x128?text=Avatar'; ?>"
                />
              </div>
              <label
                for="avatar-input"
                class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 cursor-pointer rounded-full transition-opacity"
              >
                Đổi ảnh
              </label>
            </div>
            <input
              type="file"
              id="avatar-input"
              class="hidden"
              accept="image/png, image/jpeg, image/webp"
            />
            <button
              type="submit"
              class="btn btn-primary mt-4 hidden"
              id="avatar-submit-btn"
            >Lưu ảnh</button>
          </form>
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
          <form id="details-form">
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
          <form id="password-form">
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
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($orders)): ?>
                  <?php foreach ($orders as $order): ?>
                    <tr>
                      <td class="font-semibold">#<?php echo $order['id']; ?></td>
                      <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
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
                          case 'packing':
                            $status_text = 'Đang đóng gói';
                            $badge_class = 'badge-info';
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
                        <?php if ($order['status'] === 'cancelled' && !empty($order['cancellation_reason'])): ?>
                          <div
                            class="tooltip tooltip-left"
                            data-tip="<?php echo htmlspecialchars($order['cancellation_reason']); ?>"
                          >
                            <!-- Icon -->
                            <svg
                              xmlns="http://www.w3.org/2000/svg"
                              class="h-4 w-4 inline-block ml-1 cursor-help"
                              fill="none"
                              viewBox="0 0 24 24"
                              stroke="currentColor"
                            >
                              <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                              />
                            </svg>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($order['status'] === 'pending'): ?>
                          <button
                            class="btn btn-error cancel-order-btn"
                            data-order-id="<?php echo $order['id']; ?>"
                          >
                            Hủy đơn
                          </button>
                        <?php endif; ?>
                        <button
                          class="btn btn-ghost view-order-details-btn"
                          data-order-id="<?php echo $order['id']; ?>"
                        >
                          Xem chi tiết
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td
                      colspan="5"
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

<!-- Cancel Order Modal -->
<dialog
  id="cancel_order_modal"
  class="modal"
>
  <div class="modal-box">
    <h3 class="font-bold text-lg mb-4">Hủy đơn hàng</h3>
    <form id="cancel-order-form">
      <input
        type="hidden"
        id="cancel-order-id"
        name="order_id"
      />
      <div class="form-control">
        <label class="label">
          <span class="label-text">Lý do hủy đơn hàng <span class="text-error">*</span></span>
        </label>
        <textarea
          name="cancellation_reason"
          class="textarea textarea-bordered h-24"
          placeholder="Vui lòng cho chúng tôi biết lý do bạn muốn hủy đơn hàng này..."
          required
        ></textarea>
        <label class="label">
          <span class="label-text-alt text-gray-500">Lý do hủy sẽ giúp chúng tôi cải thiện dịch vụ</span>
        </label>
      </div>
      <div class="modal-action">
        <button
          type="button"
          class="btn"
          onclick="cancel_order_modal.close()"
        >Đóng</button>
        <button
          type="submit"
          class="btn btn-error"
        >Xác nhận hủy</button>
      </div>
    </form>
  </div>
  <form
    method="dialog"
    class="modal-backdrop"
  >
    <button>close</button>
  </form>
</dialog>

<!-- Order Details Modal -->
<dialog
  id="order_details_modal"
  class="modal"
>
  <div class="modal-box w-11/12 max-w-3xl">
    <h3
      class="font-bold text-lg mb-4"
      id="modal-order-title"
    >Chi tiết đơn hàng</h3>

    <div
      id="modal-order-info"
      class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4"
    >
      <!-- Order info will be injected here -->
    </div>

    <div class="overflow-x-auto">
      <table class="table table-sm">
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th>Số lượng</th>
            <th>Giá</th>
            <th>Thành tiền</th>
          </tr>
        </thead>
        <tbody id="modal-order-items">
          <!-- Items will be injected here by JS -->
        </tbody>
      </table>
    </div>

    <div class="modal-action">
      <button
        class="btn"
        onclick="order_details_modal.close()"
      >Đóng</button>
    </div>
  </div>
  <form
    method="dialog"
    class="modal-backdrop"
  >
    <button>close</button>
  </form>
</dialog>

<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/profile.js"
></script>
