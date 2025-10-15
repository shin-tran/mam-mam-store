<!-- Page content -->
<main class="mx-auto w-full max-w-7xl px-4 py-8">
  <div class="grid md:grid-cols-2 gap-8">
    <!-- Product Images -->
    <div>
      <?php
      $image = $product['image_path'] ?: 'https://placehold.co/600x600?text=Măm+Măm';
      ?>
      <img
        src="<?php echo htmlspecialchars($image); ?>"
        alt="<?php echo htmlspecialchars($product['product_name']); ?>"
        class="w-full rounded-lg shadow-lg object-cover aspect-square"
      >
    </div>

    <!-- Product Details -->
    <div>
      <h1 class="text-4xl font-bold mb-2"><?php echo htmlspecialchars($product['product_name']); ?></h1>
      <p class="text-3xl text-primary font-semibold mb-4"><?php echo number_format($product['price'], 0, ',', '.'); ?> ₫</p>

      <div class="prose max-w-none mb-6">
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
      </div>

      <div class="flex items-center gap-4 mb-6">
        <label
          for="quantity"
          class="font-semibold"
        >Số lượng:</label>
        <div class="join">
          <button class="btn join-item btn-decrease">-</button>
          <input
            type="number"
            id="quantity"
            name="quantity"
            value="1"
            min="1"
            max="<?php echo $product['stock_quantity']; ?>"
            class="input input-bordered join-item w-20 text-center"
          />
          <button class="btn join-item btn-increase">+</button>
        </div>
        <span class="text-sm text-gray-500">
          (Còn <?php echo $product['stock_quantity']; ?> sản phẩm)
        </span>
      </div>

      <div class="flex gap-2">
        <button
          class="btn btn-primary btn-lg add-to-cart-btn"
          data-id="<?php echo $product['id']; ?>"
          data-stock="<?php echo $product['stock_quantity']; ?>"
        >
          Thêm vào giỏ hàng
        </button>
        <button
          class="btn btn-primary btn-lg buy-now-btn"
          data-id="<?php echo $product['id']; ?>"
          data-stock="<?php echo $product['stock_quantity']; ?>"
        >
          Mua ngay
        </button>
      </div>
    </div>
  </div>
</main>
<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/product-details.js"
></script>