<!-- Page content -->
<main class="mx-auto w-full">
  <!-- Product section -->
  <section
    id="menu"
    class="py-16"
  >
    <div class="container mx-auto px-4 max-w-7xl">
      <h1 class="text-3xl font-bold mb-6">Giỏ Hàng Của Bạn</h1>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div
          class="lg:col-span-2 space-y-4"
          id="cart-items-container"
        >
          <!-- Các sản phẩm trong giỏ hàng sẽ được JS render vào đây -->
        </div>

        <!-- Cột tóm tắt đơn hàng -->
        <div class="lg:col-span-1">
          <div class="card bg-base-100 shadow-xl sticky top-8">
            <div class="card-body">
              <h2 class="card-title text-xl mb-4">Tóm tắt đơn hàng</h2>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span>Tạm tính</span>
                  <span
                    id="subtotal"
                    class="font-medium"
                  >0 ₫</span>
                </div>
                <div class="flex justify-between">
                  <span>Phí vận chuyển</span>
                  <span
                    id="shipping-fee"
                    class="font-medium"
                  >0 ₫</span>
                </div>
              </div>
              <div class="divider my-4"></div>
              <div class="flex justify-between text-lg font-bold">
                <span>Tổng cộng</span>
                <span id="total-price">0 ₫</span>
              </div>
              <div class="card-actions mt-6">
                <button
                  class="btn btn-primary btn-block"
                  onclick="checkout_modal.showModal()"
                >Tiến hành thanh toán</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Thanh Toán -->
      <dialog
        id="checkout_modal"
        class="modal"
      >
        <div class="modal-box w-11/12 max-w-2xl">
          <h3 class="font-bold text-2xl mb-6">Thông tin giao hàng & Thanh toán</h3>
          <form
            method="dialog"
            id="checkout-form"
          >
            <div class="space-y-4">
              <!-- Thông tin cá nhân -->
              <div>
                <label class="label"><span class="label-text">Họ và tên</span></label>
                <input
                  type="text"
                  placeholder="Nguyễn Văn A"
                  class="input input-bordered w-full"
                  required
                />
              </div>
              <div>
                <label class="label"><span class="label-text">Số điện thoại</span></label>
                <input
                  type="tel"
                  placeholder="09xxxxxxxx"
                  class="input input-bordered w-full"
                  required
                />
              </div>
              <div>
                <label class="label"><span class="label-text">Địa chỉ nhận hàng</span></label>
                <textarea
                  class="textarea textarea-bordered w-full"
                  placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố"
                  required
                ></textarea>
              </div>

              <div class="divider"></div>

              <!-- Phương thức thanh toán -->
              <h4 class="font-semibold text-lg">Phương thức thanh toán</h4>
              <div class="form-control">
                <label class="label cursor-pointer justify-start gap-4">
                  <input
                    type="radio"
                    name="payment-method"
                    class="radio radio-primary"
                    checked
                  />
                  <span class="label-text">Thanh toán khi nhận hàng (COD)</span>
                </label>
              </div>
              <div class="form-control">
                <label class="label cursor-pointer justify-start gap-4">
                  <input
                    type="radio"
                    name="payment-method"
                    class="radio radio-primary"
                  />
                  <span class="label-text">Chuyển khoản ngân hàng</span>
                </label>
              </div>
            </div>

            <div class="modal-action mt-8">
              <button
                type="submit"
                class="btn btn-primary btn-block"
              >Xác nhận đặt hàng</button>
            </div>
          </form>
        </div>
      </dialog>

      <!-- Modal Đặt Hàng Thành Công -->
      <dialog
        id="success_modal"
        class="modal"
      >
        <div class="modal-box text-center">
          <i class="fa-solid fa-circle-check text-success text-6xl mb-4"></i>
          <h3 class="font-bold text-2xl">Đặt hàng thành công!</h3>
          <p class="py-4">Cảm ơn bạn đã mua sắm tại Măm Măm Store. Chúng tôi sẽ liên hệ với bạn để xác nhận đơn hàng
            trong thời gian sớm nhất.</p>
          <div class="modal-action justify-center">
            <form method="dialog">
              <button class="btn btn-primary">Tiếp tục mua sắm</button>
            </form>
          </div>
        </div>
      </dialog>
    </div>
  </section>
</main>
<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/orders.js"
></script>