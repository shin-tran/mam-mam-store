<div class="card bg-base-100 shadow-xl">
  <div class="card-body">
    <div class="flex justify-between items-center mb-4">
      <h2 class="card-title">Quản lý danh mục</h2>
      <button
        class="btn btn-primary"
        id="add-category-btn"
      >Thêm danh mục</button>
    </div>
    <div class="overflow-x-auto">
      <table
        class="table"
        id="categories-table"
      >
        <thead>
          <tr>
            <th>ID</th>
            <th>Tên danh mục</th>
            <th class="w-32"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
              <tr data-category-id="<?php echo $category['id']; ?>">
                <td class="font-semibold"><?php echo $category['id']; ?></td>
                <td class="category-name"><?php echo htmlspecialchars($category['category_name']); ?></td>
                <td class="text-right flex">
                  <button
                    class="btn btn-ghost btn-edit-category"
                    data-category-id="<?php echo $category['id']; ?>"
                    data-category-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                  >Sửa</button>
                  <button
                    class="btn btn-ghost text-error btn-delete-category"
                    data-category-id="<?php echo $category['id']; ?>"
                    data-category-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                  >Xóa</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td
                colspan="3"
                class="text-center"
              >Chưa có danh mục nào.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add/Edit Category Modal -->
<dialog
  id="category_modal"
  class="modal"
>
  <div class="modal-box">
    <h3
      class="font-bold text-lg"
      id="category-modal-title"
    >Thêm danh mục mới</h3>
    <form id="category-form">
      <input
        type="hidden"
        id="category-id-input"
        value=""
      >
      <div class="form-control py-4">
        <label
          class="label"
          for="category-name-input"
        >
          <span class="label-text required">Tên danh mục</span>
        </label>
        <input
          type="text"
          id="category-name-input"
          name="category_name"
          class="input input-bordered w-full"
          required
        />
      </div>
      <div class="modal-action">
        <button
          type="button"
          class="btn"
          onclick="category_modal.close()"
        >Hủy</button>
        <button
          type="submit"
          class="btn btn-primary"
        >Lưu</button>
      </div>
    </form>
  </div>
</dialog>

<!-- Delete Category Modal -->
<dialog
  id="delete_category_modal"
  class="modal"
>
  <div class="modal-box">
    <h3 class="font-bold text-lg text-error">Xác nhận xóa danh mục!</h3>
    <p class="py-4">Bạn có chắc chắn muốn xóa danh mục "<strong id="category-name-to-delete"></strong>"? Hành động này không thể hoàn tác.</p>
    <div class="modal-action">
      <form
        method="dialog"
        class="w-full flex justify-end gap-2"
      >
        <button class="btn">Hủy</button>
        <button
          id="confirm-delete-category-btn"
          class="btn btn-error"
        >Xóa</button>
      </form>
    </div>
  </div>
</dialog>

<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/dashboard/categories.js"
></script>
