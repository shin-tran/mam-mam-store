<!DOCTYPE html>
<html lang="en">

<?php
use App\Core\View;
View::layout('head', ['title' => $title]);
?>

<body>
  <div class="drawer lg:drawer-open">
    <input
      id="mobile-drawer"
      type="checkbox"
      class="drawer-toggle"
    />

    <div class="drawer-content flex flex-col">
      <!-- Menu bar on mobile -->
      <div class="w-full navbar bg-base-100 shadow-sm lg:hidden">
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
          href="/doan-phpcoban"
          class="text-xl flex items-center gap-2 font-semibold"
        >
          <img
            src="<?php echo _HOST_URL_PUBLIC ?>/icons/popcorn.svg"
            alt="Popcorn icon"
          >Ăn Vặt Shop</a>
      </div>

      <!-- Header -->
      <div class="shadow-sm bg-base-100 z-10 sticky top-0">
        <header class="navbar mx-auto justify-end">
          <div>
            <!-- User -->
            <h3 class="inline-block">Xin chào Admin!</h3>
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
                    href="<?php echo _HOST_URL_DASHBOARD ?>"
                  >
                    Dashboard
                  </a>
                </li>
                <li><a>Cài đặt</a></li>
                <li id="btn-logout"><a>Đăng xuất</a></li>
            </div>
          </div>
        </header>
      </div>

      <main class="flex-1 lg:m-3">
        <?php
        echo $content;
        ?>
      </main>
    </div>

    <!-- Sidebar -->
    <div class="drawer-side shadow-sm">
      <label
        for="mobile-drawer"
        aria-label="close sidebar"
        class="drawer-overlay"
      ></label>
      <ul class="menu p-4 pt-0 min-h-full bg-base-100 text-base-content">
        <!-- Sidebar content here -->
        <div class="px-2 py-3 mt-1">
          <a
            href="/doan-phpcoban"
            class="text-xl flex items-center gap-2 font-semibold"
          >
            <img
              src="<?php echo _HOST_URL_PUBLIC ?>/icons/popcorn.svg"
              alt="Popcorn icon"
            >Ăn Vặt Shop</a>
        </div>
        <li class="menu-title">Tổng quan</li>
        <li>
          <a href="<?php echo _HOST_URL_DASHBOARD ?>">
            <img
              src="<?php echo _HOST_URL_PUBLIC ?>/icons/house.svg"
              alt="House icon"
              class="size-5"
            >
            Dashboard
          </a>
        </li>

        <li class="menu-title">Quản lý</li>
        <li>
          <a href="<?php echo _HOST_URL_DASHBOARD ?>/orders">
            <img
              src="<?php echo _HOST_URL_PUBLIC ?>/icons/clipboard-list.svg"
              alt="Clipboard list icon"
              class="size-5"
            >
            Đơn hàng
          </a>
        </li>
        <li>
          <a href="<?php echo _HOST_URL_DASHBOARD ?>/products">
            <img
              src="<?php echo _HOST_URL_PUBLIC ?>/icons/package.svg"
              alt="Package icon"
              class="size-5"
            >
            Sản phẩm
          </a>
        </li>
        <li>
          <a href="<?php echo _HOST_URL_DASHBOARD ?>/users">
            <img
              src="<?php echo _HOST_URL_PUBLIC ?>/icons/users.svg"
              alt="Users icon"
              class="size-5"
            >
            Khách hàng
          </a>
        </li>
      </ul>
    </div>
  </div>
  <script
    type="module"
    src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/dashboard/index.js"
  ></script>
</body>

</html>