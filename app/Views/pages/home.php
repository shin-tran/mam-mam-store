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

          <!-- Navbar items -->
          <div class="hidden flex-none lg:flex">
            <ul class="menu menu-horizontal">
              <li class="dropdown dropdown-hover pb-1 -mb-1">
                <div
                  tabindex="0"
                  role="button"
                  class="menu-item"
                >Danh Mục Sản Phẩm</div>
                <ul
                  tabindex="0"
                  class="dropdown-content menu w-fit bg-base-100 rounded-box z-1 shadow-sm mt-1!"
                >
                  <li><a>Item 1</a></li>
                  <li><a>Item 2</a></li>
                </ul>
              </li>
              <li><a>Bánh Trung Thu 2025</a></li>
              <li><a>Bánh Tuổi Thơ</a></li>
            </ul>
          </div>

          <!-- Action user -->
          <div>
            <?php if (Helpers::isLoggedIn()): ?>
              <!-- Cart -->
              <div class="dropdown dropdown-end">
                <div
                  tabindex="0"
                  role="button"
                  class="btn btn-ghost btn-circle"
                >
                  <div class="indicator">
                    <img
                      src="<?php echo _HOST_URL_PUBLIC ?>/icons/shopping-cart.svg"
                      alt="Shopping cart icon"
                    >
                    <span class="badge badge-sm indicator-item">8</span>
                  </div>
                </div>
                <div
                  tabindex="0"
                  class="card card-compact dropdown-content bg-base-100 z-1 mt-3 w-52 shadow"
                >
                  <div class="card-body">
                    <span class="text-lg font-bold">8 sản phẩm</span>
                    <span class="text-info">Tổng cộng: $999</span>
                    <div class="card-actions">
                      <!-- TODO: Modal or direct to new page -->
                      <a href="<?php echo _HOST_URL ?>/orders"><button class="btn btn-primary btn-block">Giỏ hàng</button></a>
                    </div>
                  </div>
                </div>
              </div>

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

            <?php else: ?>
              <!-- Login / Register -->
              <div class="hidden lg:inline-block">
                <a
                  href="<?php echo _HOST_URL ?>/login"
                  class="link"
                >Đăng nhập</a>
                <span> / </span>
                <a
                  href="<?php echo _HOST_URL ?>/register"
                  class="link"
                >Đăng ký</a>
              </div>
            <?php endif; ?>
          </div>
        </header>
      </div>

      <!-- Page content -->
      <main class="mx-auto w-full">
        <!-- Hero section -->
        <section
          class="hero min-h-[60vh] bg-base-100"
          style="background-image: url(<?php echo _HOST_URL_PUBLIC ?>/images/banner.jpg);"
        >
          <div class="hero-overlay"></div>
          <div class="hero-content text-center text-white">
            <div>
              <h1 class="text-5xl font-bold">Thế Giới Ăn Vặt Trong Tầm Tay</h1>
              <p class="py-6">Khám phá thiên đường đồ ăn vặt thơm ngon, đậm vị, đảm bảo vệ sinh. Đặt hàng ngay để nhận
                ưu đãi hấp dẫn!</p>
              <a class="btn">Xem Thực Đơn Ngay</a>
            </div>
          </div>
        </section>

        <!-- Product section -->
        <section
          id="menu"
          class="py-16"
        >
          <div class="container mx-auto px-4">
            <div class="text-center mb-12">
              <h2 class="text-4xl font-bold">Các Món Ăn Vặt</h2>
              <p class="text-lg mt-2 text-base-content/70">Những món ăn vặt được yêu thích nhất</p>
            </div>

            <div class="flex flex-col md:flex-row justify-center items-center gap-4 mb-8">
              <div class="join">
                <button
                  class="btn join-item filter-btn active"
                  data-category="all"
                >Tất Cả</button>
                <button
                  class="btn join-item filter-btn"
                  data-category="món khô"
                >Món Khô</button>
                <button
                  class="btn join-item filter-btn"
                  data-category="món nước"
                >Món Nước</button>
                <button
                  class="btn join-item filter-btn"
                  data-category="chiên rán"
                >Chiên Rán</button>
              </div>
              <div class="form-control">
                <input
                  type="text"
                  id="search-input"
                  placeholder="Tìm kiếm món ăn..."
                  class="input input-bordered w-full md:w-auto"
                />
              </div>
            </div>

            <div
              id="product-grid"
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8"
            >
              <div class="card bg-base-100 shadow-xl">
                <figure class="hover-gallery px-2 pt-2 group">
                  <img
                    src="https://www.vitaminhouse.vn/cdn/shop/files/chen_logo_vitaminhouse_-_2024-06-19T164242.601_300x.png?v=1744356419"
                    alt="Bánh tráng trộn"
                    class="rounded-t-xl h-48 w-full object-contain transition-transform duration-500 ease-in-out"
                  />
                  <img
                    src="https://www.vitaminhouse.vn/cdn/shop/files/OIP_96_200x.jpg?v=1751616165"
                    alt="Bánh tráng trộn"
                    class="rounded-t-xl h-48 w-full object-contain opacity-0 transition-all duration-500 ease-in-out group-hover:opacity-100"
                  />
                </figure>
                <div class="card-body items-center text-center p-4">
                  <h2 class="card-title">Bánh tráng trộn</h2>
                  <p class="font-semibold">20.000 ₫</p>
                  <div class="card-actions">
                    <button
                      class="btn add-to-cart-btn"
                      data-id="1"
                    >Thêm vào giỏ</button>
                  </div>
                </div>
                </nav>
              </div>
            </div>
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

        <!-- Login / Register -->
        <?php if (!Helpers::isLoggedIn()): ?>
          <li>
            <a href="<?php echo _HOST_URL ?>/login">Đăng nhập</a>
          </li>
          <li>
            <a href="<?php echo _HOST_URL ?>/register">Đăng ký</a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
  <script
    type="module"
    src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/home.js"
  ></script>
</body>

</html>