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
                <td>
                  <select
                    class="select select-bordered select-sm status-select"
                    data-order-id="<?php echo $order['id']; ?>"
                  >
                    <option
                      value="pending"
                      <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>
                    >Đang xử lý</option>
                    <option
                      value="packing"
                      <?php echo $order['status'] === 'packing' ? 'selected' : ''; ?>
                    >Đang đóng gói</option>
                    <option
                      value="shipping"
                      <?php echo $order['status'] === 'shipping' ? 'selected' : ''; ?>
                    >Đang giao</option>
                    <option
                      value="completed"
                      <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>
                    >Hoàn thành</option>
                    <option
                      value="cancelled"
                      <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>
                    >Đã hủy</option>
                  </select>
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
