import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";
const addProductForm = document.getElementById("add-product-form");
const imageInput = document.getElementById("image-input");
const imagePreviewContainer = document.getElementById("image-preview-container");
// --- Image Viewer Modal Logic ---
const imageViewModal = document.getElementById("image_view_modal");
const modalImage = document.getElementById("modal_image");
const imageThumbnails = document.querySelectorAll(".product-image-thumbnail");
// Mở modal xem ảnh cho ảnh sản phẩm trong bảng
imageThumbnails.forEach((thumb) => {
    thumb.addEventListener("click", () => {
        const fullSrc = thumb.dataset.fullSrc;
        if (fullSrc && modalImage && imageViewModal) {
            modalImage.src = fullSrc;
            imageViewModal.showModal();
        }
    });
});
// Mở modal xem ảnh cho các ảnh preview khi thêm sản phẩm mới
imagePreviewContainer?.addEventListener("click", (event) => {
    const target = event.target;
    if (target && target.tagName === "IMG") {
        const imgElement = target;
        if (imgElement.src && modalImage && imageViewModal) {
            modalImage.src = imgElement.src;
            imageViewModal.showModal();
        }
    }
});
// Xử lý xem trước hình ảnh
imageInput?.addEventListener("change", () => {
    if (!imagePreviewContainer)
        return;
    imagePreviewContainer.innerHTML = ""; // Xóa các ảnh preview cũ
    const files = imageInput.files;
    if (files) {
        for (const file of Array.from(files)) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.createElement("img");
                img.src = e.target?.result;
                img.className = "w-full h-full object-cover rounded-md";
                imagePreviewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    }
});
// Xử lý submit form
addProductForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(addProductForm);
    const submitButton = addProductForm.querySelector('button[type="submit"]');
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
            addProductForm.reset();
            if (imagePreviewContainer)
                imagePreviewContainer.innerHTML = "";
            location.reload();
        }
        else {
            toastManager.createToast({
                message: result.message || "Có lỗi xảy ra",
                type: "error",
            });
        }
    }
    catch (error) {
        console.error("Error creating product:", error);
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ!",
            type: "error",
        });
    }
    finally {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = "Lưu sản phẩm";
        }
    }
});
