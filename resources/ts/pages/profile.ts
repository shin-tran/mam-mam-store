import { authService } from "../services/auth-service.js";
import { toastManager } from "../toast-manager.js";
import type { Address, Province, Ward } from "../types/address.js";

const detailsForm = document.getElementById("details-form") as HTMLFormElement;
const avatarForm = document.getElementById("avatar-form") as HTMLFormElement;
const avatarInput = document.getElementById("avatar-input") as HTMLInputElement;
const addressForm = document.getElementById("address-form") as HTMLFormElement;
const passwordForm = document.getElementById(
  "password-form"
) as HTMLFormElement;
const avatarPreview = document.getElementById(
  "avatar-preview"
) as HTMLImageElement;
const cancelOrderForm = document.getElementById(
  "cancel-order-form"
) as HTMLFormElement;
const cancelOrderModal = document.getElementById(
  "cancel_order_modal"
) as HTMLDialogElement;
const orderDetailsModal = document.getElementById(
  "order_details_modal"
) as HTMLDialogElement;
const addressModal = document.getElementById(
  "address_modal"
) as HTMLDialogElement;
const addressesList = document.getElementById(
  "addresses-list"
) as HTMLDivElement;
const provinceSelect = document.getElementById(
  "province-select"
) as HTMLSelectElement;
const wardSelect = document.getElementById("ward-select") as HTMLSelectElement;
const provinceCodeInput = document.getElementById(
  "province-code"
) as HTMLInputElement;

detailsForm?.addEventListener("submit", handleUpdateDetails);
passwordForm?.addEventListener("submit", handleUpdatePassword);
avatarInput?.addEventListener("change", handleAvatarPreview);
avatarForm?.addEventListener("submit", handleAvatarUpload);
cancelOrderForm?.addEventListener("submit", handleCancelOrder);
addressForm?.addEventListener("submit", handleAddressSubmit);
provinceSelect?.addEventListener("change", handleProvinceChange);

document.addEventListener("click", (e) => {
  const target = e.target as HTMLElement;
  if (target.classList.contains("cancel-order-btn")) {
    const orderId = target.getAttribute("data-order-id");
    if (orderId) openCancelModal(orderId);
  }
  if (target.classList.contains("view-order-details-btn")) {
    const orderId = target.getAttribute("data-order-id");
    if (orderId) openOrderDetailsModal(orderId);
  }
  if (target.classList.contains("edit-address-btn")) {
    const addressId = target.getAttribute("data-address-id");
    if (addressId) openEditAddressModal(addressId);
  }
  if (target.classList.contains("delete-address-btn")) {
    const addressId = target.getAttribute("data-address-id");
    if (addressId) handleDeleteAddress(addressId);
  }
  if (target.classList.contains("set-default-address-btn")) {
    const addressId = target.getAttribute("data-address-id");
    if (addressId) handleSetDefaultAddress(addressId);
  }
});

// Load addresses on page load
loadAddresses();

async function handleUpdateDetails(e: SubmitEvent) {
  e.preventDefault();
  const formData = new FormData(detailsForm);
  const submitButton = detailsForm.querySelector(
    'button[type="submit"]'
  ) as HTMLButtonElement;
  submitButton.disabled = true;
  submitButton.innerHTML =
    '<span class="loading loading-spinner"></span> Đang lưu...';
  try {
    const response = await authService.fetchWithAuth(
      "/api/users/update-details",
      { method: "POST", body: formData }
    );
    const result = await response.json();
    if (result.success) {
      toastManager.createToast({ message: result.message, type: "success" });
      const fullName = formData.get("full_name") as string;
      document.querySelectorAll(".navbar .inline-block").forEach((el) => {
        if (el) el.textContent = "Xin chào " + fullName + "!";
      });
    } else {
      toastManager.createToast({
        message: result.message || "Cập nhật thất bại",
        type: "error",
      });
    }
  } catch (error) {
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
    submitButton.disabled = false;
    submitButton.innerHTML = "Lưu thay đổi";
  }
}

async function handleUpdatePassword(e: SubmitEvent) {
  e.preventDefault();
  const formData = new FormData(passwordForm);
  const submitButton = passwordForm.querySelector(
    'button[type="submit"]'
  ) as HTMLButtonElement;
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
    const response = await authService.fetchWithAuth(
      "/api/users/update-password",
      { method: "POST", body: formData }
    );
    const result = await response.json();
    if (result.success) {
      toastManager.createToast({ message: result.message, type: "success" });
      passwordForm.reset();
    } else {
      toastManager.createToast({
        message: result.message || "Cập nhật thất bại",
        type: "error",
      });
    }
  } catch (error) {
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
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
      avatarPreview.src = e.target?.result as string;
    };
    reader.readAsDataURL(file);
    const submitButton = avatarForm.querySelector(
      'button[type="submit"]'
    ) as HTMLButtonElement;
    if (submitButton) submitButton.classList.remove("hidden");
  }
}

