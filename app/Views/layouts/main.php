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
          <!-- <div class="hidden flex-none lg:flex">
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
          </div> -->

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
                      <span class="badge badge-sm indicator-item">0</span>
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
                      src="<?php
                      $split = explode(" ", $_SESSION['user']['full_name']);
                      echo $_SESSION['user']['avatar_path']
                        ? _HOST_URL_PUBLIC.$_SESSION['user']['avatar_path']
                        : "https://placehold.co/40x40?text={$split[count($split) - 1]}";
                      ?>"
                    />
                  </div>
                </div>
                <ul
                  tabindex="0"
                  class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-37 p-2 shadow [&_li>*]:text-sm"
                >
                  <li>
                    <a
                      href="<?php echo _HOST_URL ?>/profile"
                      class="justify-between"
                    >
                      Hồ sơ của bạn
                    </a>
                  </li>
                  </li>
                  <?php if (!Helpers::userHasRole('customer')): ?>
                    <li>
                      <a
                        class="justify-between"
                        href="<?php echo _HOST_URL ?>/dashboard"
                      >
                        Dashboard
                      </a>
                    </li>
                  <?php endif; ?>
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
      <?php
      echo $content;
      ?>
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
</body>

</html>
