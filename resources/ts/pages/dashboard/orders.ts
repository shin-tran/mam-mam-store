import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";

const detailsModal = document.getElementById(
  "order_details_modal"
) as HTMLDialogElement;
const cancelModal = document.getElementById(
  "cancel_order_modal"
) as HTMLDialogElement;
const cancelOrderForm = document.getElementById(
  "cancel-order-form"
) as HTMLFormElement;
const ordersTable = document.getElementById("orders-table");
const modalTitle = document.getElementById("modal-order-title");
const modalItemsTbody = document.getElementById("modal-order-items");

// Store current select element for cancellation
let currentSelectElement: HTMLSelectElement | null = null;
let originalValue: string = "";

ordersTable?.addEventListener("click", async (e) => {
  const target = e.target as HTMLElement;

  // View Details Button
  if (target.classList.contains("view-details-btn")) {
    const orderId = target.getAttribute("data-order-id");
    if (orderId) {
      await openDetailsModal(orderId);
    }
  }
});

ordersTable?.addEventListener("change", async (e) => {
  const target = e.target as HTMLSelectElement;

  // Status Select Dropdown
  if (target.classList.contains("status-select")) {
    const orderId = target.getAttribute("data-order-id");
    const newStatus = target.value;
    const currentStatus = target.getAttribute("data-current-status");

    if (orderId) {
      // If changing to cancelled, show modal
      if (newStatus === "cancelled" && currentStatus !== "cancelled") {
        currentSelectElement = target;
        originalValue = currentStatus || "";
        // Reset select to original value temporarily
        target.value = originalValue;

        // Open cancel modal
        const orderIdInput = document.getElementById(
          "cancel-order-id"
        ) as HTMLInputElement;
        if (orderIdInput) orderIdInput.value = orderId;
        cancelOrderForm?.reset();
        cancelModal?.showModal();
      } else {
        await updateOrderStatus(orderId, newStatus, target);
      }
    }
  }
});

async function openDetailsModal(orderId: string) {
  if (!detailsModal || !modalTitle || !modalItemsTbody) return;

  modalTitle.textContent = `Chi tiết đơn hàng #${orderId}`;
  modalItemsTbody.innerHTML =
    '<tr><td colspan="4" class="text-center"><span class="loading loading-spinner"></span></td></tr>';
  detailsModal.showModal();

  try {
    const response = await authService.fetchWithAuth(`/api/orders/${orderId}`);
    const result = await response.json();

    if (result.success && result.data) {
      renderOrderItems(result.data);
    } else {
      modalItemsTbody.innerHTML =
        '<tr><td colspan="4" class="text-center text-error">Không thể tải chi tiết đơn hàng.</td></tr>';
    }
  } catch (error) {
    console.error("Failed to fetch order details:", error);
    modalItemsTbody.innerHTML =
      '<tr><td colspan="4" class="text-center text-error">Lỗi kết nối.</td></tr>';
  }
}

function renderOrderItems(items: any[]) {
  if (!modalItemsTbody) return;

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
          <td>${Number(item.price_at_purchase).toLocaleString("vi-VN")} ₫</td>
          <td>${itemTotal.toLocaleString("vi-VN")} ₫</td>
        </tr>`;
    })
    .join("");

  modalItemsTbody.innerHTML += `
    <tr class="font-bold">
      <td colspan="3" class="text-right">Tổng cộng</td>
      <td>${total.toLocaleString("vi-VN")} ₫</td>
    </tr>`;
}

async function updateOrderStatus(
  orderId: string,
  status: string,
  selectElement: HTMLSelectElement,
  cancellationReason?: string
) {
  const previousValue =
    selectElement.getAttribute("data-current-status") || selectElement.value;
  selectElement.disabled = true;

  try {
    const formData = new FormData();
    formData.append("status", status);

    // Add cancellation info if status is cancelled
    if (status === "cancelled" && cancellationReason) {
      formData.append("cancellation_reason", cancellationReason);
      formData.append("cancelled_by", "admin");
    }

    const response = await authService.fetchWithAuth(
      `/api/orders/update-status/${orderId}`,
      {
        method: "POST",
        body: formData,
      }
    );

    const result = await response.json();

    if (result.success) {
      toastManager.createToast({
        message: "Cập nhật trạng thái thành công!",
        type: "success",
      });
      // Update current status
      selectElement.setAttribute("data-current-status", status);

      // If cancelled, reload to show the info icon
      if (status === "cancelled") {
        setTimeout(() => window.location.reload(), 1000);
      }
    } else {
      toastManager.createToast({
        message: result.message || "Cập nhật thất bại.",
        type: "error",
      });
      // Revert to original value on failure
      selectElement.value = previousValue;
    }
  } catch (error) {
    console.error("Failed to update status:", error);
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ.",
      type: "error",
    });
    selectElement.value = previousValue;
  } finally {
    selectElement.disabled = false;
  }
}

// Handle cancel order form submission
cancelOrderForm?.addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(cancelOrderForm);
  const orderId = formData.get("order_id") as string;
  const reasonSelect = formData.get("cancellation_reason") as string;
  const reasonText = formData.get("cancellation_reason_text") as string;

  // Determine final cancellation reason
  let cancellationReason = "";
  if (reasonSelect === "Khác") {
    if (!reasonText.trim()) {
      toastManager.createToast({
        message: "Vui lòng nhập lý do hủy chi tiết!",
        type: "warning",
      });
      return;
    }
    cancellationReason = reasonText.trim();
  } else if (reasonSelect) {
    cancellationReason = reasonText.trim()
      ? `${reasonSelect} - ${reasonText.trim()}`
      : reasonSelect;
  } else {
    toastManager.createToast({
      message: "Vui lòng chọn lý do hủy đơn hàng!",
      type: "warning",
    });
    return;
  }

  const submitButton = cancelOrderForm.querySelector(
    'button[type="submit"]'
  ) as HTMLButtonElement;
  submitButton.disabled = true;
  submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang xử lý...`;

  try {
    if (currentSelectElement) {
      await updateOrderStatus(
        orderId,
        "cancelled",
        currentSelectElement,
        cancellationReason
      );
      cancelModal?.close();
      currentSelectElement = null;
    }
  } catch (error) {
    console.error("Error cancelling order:", error);
  } finally {
    submitButton.disabled = false;
    submitButton.innerHTML = "Xác nhận hủy";
  }
});
