<div class="card bg-base-100 shadow-xl">
  <div class="card-body">
    <h2 class="card-title mb-4">Quản lý đơn hàng</h2>
    <div class="overflow-x-auto">
      <table
        class="table"
        id="orders-table"
      >
        <thead>
          <tr>
            <th>Mã Đơn Hàng</th>
            <th>Khách Hàng</th>
            <th>Ngày Đặt</th>
            <th>Tổng Tiền</th>
            <th>Trạng Thái</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td class="font-semibold">#<?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</td>
                <td class="flex items-center gap-2">
                  <select
                    class="select select-bordered select-sm status-select"
                    data-order-id="<?php echo $order['id']; ?>"
                    data-current-status="<?php echo $order['status']; ?>"
                    <?php echo in_array($order['status'], ['completed', 'cancelled']) ? 'disabled' : ''; ?>
                  >
                    <?php
                    // Define valid transitions based on current status
                    $validTransitions = [
                      'pending' => [
                        'pending' => 'Đang xử lý',
                        'packing' => 'Đang đóng gói',
                        'cancelled' => 'Hủy đơn'
                      ],
                      'packing' => [
                        'packing' => 'Đang đóng gói',
                        'shipping' => 'Đang giao',
                        'cancelled' => 'Hủy đơn'
                      ],
                      'shipping' => [
                        'shipping' => 'Đang giao',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Hủy đơn'
                      ],
                      'completed' => [
                        'completed' => 'Hoàn thành'
                      ],
                      'cancelled' => [
                        'cancelled' => 'Đã hủy'
                      ]
                    ];

                    $currentStatus = $order['status'];
                    $availableStatuses = $validTransitions[$currentStatus] ?? [];

                    foreach ($availableStatuses as $statusValue => $statusLabel):
                      ?>
                      <option
                        value="<?php echo $statusValue; ?>"
                        <?php echo $order['status'] === $statusValue ? 'selected' : ''; ?>
                      ><?php echo $statusLabel; ?></option>
                    <?php endforeach; ?>
                  </select>
                  <?php if ($order['status'] === 'cancelled' && !empty($order['cancellation_reason'])): ?>
                    <div
                      class="tooltip tooltip-left"
                      data-tip="<?php echo htmlspecialchars($order['cancellation_reason']); ?>"
                    >
                      <!-- INFO icon -->
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 inline-block ml-1 cursor-help text-error"
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
                  <button
                    class="btn btn-sm btn-ghost view-details-btn"
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
                colspan="6"
                class="text-center"
              >Không có đơn hàng nào.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

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
        <select
          name="cancellation_reason"
          id="cancellation-reason-select"
          class="select select-bordered mb-2"
        >
          <option value="">Chọn lý do</option>
          <option value="Khách hàng yêu cầu hủy">Khách hàng yêu cầu hủy</option>
          <option value="Sản phẩm hết hàng">Sản phẩm hết hàng</option>
          <option value="Không liên hệ được khách hàng">Không liên hệ được khách hàng</option>
          <option value="Địa chỉ giao hàng không hợp lệ">Địa chỉ giao hàng không hợp lệ</option>
          <option value="Đơn hàng trùng lặp">Đơn hàng trùng lặp</option>
          <option value="Khác">Khác (nhập chi tiết bên dưới)</option>
        </select>
        <textarea
          name="cancellation_reason_text"
          id="cancellation-reason-text"
          class="textarea textarea-bordered h-24"
          placeholder="Nhập lý do chi tiết (nếu chọn 'Khác' hoặc muốn bổ sung thông tin)..."
        ></textarea>
        <label class="label">
          <span class="label-text-alt text-gray-500">Lý do hủy sẽ được gửi cho khách hàng</span>
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
      class="font-bold text-lg"
      id="modal-order-title"
    >Chi tiết đơn hàng</h3>
    <div
      id="modal-order-customer-info"
      class="prose prose-sm mt-2"
    ></div>
    <div class="divider"></div>
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
</dialog>

<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/dashboard/orders.js"
></script>
