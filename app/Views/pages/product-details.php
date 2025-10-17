<?php use App\Helpers\Helpers; ?>
<!-- Page content -->
<main class="mx-auto w-full max-w-7xl px-4 py-8">
  <div class="grid md:grid-cols-2 gap-8">
    <!-- Product Images -->
    <div>
      <?php
      $image = $product['image_path'] ? _HOST_URL_PUBLIC.$product['image_path'] : 'https://placehold.co/600x600?text=Măm+Măm';
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

  <!-- Reviews Section -->
  <div class="divider mt-16 mb-8"></div>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="md:col-span-2">
      <h2 class="text-2xl font-bold mb-6">Đánh giá sản phẩm</h2>
      <div
        id="reviews-list"
        class="space-y-6"
      >
        <?php if (!empty($reviews)): ?>
          <?php foreach ($reviews as $review): ?>
            <div class="flex gap-4">
              <div class="avatar">
                <div class="w-12 h-12 rounded-full">
                  <img src="<?php echo $review['avatar_path'] ? _HOST_URL_PUBLIC.$review['avatar_path'] : 'https://placehold.co/48x48?text='.mb_substr($review['full_name'], 0, 1); ?>" />
                </div>
              </div>
              <div>
                <div class="font-bold"><?php echo htmlspecialchars($review['full_name']); ?></div>
                <div class="rating rating-sm">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input
                      type="radio"
                      class="mask mask-star-2 bg-orange-400"
                      <?php echo $i === $review['rating'] ? 'checked' : ''; ?>
                      disabled
                    />
                  <?php endfor; ?>
                </div>
                <p class="mt-2 text-base-content/80"><?php echo htmlspecialchars($review['comment']); ?></p>
                <div class="text-xs text-base-content/60 mt-1"><?php echo date('d/m/Y H:i', strtotime($review['review_date'])); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p id="no-reviews-text">Chưa có đánh giá nào cho sản phẩm này.</p>
        <?php endif; ?>
      </div>
    </div>
    <div class="md:col-span-1">
      <?php if (Helpers::isLoggedIn()): ?>
        <div class="card bg-base-100 shadow-lg sticky top-8">
          <div class="card-body">
            <h3 class="card-title">Viết đánh giá của bạn</h3>
            <form id="review-form">
              <div class="form-control">
                <label class="label"><span class="label-text">Xếp hạng</span></label>
                <div class="rating rating-lg">
                  <input
                    type="radio"
                    name="rating"
                    value="1"
                    class="mask mask-star-2 bg-orange-400"
                  />
                  <input
                    type="radio"
                    name="rating"
                    value="2"
                    class="mask mask-star-2 bg-orange-400"
                  />
                  <input
                    type="radio"
                    name="rating"
                    value="3"
                    class="mask mask-star-2 bg-orange-400"
                  />
                  <input
                    type="radio"
                    name="rating"
                    value="4"
                    class="mask mask-star-2 bg-orange-400"
                  />
                  <input
                    type="radio"
                    name="rating"
                    value="5"
                    class="mask mask-star-2 bg-orange-400"
                    checked
                  />
                </div>
              </div>
              <div class="form-control mt-4">
                <label class="label"><span class="label-text">Bình luận</span></label>
                <textarea
                  id="comment-textarea"
                  name="comment"
                  class="textarea textarea-bordered h-24"
                  placeholder="Sản phẩm này tuyệt vời..."
                  required
                ></textarea>
              </div>
              <div class="card-actions mt-4">
                <button
                  type="submit"
                  class="btn btn-primary btn-block"
                >Gửi đánh giá</button>
              </div>
            </form>
          </div>
        </div>
      <?php else: ?>
        <div class="alert">
          <span>Vui lòng <a
              href="/login"
              class="link"
            >đăng nhập</a> để viết đánh giá.</span>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>
<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/product-details.js"
></script>
