import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";

// === GENERAL ELEMENTS ===
const productsTable = document
  .getElementById("products-table")
  ?.querySelector("tbody");

// === ADD MODAL ELEMENTS ===
const addProductModal = document.getElementById(
  "add_product_modal"
) as HTMLDialogElement;
const addProductForm = document.getElementById(
  "add-product-form"
) as HTMLFormElement;
const addImageInput = document.getElementById(
  "image-input"
) as HTMLInputElement;
const addImagePreviewContainer = document.getElementById(
  "image-preview-container"
);

// === EDIT MODAL ELEMENTS ===
const editProductModal = document.getElementById(
  "edit_product_modal"
) as HTMLDialogElement;
const editProductForm = document.getElementById(
  "edit-product-form"
) as HTMLFormElement;
const editProductIdInput = document.getElementById(
  "edit-product-id"
) as HTMLInputElement;
const editProductNameInput = document.getElementById(
  "edit-product-name"
) as HTMLInputElement;
const editProductPriceInput = document.getElementById(
  "edit-product-price"
) as HTMLInputElement;
const editProductStockInput = document.getElementById(
  "edit-product-stock"
) as HTMLInputElement;
const editProductCategorySelect = document.getElementById(
  "edit-product-category"
) as HTMLSelectElement;
const editProductDescriptionTextarea = document.getElementById(
  "edit-product-description"
) as HTMLTextAreaElement;
const editImageInput = document.getElementById(
  "edit-image-input"
) as HTMLInputElement;
const editImagePreviewContainer = document.getElementById(
  "edit-image-preview-container"
);

// === DELETE MODAL ELEMENTS ===
const deleteProductModal = document.getElementById(
  "delete_product_modal"
) as HTMLDialogElement;
const productNameToDeleteSpan = document.getElementById(
  "product-name-to-delete"
);
const confirmDeleteBtn = document.getElementById("confirm-delete-product-btn");
let productIdToDelete: string | null = null;

// === IMAGE VIEWER MODAL ===
const imageViewModal = document.getElementById(
  "image_view_modal"
) as HTMLDialogElement;
const modalImage = document.getElementById("modal_image") as HTMLImageElement;

function showFullImage(src: string) {
  if (src && modalImage && imageViewModal) {
    modalImage.src = src;
    imageViewModal.showModal();
  }
}

// === EVENT LISTENERS ===
document.addEventListener("click", (event) => {
  const target = event.target as HTMLElement;

  // Image thumbnails
  const thumbnail = target.closest(".product-image-thumbnail");
  if (thumbnail) {
    showFullImage((thumbnail as HTMLImageElement).dataset.fullSrc || "");
    return;
  }

  // Preview images in modals
  const previewImg = target.closest(
    "#image-preview-container img, #edit-image-preview-container img"
  );
  if (previewImg) {
    showFullImage((previewImg as HTMLImageElement).src);
    return;
  }

  // Edit button
  const editButton = target.closest(".btn-edit");
  if (editButton && productsTable) {
    handleEditClick(editButton);
    return;
  }

  // Delete button
  const deleteButton = target.closest(".btn-delete");
  if (deleteButton) {
    handleDeleteClick(deleteButton);
    return;
  }
});

addImageInput?.addEventListener("change", () =>
  handleImagePreview(addImageInput, addImagePreviewContainer)
);
editImageInput?.addEventListener("change", () =>
  handleImagePreview(editImageInput, editImagePreviewContainer)
);
addProductForm?.addEventListener("submit", handleAddSubmit);
editProductForm?.addEventListener("submit", handleEditSubmit);
confirmDeleteBtn?.addEventListener("click", handleConfirmDelete);

// === FUNCTION IMPLEMENTATIONS ===

function handleImagePreview(
  input: HTMLInputElement,
  previewContainer: HTMLElement | null
) {
  if (!previewContainer) return;
  previewContainer.innerHTML = "";

  const files = input.files;
  if (files) {
    for (const file of Array.from(files)) {
      const reader = new FileReader();
      reader.onload = (e) => {
        const img = document.createElement("img");
        img.src = e.target?.result as string;
        img.className = "w-24 h-24 object-cover rounded-md cursor-pointer";
        previewContainer.appendChild(img);
      };
      reader.readAsDataURL(file);
    }
  }
}

