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
        <div class="join hidden lg:block">
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
                <a href="<?php echo _HOST_URL."/product/{$product['id']}"; ?>">
                  <img
                    src="<?php echo _HOST_URL_PUBLIC.htmlspecialchars($product['image_path'] ?? 'https://placehold.co/400x400?text=Măm+Măm'); ?>"
                    alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                    class="rounded-t-xl h-48 w-full object-cover"
                    onerror="this.onerror=null;this.src='https://placehold.co/400x400?text=Măm+Măm';"
                  />
                </a>
              </figure>
              <div class="card-body items-center text-center p-4">
                <h2 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h2>
                <p class="font-semibold text-primary"><?php echo number_format($product['price'], 0, ',', '.'); ?> ₫</p>
                <div class="card-actions join">
                  <button
                    class="btn btn-primary btn-sm join-item add-to-cart-btn"
                    data-id="<?php echo $product['id']; ?>"
                    data-stock="<?php echo $product['stock_quantity']; ?>"
                  >Thêm vào giỏ</button>
                  <button
                    class="btn btn-secondary btn-sm join-item buy-now-btn"
                    data-id="<?php echo $product['id']; ?>"
                    data-stock="<?php echo $product['stock_quantity']; ?>"
                  >Mua ngay</button>
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
<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/home.js"
></script>
