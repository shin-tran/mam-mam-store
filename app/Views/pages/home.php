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
                  <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                      <li><a><?php echo htmlspecialchars($category['category_name']); ?></a></li>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <li><a>Không có danh mục</a></li>
                  <?php endif; ?>
                </ul>
              </li>
            </ul>
          </div>

          <!-- Action user -->
          <div>
            <?php if (Helpers::isLoggedIn()): ?>
              <!-- Cart -->
              <div class="inline-block">
                <a href="<?php echo _HOST_URL ?>/orders">
                  <div class="btn btn-ghost btn-circle">
                    <div class="indicator">
                      <img
                        src="<?php echo _HOST_URL_PUBLIC ?>/icons/shopping-cart.svg"
                        alt="Shopping cart icon"
                      >
                      <!-- TODO: Lấy số lượng sản phẩm -->
                      <span class="badge badge-sm indicator-item">8</span>
                    </div>
                  </div>
                </a>
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
          <div class="hero-overlay bg-black/60"></div>
          <div class="hero-content text-center text-white">
            <div>
              <h1 class="text-5xl font-bold">Thế Giới Ăn Vặt Trong Tầm Tay</h1>
              <p class="py-6">Khám phá thiên đường đồ ăn vặt thơm ngon, đậm vị, đảm bảo vệ sinh. Đặt hàng ngay để nhận
                ưu đãi hấp dẫn!</p>
              <a
                href="#menu"
                class="btn btn-primary"
              >Xem Thực Đơn Ngay</a>
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
              <h2 class="text-4xl font-bold">Thực Đơn Của Chúng Tôi</h2>
              <p class="text-lg mt-2 text-base-content/70">Những món ăn vặt được yêu thích nhất</p>
            </div>

            <div class="flex flex-col md:flex-row justify-center items-center gap-4 mb-8">
              <div class="join">
                <button
                  class="btn join-item filter-btn active"
                  data-category="all"
                >Tất Cả</button>
                <?php if (!empty($categories)): ?>
                  <?php foreach ($categories as $category): ?>
                    <button
                      class="btn join-item filter-btn"
                      data-category="<?php echo htmlspecialchars($category['category_name']); ?>"
                    >
                      <?php echo htmlspecialchars($category['category_name']); ?>
                    </button>
                  <?php endforeach; ?>
                <?php endif; ?>
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
              <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                  <div
                    class="card bg-base-100 shadow-xl product-card"
                    data-category="<?php echo htmlspecialchars($product['category_name']); ?>"
                    data-name="<?php echo htmlspecialchars(strtolower($product['product_name'])); ?>"
                  >
                    <figure class="px-2 pt-2">
                      <img
                        src="<?php echo htmlspecialchars($product['image_url'] ?? 'https://placehold.co/400x400?text=Măm+Măm'); ?>"
                        alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                        class="rounded-t-xl h-48 w-full object-cover"
                        onerror="this.onerror=null;this.src='https://placehold.co/400x400?text=Măm+Măm';"
                      />
                    </figure>
                    <div class="card-body items-center text-center p-4">
                      <h2 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h2>
                      <p class="font-semibold text-primary"><?php echo number_format($product['price'], 0, ',', '.'); ?> ₫</p>
                      <div class="card-actions">
                        <button
                          class="btn btn-primary add-to-cart-btn"
                          data-id="<?php echo $product['id']; ?>"
                        >Thêm vào giỏ</button>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-center col-span-full">Chưa có sản phẩm nào để hiển thị.</p>
              <?php endif; ?>
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
              <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                  <li><a><?php echo htmlspecialchars($category['category_name']); ?></a></li>
                <?php endforeach; ?>
              <?php else: ?>
                <li><a>Không có danh mục</a></li>
              <?php endif; ?>
            </ul>
          </details>
        </li>

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