async function handleAddSubmit(e: SubmitEvent) {
  e.preventDefault();
  const formData = new FormData(addProductForm);
  const submitButton = addProductForm.querySelector<HTMLButtonElement>(
    'button[type="submit"]'
  );

  if (submitButton) {
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang lưu...`;
  }

  try {
    const response = await authService.fetchWithAuth("/api/products/create", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      toastManager.createToast({ message: result.message, type: "success" });
      addProductModal.close();
      addProductForm.reset();
      if (addImagePreviewContainer) addImagePreviewContainer.innerHTML = "";
      window.location.reload();
    } else {
      toastManager.createToast({
        message: result.message || "Có lỗi xảy ra",
        type: "error",
      });
    }
  } catch (error) {
    console.error("Error creating product:", error);
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.innerHTML = "Lưu sản phẩm";
    }
  }
}

function handleEditClick(button: Element) {
  const id = button.getAttribute("data-product-id");
  if (!id) return;

  // Populate form
  editProductIdInput.value = id;
  editProductNameInput.value = button.getAttribute("data-product-name") || "";
  editProductPriceInput.value = button.getAttribute("data-product-price") || "";
  editProductStockInput.value = button.getAttribute("data-product-stock") || "";
  editProductCategorySelect.value =
    button.getAttribute("data-product-category") || "";
  editProductDescriptionTextarea.value =
    button.getAttribute("data-product-description") || "";

  // Show current image preview
  if (editImagePreviewContainer) {
    editImagePreviewContainer.innerHTML = "";
    const currentImageUrl = button.getAttribute("data-product-image");
    if (currentImageUrl) {
      const img = document.createElement("img");
      img.src = `/public${currentImageUrl}`;
      img.className = "w-24 h-24 object-cover rounded-md cursor-pointer";
      editImagePreviewContainer.appendChild(img);
    }
  }

  editProductModal.showModal();
}

async function handleEditSubmit(e: SubmitEvent) {
  e.preventDefault();
  const productId = editProductIdInput.value;
  if (!productId) return;

  const formData = new FormData(editProductForm);
  const submitButton = editProductForm.querySelector<HTMLButtonElement>(
    'button[type="submit"]'
  );

  if (submitButton) {
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang cập nhật...`;
  }

  try {
    const response = await authService.fetchWithAuth(
      `/api/products/update/${productId}`,
      {
        method: "POST",
        body: formData,
      }
    );
    const result = await response.json();

    if (result.success) {
      toastManager.createToast({ message: result.message, type: "success" });
      editProductModal.close();
      setTimeout(() => window.location.reload(), 1500);
    } else {
      toastManager.createToast({
        message: result.message || "Cập nhật thất bại.",
        type: "error",
      });
    }
  } catch (error) {
    console.error("Error updating product:", error);
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.innerHTML = "Lưu thay đổi";
    }
  }
}

function handleDeleteClick(button: Element) {
  productIdToDelete = button.getAttribute("data-product-id");
  const productName = button.getAttribute("data-product-name");

  if (productNameToDeleteSpan) {
    productNameToDeleteSpan.textContent = productName;
  }
  deleteProductModal.showModal();
}

async function handleConfirmDelete() {
  if (!productIdToDelete) return;

  confirmDeleteBtn?.setAttribute("disabled", "true");
  if (confirmDeleteBtn)
    confirmDeleteBtn.innerHTML = `<span class="loading loading-spinner"></span> Đang xóa...`;

  try {
    const response = await authService.fetchWithAuth(
      `/api/products/delete/${productIdToDelete}`,
      {
        method: "POST",
      }
    );
    const result = await response.json();

    if (result.success) {
      toastManager.createToast({ message: result.message, type: "success" });
      const rowToDelete = productsTable?.querySelector(
        `tr[data-product-id="${productIdToDelete}"]`
      );
      rowToDelete?.remove();
    } else {
      toastManager.createToast({ message: result.message, type: "error" });
    }
  } catch (error) {
    console.error("Error deleting product:", error);
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
    if (confirmDeleteBtn) {
      confirmDeleteBtn.removeAttribute("disabled");
      confirmDeleteBtn.innerHTML = "Xóa";
    }
    deleteProductModal.close();
    productIdToDelete = null;
  }
}
