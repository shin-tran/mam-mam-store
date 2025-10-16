<div class="my-4 text-right">
  <button
    class="btn btn-primary"
    onclick="add_product_modal.showModal()"
  >Thêm sản phẩm</button>
</div>

<div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100">
  <table class="table">
    <!-- head -->
    <thead>
      <tr>
        <th>Sản phẩm</th>
        <th>Danh mục</th>
        <th>Giá</th>
        <th>Số lượng</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
          <tr>
            <td>
              <div class="flex items-center gap-3">
                <div class="avatar">
                  <div class="mask mask-squircle h-12 w-12">
                    <img
                      src="<?php echo $product['image_path']
                        ? _HOST_URL_PUBLIC.htmlspecialchars($product['image_path'])
                        : 'https://placehold.co/100x100/e2e8f0/7d8da1?text=N/A'; ?>"
                      alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                      class="cursor-pointer product-image-thumbnail"
                      data-full-src="<?php echo _HOST_URL_PUBLIC.htmlspecialchars($product['image_path'] ?? ''); ?>"
                    />
                  </div>
                </div>
                <div>
                  <div class="font-bold"><?php echo htmlspecialchars($product['product_name']); ?></div>
                </div>
              </div>
            </td>
            <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
            <td><?php echo number_format($product['price'], 0, ',', '.'); ?> ₫</td>
            <td><?php echo $product['stock_quantity']; ?></td>
            <th>
              <button class="btn btn-ghost">Sửa</button>
              <button class="btn btn-ghost text-error">Xóa</button>
            </th>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td
            colspan="5"
            class="text-center"
          >Chưa có sản phẩm nào.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Add Product Modal -->
<dialog
  id="add_product_modal"
  class="modal"
>
  <div class="modal-box">
    <h3 class="font-bold text-2xl mb-4 text-center">Thêm sản phẩm mới</h3>
    <form
      id="add-product-form"
      novalidate
    >
      <div class="flex flex-col gap-4 [&>div]:w-full">
        <div class="form-control flex justify-between">
          <label class="label"><span class="label-text required">Tên sản phẩm</span></label>
          <input
            type="text"
            name="product_name"
            placeholder="Nhập tên sản phẩm"
            class="input input-bordered"
            required
          />
        </div>

        <div class="form-control flex justify-between">
          <label class="label"><span class="label-text required">Giá</span></label>
          <input
            type="number"
            name="price"
            placeholder="0"
            class="input input-bordered"
            required
          />
        </div>

        <div class="form-control flex justify-between">
          <label class="label"><span class="label-text required">Số lượng</span></label>
          <input
            type="number"
            name="stock_quantity"
            placeholder="0"
            class="input input-bordered"
            required
          />
        </div>

        <div class="form-control flex justify-between">
          <label class="label"><span class="label-text required">Danh mục</span></label>
          <select
            name="category_id"
            class="select select-bordered"
            required
          >
            <option
              disabled
              selected
            >Chọn danh mục</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-control flex justify-between">
          <label class="label"><span class="label-text">Mô tả</span></label>
          <textarea
            name="description"
            class="textarea textarea-bordered"
            placeholder="Mô tả chi tiết về sản phẩm"
          ></textarea>
        </div>

        <div class="form-control">
          <label class="label"><span class="label-text required">Hình ảnh</span></label>
          <input
            type="file"
            name="image"
            id="image-input"
            class="file-input file-input-bordered"
            accept="image/png, image/jpeg, image/webp"
          />
          <div
            id="image-preview-container"
            class="mt-4 items-center grid grid-cols-2 sm:grid-cols-4 gap-2"
          ></div>
        </div>

      </div>
      <div class="modal-action">
        <button
          type="button"
          class="btn"
          onclick="add_product_modal.close()"
        >Hủy</button>
        <button
          type="submit"
          class="btn btn-primary"
        >Lưu sản phẩm</button>
      </div>
    </form>
  </div>
</dialog>

<!-- Image Viewer Modal -->
<dialog
  id="image_view_modal"
  class="modal"
>
  <div class="modal-box max-w-3xl p-0 bg-transparent shadow-none">
    <img
      id="modal_image"
      alt="Product Image Full View"
      class="w-full h-auto rounded-lg"
    />
  </div>
  <form
    method="dialog"
    class="modal-backdrop"
  >
    <button>close</button>
  </form>
</dialog>

<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/dashboard/products.js"
></script>
