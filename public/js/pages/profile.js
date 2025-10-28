import { authService } from "../services/auth-service.js";
import { toastManager } from "../toast-manager.js";
const detailsForm = document.getElementById("details-form");
const avatarForm = document.getElementById("avatar-form");
const avatarInput = document.getElementById("avatar-input");
const passwordForm = document.getElementById("password-form");
const avatarPreview = document.getElementById("avatar-preview");
const cancelOrderForm = document.getElementById("cancel-order-form");
const cancelOrderModal = document.getElementById("cancel_order_modal");
const orderDetailsModal = document.getElementById("order_details_modal");
detailsForm?.addEventListener("submit", handleUpdateDetails);
passwordForm?.addEventListener("submit", handleUpdatePassword);
avatarInput?.addEventListener("change", handleAvatarPreview);
avatarForm?.addEventListener("submit", handleAvatarUpload);
cancelOrderForm?.addEventListener("submit", handleCancelOrder);
document.addEventListener("click", (e) => {
    const target = e.target;
    if (target.classList.contains("cancel-order-btn")) {
        const orderId = target.getAttribute("data-order-id");
        if (orderId)
            openCancelModal(orderId);
    }
    if (target.classList.contains("view-order-details-btn")) {
        const orderId = target.getAttribute("data-order-id");
        if (orderId)
            openOrderDetailsModal(orderId);
    }
});
async function handleUpdateDetails(e) {
    e.preventDefault();
    const formData = new FormData(detailsForm);
    const submitButton = detailsForm.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML =
        '<span class="loading loading-spinner"></span> Đang lưu...';
    try {
        const response = await authService.fetchWithAuth("/api/users/update-details", { method: "POST", body: formData });
        const result = await response.json();
        if (result.success) {
            toastManager.createToast({ message: result.message, type: "success" });
            const fullName = formData.get("full_name");
            document.querySelectorAll(".navbar .inline-block").forEach((el) => {
                if (el)
                    el.textContent = "Xin chào " + fullName + "!";
            });
        }
        else {
            toastManager.createToast({
                message: result.message || "Cập nhật thất bại",
                type: "error",
            });
        }
    }
    catch (error) {
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ!",
            type: "error",
        });
    }
    finally {
        submitButton.disabled = false;
        submitButton.innerHTML = "Lưu thay đổi";
    }
}
async function handleUpdatePassword(e) {
    e.preventDefault();
    const formData = new FormData(passwordForm);
    const submitButton = passwordForm.querySelector('button[type="submit"]');
    if (formData.get("new_password") !== formData.get("confirm_password")) {
        toastManager.createToast({
            message: "Mật khẩu mới không khớp!",
            type: "error",
        });
        return;
    }
    submitButton.disabled = true;
    submitButton.innerHTML =
        '<span class="loading loading-spinner"></span> Đang lưu...';
    try {
        const response = await authService.fetchWithAuth("/api/users/update-password", { method: "POST", body: formData });
        const result = await response.json();
        if (result.success) {
            toastManager.createToast({ message: result.message, type: "success" });
            passwordForm.reset();
        }
        else {
            toastManager.createToast({
                message: result.message || "Cập nhật thất bại",
                type: "error",
            });
        }
    }
    catch (error) {
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ!",
            type: "error",
        });
    }
    finally {
        submitButton.disabled = false;
        submitButton.innerHTML = "Đổi mật khẩu";
    }
}
function handleAvatarPreview() {
    const file = avatarInput.files?.[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            // set src cho avatarPreview để có thể hiển thị ảnh
            avatarPreview.src = e.target?.result;
        };
        reader.readAsDataURL(file);
        const submitButton = avatarForm.querySelector('button[type="submit"]');
        if (submitButton)
            submitButton.classList.remove("hidden");
    }
}
async function handleAvatarUpload(e) {
    e.preventDefault();
    const file = avatarInput.files?.[0];
    if (!file) {
        toastManager.createToast({
            message: "Vui lòng chọn một ảnh!",
            type: "warning",
        });
        return;
    }
    const formData = new FormData();
    formData.append("avatar", file);
    const submitButton = avatarForm.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML =
        '<span class="loading loading-spinner"></span> Đang tải lên...';
    try {
        const response = await authService.fetchWithAuth("/api/users/update-avatar", { method: "POST", body: formData });
        const result = await response.json();
        if (result.success) {
            toastManager.createToast({ message: result.message, type: "success" });
            const newAvatarUrl = "/public" + result.data.avatar_path;
            document
                .querySelectorAll("#header-avatar-image, #dashboard-header-avatar-image, #avatar-preview")
                .forEach((img) => {
                if (img)
                    img.src = newAvatarUrl;
            });
            submitButton.classList.add("hidden");
            avatarInput.value = "";
        }
        else {
            toastManager.createToast({
                message: result.message || "Tải lên thất bại",
                type: "error",
            });
        }
    }
    catch (error) {
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ!",
            type: "error",
        });
    }
    finally {
        submitButton.disabled = false;
        submitButton.innerHTML = "Lưu ảnh";
    }
}
function openCancelModal(orderId) {
    const orderIdInput = document.getElementById("cancel-order-id");
    if (orderIdInput)
        orderIdInput.value = orderId;
    cancelOrderModal?.showModal();
}
async function handleCancelOrder(e) {
    e.preventDefault();
    const formData = new FormData(cancelOrderForm);
    const orderId = formData.get("order_id");
    const cancellationReason = formData.get("cancellation_reason");
    if (!cancellationReason.trim()) {
        toastManager.createToast({
            message: "Vui lòng nhập lý do hủy đơn!",
            type: "warning",
        });
        return;
    }
    const submitButton = cancelOrderForm.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML =
        '<span class="loading loading-spinner"></span> Đang xử lý...';
    try {
        const response = await authService.fetchWithAuth(`/api/orders/${orderId}/cancel`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ cancellation_reason: cancellationReason }),
        });
        const result = await response.json();
        if (result.success) {
            toastManager.createToast({
                message: "Hủy đơn hàng thành công!",
                type: "success",
            });
            cancelOrderModal?.close();
            cancelOrderForm.reset();
            setTimeout(() => window.location.reload(), 1000);
        }
        else {
            toastManager.createToast({
                message: result.message || "Không thể hủy đơn hàng",
                type: "error",
            });
        }
    }
    catch (error) {
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ!",
            type: "error",
        });
    }
    finally {
        submitButton.disabled = false;
        submitButton.innerHTML = "Xác nhận hủy";
    }
}
async function openOrderDetailsModal(orderId) {
    const modalTitle = document.getElementById("modal-order-title");
    const modalOrderInfo = document.getElementById("modal-order-info");
    const modalOrderItems = document.getElementById("modal-order-items");
    if (!orderDetailsModal || !modalTitle || !modalOrderInfo || !modalOrderItems)
        return;
    modalTitle.textContent = "Chi tiết đơn hàng #" + orderId;
    // modalOrderInfo.innerHTML = '<div class="col-span-2 text-center"><span class="loading loading-spinner"></span></div>';
    modalOrderItems.innerHTML =
        '<tr><td colspan="4" class="text-center"><span class="loading loading-spinner"></span></td></tr>';
    orderDetailsModal.showModal();
    try {
        const response = await authService.fetchWithAuth("/api/orders/" + orderId);
        const result = await response.json();
        if (result.success && result.data) {
            renderOrderDetails(result.data, modalOrderInfo, modalOrderItems);
        }
        else {
            // modalOrderInfo.innerHTML = '<div class="col-span-2 text-center text-error">Không thể tải thông tin đơn hàng.</div>';
            modalOrderItems.innerHTML =
                '<tr><td colspan="4" class="text-center text-error">Không thể tải chi tiết đơn hàng.</td></tr>';
        }
    }
    catch (error) {
        console.error("Failed to fetch order details:", error);
        // modalOrderInfo.innerHTML = '<div class="col-span-2 text-center text-error">Lỗi kết nối.</div>';
        modalOrderItems.innerHTML =
            '<tr><td colspan="4" class="text-center text-error">Lỗi kết nối.</td></tr>';
    }
}
function renderOrderDetails(items, infoContainer, itemsContainer) {
    if (items.length === 0) {
        itemsContainer.innerHTML =
            '<tr><td colspan="4" class="text-center">Đơn hàng này không có sản phẩm.</td></tr>';
        return;
    }
    let total = 0;
    itemsContainer.innerHTML = items
        .map((item) => {
        const itemTotal = item.quantity * item.price_at_purchase;
        total += itemTotal;
        return `
        <tr>
          <td>
            <div class="flex items-center gap-3">
              <div class="avatar">
                <div class="mask mask-squircle w-12 h-12">
                  <img
                    src="/public/${item.image_path}"
                    alt="${item.product_name}" />
                </div>
              </div>
              <div>
                <div class="font-bold">${item.product_name}</div>
              </div>
            </div>
          </td>
          <td>${item.quantity}</td>
          <td>${Number(item.price_at_purchase).toLocaleString("vi-VN")}</td>
          <td>${itemTotal.toLocaleString("vi-VN")}</td>
        </tr>`;
    })
        .join("");
    itemsContainer.innerHTML += `
  <tr class="font-bold">
    <td colspan="3" class="text-right">Tổng cộng</td>
    <td>${total.toLocaleString("vi-VN")}</td>
  </tr>`;
}