async function handleAvatarUpload(e: SubmitEvent) {
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
  const submitButton = avatarForm.querySelector(
    'button[type="submit"]'
  ) as HTMLButtonElement;
  submitButton.disabled = true;
  submitButton.innerHTML =
    '<span class="loading loading-spinner"></span> Đang tải lên...';
  try {
    const response = await authService.fetchWithAuth(
      "/api/users/update-avatar",
      { method: "POST", body: formData }
    );
    const result = await response.json();
    if (result.success) {
      toastManager.createToast({ message: result.message, type: "success" });
      const newAvatarUrl = "/public" + result.data.avatar_path;
      document
        .querySelectorAll<HTMLImageElement>(
          "#header-avatar-image, #dashboard-header-avatar-image, #avatar-preview"
        )
        .forEach((img) => {
          if (img) img.src = newAvatarUrl;
        });
      submitButton.classList.add("hidden");
      avatarInput.value = "";
    } else {
      toastManager.createToast({
        message: result.message || "Tải lên thất bại",
        type: "error",
      });
    }
  } catch (error) {
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
    submitButton.disabled = false;
    submitButton.innerHTML = "Lưu ảnh";
  }
}

function openCancelModal(orderId: string) {
  const orderIdInput = document.getElementById(
    "cancel-order-id"
  ) as HTMLInputElement;
  if (orderIdInput) orderIdInput.value = orderId;
  cancelOrderModal?.showModal();
}

async function handleCancelOrder(e: SubmitEvent) {
  e.preventDefault();
  const formData = new FormData(cancelOrderForm);
  const orderId = formData.get("order_id") as string;
  const cancellationReason = formData.get("cancellation_reason") as string;
  if (!cancellationReason.trim()) {
    toastManager.createToast({
      message: "Vui lòng nhập lý do hủy đơn!",
      type: "warning",
    });
    return;
  }
  const submitButton = cancelOrderForm.querySelector(
    'button[type="submit"]'
  ) as HTMLButtonElement;
  submitButton.disabled = true;
  submitButton.innerHTML =
    '<span class="loading loading-spinner"></span> Đang xử lý...';
  try {
    const response = await authService.fetchWithAuth(
      `/api/orders/${orderId}/cancel`,
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cancellation_reason: cancellationReason }),
      }
    );
    const result = await response.json();
    if (result.success) {
      toastManager.createToast({
        message: "Hủy đơn hàng thành công!",
        type: "success",
      });
      cancelOrderModal?.close();
      cancelOrderForm.reset();
      window.location.reload()
    } else {
      toastManager.createToast({
        message: result.message || "Không thể hủy đơn hàng",
        type: "error",
      });
    }
  } catch (error) {
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
    submitButton.disabled = false;
    submitButton.innerHTML = "Xác nhận hủy";
  }
}

async function openOrderDetailsModal(orderId: string) {
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
    } else {
      // modalOrderInfo.innerHTML = '<div class="col-span-2 text-center text-error">Không thể tải thông tin đơn hàng.</div>';
      modalOrderItems.innerHTML =
        '<tr><td colspan="4" class="text-center text-error">Không thể tải chi tiết đơn hàng.</td></tr>';
    }
  } catch (error) {
    console.error("Failed to fetch order details:", error);
    // modalOrderInfo.innerHTML = '<div class="col-span-2 text-center text-error">Lỗi kết nối.</div>';
    modalOrderItems.innerHTML =
      '<tr><td colspan="4" class="text-center text-error">Lỗi kết nối.</td></tr>';
  }
}

