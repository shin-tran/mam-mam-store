import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";

// --- CATEGORY ELEMENTS ---
const categoriesTable = document.getElementById("categories-table");
const addCategoryBtn = document.getElementById("add-category-btn");
const categoryModal = document.getElementById(
  "category_modal"
) as HTMLDialogElement;
const categoryForm = document.getElementById(
  "category-form"
) as HTMLFormElement;
const categoryModalTitle = document.getElementById("category-modal-title");
const categoryIdInput = document.getElementById(
  "category-id-input"
) as HTMLInputElement;
const categoryNameInput = document.getElementById(
  "category-name-input"
) as HTMLInputElement;
const deleteCategoryModal = document.getElementById(
  "delete_category_modal"
) as HTMLDialogElement;
const categoryNameToDeleteSpan = document.getElementById(
  "category-name-to-delete"
);
const confirmDeleteCategoryBtn = document.getElementById(
  "confirm-delete-category-btn"
);
let categoryIdToDelete: string | null = null;

// =================================================================================
// CATEGORY MANAGEMENT
// =================================================================================

// Mở modal Thêm mới
addCategoryBtn?.addEventListener("click", () => {
  if (!categoryModal || !categoryModalTitle || !categoryForm) return;
  categoryModalTitle.textContent = "Thêm danh mục mới";
  categoryForm.reset();
  categoryIdInput.value = "";
  categoryModal.showModal();
});

// Mở modal Sửa hoặc Xóa
categoriesTable?.addEventListener("click", (event) => {
  const target = event.target as HTMLElement;
  const editButton = target.closest(".btn-edit-category");
  const deleteButton = target.closest(".btn-delete-category");

  if (editButton) {
    if (!categoryModal || !categoryModalTitle || !categoryForm) return;
    const id = editButton.getAttribute("data-category-id");
    const name = editButton.getAttribute("data-category-name");

    categoryModalTitle.textContent = "Sửa danh mục";
    categoryIdInput.value = id || "";
    categoryNameInput.value = name || "";
    categoryModal.showModal();
  }

  if (deleteButton) {
    // deleteCategoryModal phải được load xong
    if (!deleteCategoryModal || !categoryNameToDeleteSpan) return;
    categoryIdToDelete = deleteButton.getAttribute("data-category-id");
    const name = deleteButton.getAttribute("data-category-name");

    categoryNameToDeleteSpan.textContent = name;
    deleteCategoryModal.showModal();
  }
});

// Xử lý submit form Thêm/Sửa danh mục
categoryForm?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const id = categoryIdInput.value;
  const name = categoryNameInput.value;
  const isEditing = !!id;

  const url = isEditing
    ? `/api/categories/update/${id}`
    : "/api/categories/create";
  const formData = new FormData();
  formData.append("category_name", name);

  const submitButton = categoryForm.querySelector<HTMLButtonElement>(
    'button[type="submit"]'
  );
  if (submitButton) {
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang lưu...`;
  }

  try {
    const response = await authService.fetchWithAuth(url, {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      toastManager.createToast({ message: result.message, type: "success" });
      // Tải lại trang để cập nhật danh sách
      setTimeout(() => window.location.reload(), 1500);
    } else {
      toastManager.createToast({ message: result.message, type: "error" });
    }
  } catch (error) {
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ.",
      type: "error",
    });
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.innerHTML = `Lưu`;
    }
    categoryModal.close();
  }
});

// Xử lý xác nhận xóa danh mục
confirmDeleteCategoryBtn?.addEventListener("click", async (event) => {
  event.preventDefault();
  if (!categoryIdToDelete) return;

  const button = event.target as HTMLButtonElement;
  button.disabled = true;
  button.innerHTML = `<span class="loading loading-spinner"></span> Đang xóa...`;

  try {
    const response = await authService.fetchWithAuth(
      `/api/categories/delete/${categoryIdToDelete}`,
      {
        method: "POST",
      }
    );
    const result = await response.json();

    if (result.success) {
      toastManager.createToast({ message: result.message, type: "success" });
      const rowToDelete = categoriesTable?.querySelector(
        `tr[data-category-id="${categoryIdToDelete}"]`
      );
      rowToDelete?.remove();
    } else {
      toastManager.createToast({ message: result.message, type: "error" });
    }
  } catch (error) {
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
    button.disabled = false;
    button.innerHTML = "Xóa";
    deleteCategoryModal.close();
    categoryIdToDelete = null;
  }
});
