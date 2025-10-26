<!-- Stats Cards -->
<div class="stats shadow w-full mb-6 stats-vertical lg:stats-horizontal">
  <div class="stat">
    <div class="stat-title">Doanh thu tháng này</div>
    <div class="stat-value text-primary"><?php echo number_format($stats['monthly_revenue'] ?? 0, 0, ',', '.'); ?> ₫</div>
    <div class="stat-desc">Chỉ tính đơn hàng đã hoàn thành</div>
  </div>

  <div class="stat">
    <div class="stat-title">Đơn hàng mới</div>
    <div class="stat-value text-secondary"><?php echo $stats['new_orders_count'] ?? 0; ?></div>
    <div class="stat-desc">Các đơn hàng đang chờ xử lý</div>
  </div>

  <div class="stat">
    <div class="stat-title">Khách hàng mới</div>
    <div class="stat-value"><?php echo $stats['new_users_count'] ?? 0; ?></div>
    <div class="stat-desc">Tài khoản đăng ký trong tháng này</div>
  </div>
</div>

<!-- Recent Orders Table -->
<div class="card bg-base-100 shadow-xl">
  <div class="card-body">
    <h2 class="card-title mb-4">Đơn hàng gần đây</h2>
    <div class="overflow-x-auto">
      <table class="table">
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Ngày đặt</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recentOrders)): ?>
            <?php foreach ($recentOrders as $order): ?>
              <tr>
                <th class="font-semibold">#<?php echo $order['id']; ?></th>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
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
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td
                colspan="5"
                class="text-center"
              >Chưa có đơn hàng nào gần đây.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/dashboard/index.js"
></script>
