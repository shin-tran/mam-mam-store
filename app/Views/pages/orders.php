<!DOCTYPE html>
<html lang="en">

<?php
use App\Core\View;
use App\Helpers\Helpers;
View::layout('head', ['title' => $title]);
?>

<body>
  <div class="drawer">
    <input
      id="mobile-drawer"
      type="checkbox"
      class="drawer-toggle"
    />
    <div class="drawer-content flex flex-col">
      <div class="shadow-sm bg-base-100 z-10 sticky top-0">
        <header class="navbar mx-auto justify-between max-w-7xl">
          <!-- Navbar title -->
          <div class="flex items-center lg:flex-none">
            <!-- Menu bar on mobile -->
            <div class="lg:hidden">
              <label
                for="mobile-drawer"
                aria-label="open sidebar"
                class="btn btn-square btn-ghost"
              >
                <img
                  src="<?php echo _HOST_URL_PUBLIC ?>/icons/menu.svg"
                  alt="Mobile menu icon"
                >
              </label>
            </div>
            <a
              href="/"
              class="text-xl flex items-center gap-2 font-semibold"
            >
              <img
                src="<?php echo _HOST_URL_PUBLIC ?>/icons/popcorn.svg"
                alt="Popcorn icon"
              >Măm Măm Store</a>
          </div>

          <!-- Action user -->
          <div>
            <!-- User -->
            <div class="dropdown dropdown-end">
              <div
                tabindex="0"
                role="button"
                class="btn btn-ghost btn-circle avatar"
              >
                <div class="w-10 rounded-full">
                  <img
                    alt="User avatar"
                    src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp"
                  />
                </div>
              </div>
              <ul
                tabindex="0"
                class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-37 p-2 shadow [&_li>*]:text-sm"
              >
                <li>
                  <a class="justify-between">
                    Thông tin cá nhân
                  </a>
                </li>
                <li>
                  <a
                    class="justify-between"
                    href="<?php echo _HOST_URL ?>/dashboard"
                  >
                    Dashboard
                  </a>
                </li>
                <li><a>Cài đặt</a></li>
                <li id="btn-logout"><a>Đăng xuất</a></li>
              </ul>
            </div>
          </div>
        </header>
      </div>

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
    </div>

    <!-- Mobile menu -->
    <nav class="drawer-side">
      <label
        for="mobile-drawer"
        aria-label="close sidebar"
        class="drawer-overlay"
      ></label>
      <ul class="menu bg-base-200 min-h-full w-fit p-4">
        <!-- Sidebar content here -->
        <li>
          <details class="w-fit">
            <summary>
              Danh Mục Sản Phẩm
            </summary>
            <ul class="bg-base-100 [&_li>*]:rounded-none p-0 shadow">
              <li><a>Bánh Mặn</a></li>
              <li><a>Bánh Ngọt</a></li>
              <li><a>Kẹo các loại</a></li>
            </ul>
          </details>
        </li>
        <li><a>Sidebar Item 2</a></li>
      </ul>
    </nav>
  </div>
  <script
    type="module"
    src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/orders.js"
  ></script>
</body>

</html>