function renderOrderDetails(
  items: any[],
  infoContainer: HTMLElement,
  itemsContainer: HTMLElement
) {
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

let provinces: Province[] = [];
let wards: Ward[] = [];

async function loadProvinces() {
  if (!provinceSelect) return;

  try {
    const response = await fetch(
      "https://tinhthanhpho.com/api/v1/new-provinces?limit=36"
    );
    const result = await response.json();

    if (result.success && result.data) {
      provinces = result.data;
      provinceSelect.innerHTML =
        '<option value="">-- Chọn Tỉnh/Thành phố --</option>';
      provinces.forEach((province) => {
        const option = document.createElement("option");
        option.value = `${province.type} ${province.name}`;
        option.setAttribute("data-code", province.code);
        option.textContent = province.name;
        provinceSelect.appendChild(option);
      });
    }
  } catch (error) {
    console.error("Failed to load provinces:", error);
    toastManager.createToast({
      message: "Không thể tải danh sách tỉnh/thành phố",
      type: "error",
    });
  }
}

async function handleProvinceChange() {
  if (!provinceSelect || !wardSelect || !provinceCodeInput) return;

  const selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
  if (!selectedOption) return;

  const provinceCode = selectedOption.getAttribute("data-code");

  if (!provinceCode) {
    wardSelect.disabled = true;
    wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
    provinceCodeInput.value = "";
    return;
  }

  provinceCodeInput.value = provinceCode;
  wardSelect.disabled = true;
  wardSelect.innerHTML = '<option value="">Đang tải...</option>';

  try {
    const response = await fetch(
      `https://tinhthanhpho.com/api/v1/new-provinces/${provinceCode}/wards?limit=170`
    );
    const result = await response.json();

    if (result.success && result.data) {
      wards = result.data;
      wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
      wards.forEach((ward) => {
        const option = document.createElement("option");
        option.value = `${ward.type} ${ward.name}`;
        option.textContent = ward.name;
        wardSelect.appendChild(option);
      });
      wardSelect.disabled = false;
    }
  } catch (error) {
    console.error("Failed to load wards:", error);
    wardSelect.innerHTML = '<option value="">Lỗi khi tải phường/xã</option>';
    toastManager.createToast({
      message: "Không thể tải danh sách phường/xã",
      type: "error",
    });
  }
}

async function loadAddresses() {
  if (!addressesList) return;

  addressesList.innerHTML = `
    <div class="text-center py-8">
      <span class="loading loading-spinner loading-lg"></span>
    </div>
  `;

  try {
    const response = await authService.fetchWithAuth("/api/users/addresses");
    const result = await response.json();

    if (result.success && result.data) {
      renderAddresses(result.data);
    } else {
      addressesList.innerHTML = `
        <div class="text-center py-8 text-gray-500">
          Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ mới!
        </div>
      `;
    }
  } catch (error) {
    console.error("Failed to load addresses:", error);
    addressesList.innerHTML = `
      <div class="text-center py-8 text-error">
        Lỗi khi tải danh sách địa chỉ
      </div>
    `;
  }
}

function renderAddresses(addresses: Address[]) {
  if (!addressesList) return;

  if (addresses.length === 0) {
    addressesList.innerHTML = `
      <div class="text-center py-8 text-gray-500">
        Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ mới!
      </div>
    `;
    return;
  }

  addressesList.innerHTML = addresses
    .map((address) => {
      const fullAddress = `${address.street_address}, ${address.ward}, ${address.city}`;
      const isDefault = address.is_default === 1;

      return `
        <div class="card bg-base-200 ${
          isDefault ? "border-2 border-primary" : ""
        }">
          <div class="card-body px-3!">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <h4 class="font-semibold">${address.recipient_name}</h4>
                  ${
                    isDefault
                      ? '<span class="badge badge-primary badge-sm h-fit">Mặc định</span>'
                      : ""
                  }
                </div>
                <p class="text-sm text-gray-600">${address.phone_number}</p>
                <p class="text-sm mt-1">${fullAddress}</p>
              </div>
              <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </label>
                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                  <li>
                    <button class="edit-address-btn" data-address-id="${
                      address.id
                    }">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Chỉnh sửa
                    </button>
                  </li>
                  ${
                    !isDefault
                      ? `
                  <li>
                    <button class="set-default-address-btn" data-address-id="${address.id}">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Đặt làm mặc định
                    </button>
                  </li>
                  <li>
                    <button class="delete-address-btn text-error" data-address-id="${address.id}">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Xóa
                    </button>
                  </li>
                  `
                      : ""
                  }
                </ul>
              </div>
            </div>
          </div>
        </div>
      `;
    })
    .join("");
}

function resetAddressForm() {
  if (!addressForm) return;
  addressForm.reset();
  const addressId = document.getElementById("address-id") as HTMLInputElement;
  const modalTitle = document.getElementById("address-modal-title");
  if (addressId) addressId.value = "";
  if (modalTitle) modalTitle.textContent = "Thêm địa chỉ mới";

  // Load provinces and reset wards
  loadProvinces();
  if (wardSelect) {
    wardSelect.disabled = true;
    wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
  }
}

async function openEditAddressModal(addressId: string) {
  if (!addressModal || !addressForm) return;

  try {
    const response = await authService.fetchWithAuth(
      `/api/users/addresses/${addressId}`
    );
    const result = await response.json();

    if (result.success && result.data) {
      const address = result.data as Address;
      const modalTitle = document.getElementById("address-modal-title");
      const addressIdInput = document.getElementById(
        "address-id"
      ) as HTMLInputElement;

      if (modalTitle) modalTitle.textContent = "Chỉnh sửa địa chỉ";
      if (addressIdInput) addressIdInput.value = addressId;

      // Load provinces first
      await loadProvinces();

      (
        addressForm.elements.namedItem("recipient_name") as HTMLInputElement
      ).value = address.recipient_name;
      (
        addressForm.elements.namedItem("phone_number") as HTMLInputElement
      ).value = address.phone_number;
      (
        addressForm.elements.namedItem("street_address") as HTMLInputElement
      ).value = address.street_address;

      // Set city/province
      if (provinceSelect) {
        // Find and select the matching province
        const options = Array.from(provinceSelect.options);
        const matchingOption = options.find(
          (opt) => opt.value === address.city
        );
        if (matchingOption) {
          provinceSelect.value = address.city;
          const provinceCode = matchingOption.getAttribute("data-code");
          if (provinceCode && provinceCodeInput) {
            provinceCodeInput.value = provinceCode;

            // Load wards for this province
            await handleProvinceChange();

            // Then set the ward value
            if (wardSelect) {
              wardSelect.value = address.ward;
            }
          }
        }
      }

      (
        addressForm.elements.namedItem("is_default") as HTMLInputElement
      ).checked = address.is_default === 1;

      addressModal.showModal();
    } else {
      toastManager.createToast({
        message: "Không thể tải thông tin địa chỉ",
        type: "error",
      });
    }
  } catch (error) {
    console.error("Failed to load address:", error);
    toastManager.createToast({
      message: "Lỗi khi tải thông tin địa chỉ",
      type: "error",
    });
  }
}

async function handleAddressSubmit(e: SubmitEvent) {
  e.preventDefault();
  if (!addressForm) return;

  const formData = new FormData(addressForm);
  const addressId = (document.getElementById("address-id") as HTMLInputElement)
    ?.value;
  const submitButton = addressForm.querySelector(
    'button[type="submit"]'
  ) as HTMLButtonElement;

  submitButton.disabled = true;
  submitButton.innerHTML =
    '<span class="loading loading-spinner"></span> Đang lưu...';

  try {
    const url = addressId
      ? `/api/users/addresses/update/${addressId}`
      : "/api/users/addresses/create";

    const response = await authService.fetchWithAuth(url, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      toastManager.createToast({
        message: addressId
          ? "Cập nhật địa chỉ thành công!"
          : "Thêm địa chỉ thành công!",
        type: "success",
      });
      addressModal?.close();
      resetAddressForm();
      loadAddresses();
    } else {
      toastManager.createToast({
        message: result.message || "Lưu địa chỉ thất bại",
        type: "error",
      });
    }
  } catch (error) {
    console.error("Failed to save address:", error);
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
    submitButton.disabled = false;
    submitButton.innerHTML = "Lưu địa chỉ";
  }
}

async function handleDeleteAddress(addressId: string) {
  if (!confirm("Bạn có chắc chắn muốn xóa địa chỉ này?")) return;

  try {
    const response = await authService.fetchWithAuth(
      `/api/users/addresses/delete/${addressId}`,
      { method: "POST" }
    );

    const result = await response.json();

    if (result.success) {
      toastManager.createToast({
        message: "Xóa địa chỉ thành công!",
        type: "success",
      });
      loadAddresses();
    } else {
      toastManager.createToast({
        message: result.message || "Xóa địa chỉ thất bại",
        type: "error",
      });
    }
  } catch (error) {
    console.error("Failed to delete address:", error);
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  }
}

async function handleSetDefaultAddress(addressId: string) {
  try {
    const response = await authService.fetchWithAuth(
      `/api/users/addresses/set-default/${addressId}`,
      { method: "POST" }
    );

    const result = await response.json();

    if (result.success) {
      toastManager.createToast({
        message: "Đặt địa chỉ mặc định thành công!",
        type: "success",
      });
      loadAddresses();
    } else {
      toastManager.createToast({
        message: result.message || "Đặt địa chỉ mặc định thất bại",
        type: "error",
      });
    }
  } catch (error) {
    console.error("Failed to set default address:", error);
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  }
}

// Make resetAddressForm globally accessible for the onclick handler
(window as any).resetAddressForm = resetAddressForm;
