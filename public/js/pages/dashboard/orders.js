import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";
const ordersTable = document.getElementById("orders-table");
const detailsModal = document.getElementById("order_details_modal");
const modalTitle = document.getElementById("modal-order-title");
const modalItemsTbody = document.getElementById("modal-order-items");
ordersTable?.addEventListener("click", async (e) => {
    const target = e.target;
    // View Details Button
    if (target.classList.contains("view-details-btn")) {
        const orderId = target.getAttribute("data-order-id");
        if (orderId) {
            await openDetailsModal(orderId);
        }
    }
});
ordersTable?.addEventListener("change", async (e) => {
    const target = e.target;
    // Status Select Dropdown
    if (target.classList.contains("status-select")) {
        const orderId = target.getAttribute("data-order-id");
        const newStatus = target.value;
        if (orderId) {
            await updateOrderStatus(orderId, newStatus, target);
        }
    }
});
async function openDetailsModal(orderId) {
    if (!detailsModal || !modalTitle || !modalItemsTbody)
        return;
    modalTitle.textContent = `Chi tiết đơn hàng #${orderId}`;
    modalItemsTbody.innerHTML =
        '<tr><td colspan="4" class="text-center"><span class="loading loading-spinner"></span></td></tr>';
    detailsModal.showModal();
    try {
        const response = await authService.fetchWithAuth(`/api/orders/${orderId}`);
        const result = await response.json();
        if (result.success && result.data) {
            renderOrderItems(result.data);
        }
        else {
            modalItemsTbody.innerHTML =
                '<tr><td colspan="4" class="text-center text-error">Không thể tải chi tiết đơn hàng.</td></tr>';
        }
    }
    catch (error) {
        console.error("Failed to fetch order details:", error);
        modalItemsTbody.innerHTML =
            '<tr><td colspan="4" class="text-center text-error">Lỗi kết nối.</td></tr>';
    }
}
function renderOrderItems(items) {
    if (!modalItemsTbody)
        return;
    if (items.length === 0) {
        modalItemsTbody.innerHTML =
            '<tr><td colspan="4" class="text-center">Đơn hàng này không có sản phẩm.</td></tr>';
        return;
    }
    let total = 0;
    modalItemsTbody.innerHTML = items
        .map((item) => {
        const itemTotal = item.quantity * item.price_at_purchase;
        total += itemTotal;
        return `
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="avatar">
                            <div class="mask mask-squircle w-12 h-12">
                                <img src="/public/${item.image_path}" alt="${item.product_name}" />
                            </div>
                        </div>
                        <div>
                            <div class="font-bold">${item.product_name}</div>
                        </div>
                    </div>
                </td>
                <td>${item.quantity}</td>
                <td>${Number(item.price_at_purchase).toLocaleString("vi-VN")} ₫</td>
                <td>${itemTotal.toLocaleString("vi-VN")} ₫</td>
            </tr>
        `;
    })
        .join("");
    modalItemsTbody.innerHTML += `
        <tr class="font-bold">
            <td colspan="3" class="text-right">Tổng cộng</td>
            <td>${total.toLocaleString("vi-VN")} ₫</td>
        </tr>
    `;
}
async function updateOrderStatus(orderId, status, selectElement) {
    const originalValue = Array.from(selectElement.options).find((opt) => opt.selected)?.value;
    selectElement.disabled = true;
    try {
        const formData = new FormData();
        formData.append("status", status);
        const response = await authService.fetchWithAuth(`/api/orders/update-status/${orderId}`, {
            method: "POST",
            body: formData,
        });
        const result = await response.json();
        if (result.success) {
            toastManager.createToast({
                message: "Cập nhật trạng thái thành công!",
                type: "success",
            });
        }
        else {
            toastManager.createToast({
                message: result.message || "Cập nhật thất bại.",
                type: "error",
            });
            // Revert to original value on failure
            if (originalValue)
                selectElement.value = originalValue;
        }
    }
    catch (error) {
        console.error("Failed to update status:", error);
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ.",
            type: "error",
        });
        if (originalValue)
            selectElement.value = originalValue;
    }
    finally {
        selectElement.disabled = false;
    }
}
