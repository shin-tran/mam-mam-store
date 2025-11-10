import { authService } from "../services/auth-service.js";
import { toastManager } from "../toast-manager.js";
import { Helpers } from "../utils/helpers.js";

// DOM Elements
const cartItemsContainer = document.getElementById("cart-items-container");
const subtotalEl = document.getElementById("subtotal");
const shippingFeeEl = document.getElementById("shipping-fee");
const totalPriceEl = document.getElementById("total-price");
const checkoutBtn = document.querySelector(".btn-block");

// Checkout Modal Elements
const checkoutModal = document.getElementById(
  "checkout_modal"
) as HTMLDialogElement;
const checkoutForm = document.getElementById(
  "checkout-form"
) as HTMLFormElement;
const successModal = document.getElementById(
  "success_modal"
) as HTMLDialogElement;
const continueShoppingBtn = successModal?.querySelector("button");

// Address Elements
const addressesContainer = document.getElementById("addresses-container");
const newAddressForm = document.getElementById("new-address-form");
const addNewAddressBtn = document.getElementById("add-new-address-btn");
const saveNewAddressBtn = document.getElementById(
  "save-new-address-btn"
) as HTMLButtonElement;
const cancelNewAddressBtn = document.getElementById("cancel-new-address-btn");

// New address inputs
const newRecipientNameInput = document.getElementById(
  "new-recipient-name"
) as HTMLInputElement;
const newPhoneNumberInput = document.getElementById(
  "new-phone-number"
) as HTMLInputElement;
const newStreetAddressInput = document.getElementById(
  "new-street-address"
) as HTMLInputElement;
const newProvinceSelect = document.getElementById(
  "new-province-select"
) as HTMLSelectElement;
const newWardSelect = document.getElementById(
  "new-ward-select"
) as HTMLSelectElement;
const newProvinceCodeInput = document.getElementById(
  "new-province-code"
) as HTMLInputElement;
const newIsDefaultCheckbox = document.getElementById(
  "new-is-default"
) as HTMLInputElement;

// State
let fullProductInfo: any[] = []; // To store product details fetched from API
let userAddresses: any[] = []; // To store user's saved addresses
let selectedAddressId: number | null = null; // Currently selected address
let provinces: any[] = []; // To store provinces data
let wards: any[] = []; // To store wards data

/**
 * Main function to initialize the cart page
 */
async function initializeCart() {
  const cart = JSON.parse(localStorage.getItem("cart") || "[]");

  if (cart.length === 0) {
    displayEmptyCart();
    return;
  }

  try {
    const productIds = cart.map((item: any) => item.productId);
    const response = await authService.fetchWithAuth("/api/products/cart", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ productIds }),
    });

    const result = await response.json();

    if (result.success) {
      // Combine API data with localStorage quantities
      fullProductInfo = result.data
        .map((product: any) => {
          const cartItem = cart.find(
            (item: any) => item.productId === String(product.id)
          );
          return { ...product, quantity: cartItem ? cartItem.quantity : 0 };
        })
        .filter((p: any) => p.quantity > 0);

      renderCart();
      fetchUserAddresses();
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    console.error("Failed to fetch cart products:", error);
    displayError("Không thể tải giỏ hàng. Vui lòng thử lại.");
  }
}

/**
 * Renders the entire cart based on the `fullProductInfo` state
 */
function renderCart() {
  if (!cartItemsContainer) return;

  if (fullProductInfo.length === 0) {
    displayEmptyCart();
    return;
  }

  cartItemsContainer.innerHTML = fullProductInfo
    .map(
      (product) => `
        <div class="card card-side bg-base-100 shadow-md" data-product-id="${
          product.id
        }">
            <figure class="w-24 md:w-32 flex-shrink-0">
                <img src="/public/${product.image_path}" alt="${
        product.product_name
      }" class="w-full h-full object-cover"/>
            </figure>
            <div class="card-body p-4">
                <h2 class="card-title text-base md:text-lg">${
                  product.product_name
                }</h2>
                <p class="text-primary font-semibold">${Number(
                  product.price
                ).toLocaleString("vi-VN")} ₫</p>
                <div class="card-actions items-center justify-between mt-2">
                    <div class="join">
                        <button class="btn btn-sm join-item btn-decrease">-</button>
                        <input type="number" value="${
                          product.quantity
                        }" min="1" max="${
        product.stock_quantity
      }" class="input input-sm join-item w-12 text-center quantity-input" />
                        <button class="btn btn-sm join-item btn-increase">+</button>
                    </div>
                    <button class="btn btn-ghost btn-sm text-error btn-remove">Xóa</button>
                </div>
            </div>
        </div>
    `
    )
    .join("");

  updateTotals();
}

/**
 * Handles clicks on quantity buttons and remove button
 */
function handleCartActions(event: Event) {
  const target = event.target as HTMLElement;
  const card = target.closest(".card") as HTMLDivElement;
  if (!card) return;

  const productId = card.dataset.productId;
  if (!productId) return;

  if (target.classList.contains("btn-increase")) {
    updateQuantity(productId, 1);
  } else if (target.classList.contains("btn-decrease")) {
    updateQuantity(productId, -1);
  } else if (target.matches(".quantity-input")) {
    const input = target as HTMLInputElement;
    input.addEventListener("change", () => {
      const newQuantity = parseInt(input.value, 10);
      updateQuantity(productId, 0, newQuantity); // 0 means set to newQuantity
    });
  } else if (target.classList.contains("btn-remove")) {
    removeItem(productId);
    Helpers.updateCartBadge();
  }
}

/**
 * Updates the quantity of a product in the cart
 */
function updateQuantity(
  productId: string,
  change: number,
  absoluteValue?: number
) {
  const productIndex = fullProductInfo.findIndex((p) => p.id == productId);
  if (productIndex === -1) return;

  const product = fullProductInfo[productIndex];
  let newQuantity =
    absoluteValue !== undefined ? absoluteValue : product.quantity + change;

  if (newQuantity < 1) newQuantity = 1;
  if (newQuantity > product.stock_quantity) {
    toastManager.createToast({
      message: `Chỉ còn ${product.stock_quantity} sản phẩm trong kho`,
      type: "error",
    });
    newQuantity = product.stock_quantity;
  }

  product.quantity = newQuantity;

  // Update localStorage
  const cart = JSON.parse(localStorage.getItem("cart") || "[]");
  const cartItemIndex = cart.findIndex(
    (item: any) => item.productId === productId
  );
  if (cartItemIndex > -1) {
    cart[cartItemIndex].quantity = newQuantity;
    localStorage.setItem("cart", JSON.stringify(cart));
  }

  // Re-render the specific item's quantity input and totals
  const input = cartItemsContainer?.querySelector(
    `[data-product-id="${productId}"] .quantity-input`
  ) as HTMLInputElement;
  if (input) input.value = newQuantity.toString();
  updateTotals();
}

/**
 * Removes an item from the cart
 */
function removeItem(productId: string) {
  // Remove from state
  fullProductInfo = fullProductInfo.filter((p) => p.id != productId);

  // Remove from localStorage
  let cart = JSON.parse(localStorage.getItem("cart") || "[]");
  cart = cart.filter((item: any) => item.productId !== productId);
  localStorage.setItem("cart", JSON.stringify(cart));

  toastManager.createToast({
    message: "Đã xóa sản phẩm khỏi giỏ hàng",
    type: "success",
  });

  renderCart(); // Re-render the whole cart
}

/**
 * Calculates and updates the subtotal, shipping, and total price
 */
function updateTotals() {
  const subtotal = fullProductInfo.reduce(
    (sum, p) => sum + p.price * p.quantity,
    0
  );
  const shipping = 0; // Fixed for now

  if (subtotalEl)
    subtotalEl.textContent = `${subtotal.toLocaleString("vi-VN")} ₫`;
  if (shippingFeeEl)
    shippingFeeEl.textContent = `${shipping.toLocaleString("vi-VN")} ₫`;
  if (totalPriceEl)
    totalPriceEl.textContent = `${(subtotal + shipping).toLocaleString(
      "vi-VN"
    )} ₫`;

  // Disable checkout if cart is empty
  if (checkoutBtn)
    (checkoutBtn as HTMLButtonElement).disabled = fullProductInfo.length === 0;
}

function displayEmptyCart() {
  if (cartItemsContainer) {
    cartItemsContainer.innerHTML = `
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body items-center text-center">
                    <p class="text-lg">Giỏ hàng của bạn đang trống.</p>
                    <div class="card-actions mt-4">
                        <a href="/" class="btn btn-primary">Tiếp tục mua sắm</a>
                    </div>
                </div>
            </div>`;
  }
  updateTotals();
}

function displayError(message: string) {
  if (cartItemsContainer) {
    cartItemsContainer.innerHTML = `<div class="alert alert-error">${message}</div>`;
  }
}

/**
 * Fetch user's saved addresses from API
 */
async function fetchUserAddresses() {
  try {
    const response = await authService.fetchWithAuth("/api/users/addresses");
    if (!response.ok) {
      renderAddressesError();
      return;
    }

    const result = await response.json();
    if (result.success && result.data) {
      userAddresses = result.data;
      renderAddresses();

      // Auto-select default address
      const defaultAddress = userAddresses.find((addr) => addr.is_default === 1);
      if (defaultAddress) {
        selectedAddressId = defaultAddress.id;
      } else if (userAddresses.length > 0) {
        selectedAddressId = userAddresses[0].id;
      }
    } else {
      renderAddressesEmpty();
    }
  } catch (error) {
    console.error("Failed to fetch addresses:", error);
    renderAddressesError();
  }
}

/**
 * Render user addresses as radio buttons
 */
function renderAddresses() {
  if (!addressesContainer) return;

  if (userAddresses.length === 0) {
    renderAddressesEmpty();
    return;
  }

  addressesContainer.innerHTML = userAddresses
    .map(
      (address) => `
      <div class="form-control">
        <label class="label cursor-pointer justify-start gap-3 border rounded-lg p-4 hover:bg-base-200 transition-colors ${
          address.is_default === 1 ? 'border-primary bg-primary/5' : 'border-base-300'
        }">
          <input
            type="radio"
            name="selected-address"
            value="${address.id}"
            class="radio radio-primary"
            ${address.is_default === 1 ? 'checked' : ''}
          />
          <div class="flex-1">
            <div class="font-semibold">
              ${address.recipient_name}
              ${address.is_default === 1 ? '<span class="badge badge-primary badge-sm ml-2">Mặc định</span>' : ''}
            </div>
            <div class="text-sm opacity-70">${address.phone_number}</div>
            <div class="text-sm opacity-70">${address.street_address}, ${address.ward}, ${address.city}</div>
          </div>
        </label>
      </div>
    `
    )
    .join("");

  // Add event listeners to radio buttons
  const radioButtons = addressesContainer.querySelectorAll(
    'input[name="selected-address"]'
  );
  radioButtons.forEach((radio) => {
    radio.addEventListener("change", (e) => {
      selectedAddressId = parseInt((e.target as HTMLInputElement).value);
    });
  });
}

/**
 * Render empty state for addresses
 */
function renderAddressesEmpty() {
  if (!addressesContainer) return;
  addressesContainer.innerHTML = `
    <div class="text-center py-4 text-sm opacity-70">
      Bạn chưa có địa chỉ nào. Vui lòng thêm địa chỉ mới.
    </div>
  `;
}

/**
 * Render error state for addresses
 */
function renderAddressesError() {
  if (!addressesContainer) return;
  addressesContainer.innerHTML = `
    <div class="alert alert-error">
      <span>Không thể tải danh sách địa chỉ. Vui lòng thử lại.</span>
    </div>
  `;
}

/**
 * Show new address form
 */
function showNewAddressForm() {
  if (newAddressForm) {
    newAddressForm.classList.remove("hidden");
  }
  loadProvinces();
}

/**
 * Load provinces from API
 */
async function loadProvinces() {
  if (!newProvinceSelect) return;

  try {
    const response = await fetch(
      "https://tinhthanhpho.com/api/v1/new-provinces?limit=36"
    );
    const result = await response.json();

    if (result.success && result.data) {
      provinces = result.data;
      newProvinceSelect.innerHTML =
        '<option value="">-- Chọn Tỉnh/Thành phố --</option>';
      provinces.forEach((province: any) => {
        const option = document.createElement("option");
        option.value = `${province.type} ${province.name}`;
        option.setAttribute("data-code", province.code);
        option.textContent = province.name;
        newProvinceSelect.appendChild(option);
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

/**
 * Handle province change to load wards
 */
async function handleProvinceChange() {
  if (!newProvinceSelect || !newWardSelect || !newProvinceCodeInput) return;

  const selectedOption =
    newProvinceSelect.options[newProvinceSelect.selectedIndex];
  if (!selectedOption) return;

  const provinceCode = selectedOption.getAttribute("data-code");

  if (!provinceCode) {
    newWardSelect.disabled = true;
    newWardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
    newProvinceCodeInput.value = "";
    return;
  }

  newProvinceCodeInput.value = provinceCode;
  newWardSelect.disabled = true;
  newWardSelect.innerHTML = '<option value="">Đang tải...</option>';

  try {
    const response = await fetch(
      `https://tinhthanhpho.com/api/v1/new-provinces/${provinceCode}/wards?limit=170`
    );
    const result = await response.json();

    if (result.success && result.data) {
      wards = result.data;
      newWardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
      wards.forEach((ward: any) => {
        const option = document.createElement("option");
        option.value = `${ward.type} ${ward.name}`;
        option.textContent = ward.name;
        newWardSelect.appendChild(option);
      });
      newWardSelect.disabled = false;
    }
  } catch (error) {
    console.error("Failed to load wards:", error);
    newWardSelect.innerHTML = '<option value="">Lỗi khi tải phường/xã</option>';
    toastManager.createToast({
      message: "Không thể tải danh sách phường/xã",
      type: "error",
    });
  }
}

/**
 * Hide new address form
 */
function hideNewAddressForm() {
  if (newAddressForm) {
    newAddressForm.classList.add("hidden");
    clearNewAddressForm();
  }
}

/**
 * Clear new address form inputs
 */
function clearNewAddressForm() {
  if (newRecipientNameInput) newRecipientNameInput.value = "";
  if (newPhoneNumberInput) newPhoneNumberInput.value = "";
  if (newStreetAddressInput) newStreetAddressInput.value = "";
  if (newProvinceSelect) newProvinceSelect.selectedIndex = 0;
  if (newWardSelect) {
    newWardSelect.selectedIndex = 0;
    newWardSelect.disabled = true;
  }
  if (newProvinceCodeInput) newProvinceCodeInput.value = "";
  if (newIsDefaultCheckbox) newIsDefaultCheckbox.checked = false;
}

/**
 * Save new address
 */
async function saveNewAddress() {
  const recipientName = newRecipientNameInput?.value.trim();
  const phoneNumber = newPhoneNumberInput?.value.trim();
  const streetAddress = newStreetAddressInput?.value.trim();
  const ward = newWardSelect?.value.trim();
  const city = newProvinceSelect?.value.trim();
  const isDefault = newIsDefaultCheckbox?.checked ? 1 : 0;

  // Validation
  if (!recipientName || !phoneNumber || !streetAddress || !ward || !city) {
    toastManager.createToast({
      message: "Vui lòng điền đầy đủ thông tin địa chỉ",
      type: "error",
    });
    return;
  }

  try {
    if (saveNewAddressBtn) {
      saveNewAddressBtn.disabled = true;
      saveNewAddressBtn.innerHTML = `<span class="loading loading-spinner loading-sm"></span> Đang lưu...`;
    }

    const response = await authService.fetchWithAuth(
      "/api/users/addresses/create",
      {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          recipient_name: recipientName,
          phone_number: phoneNumber,
          street_address: streetAddress,
          ward: ward,
          city: city,
          is_default: isDefault.toString(),
        }),
      }
    );

    const result = await response.json();

    if (result.success) {
      toastManager.createToast({
        message: "Thêm địa chỉ thành công",
        type: "success",
      });
      hideNewAddressForm();
      await fetchUserAddresses();
    } else {
      toastManager.createToast({
        message: result.message || "Thêm địa chỉ thất bại",
        type: "error",
      });
    }
  } catch (error) {
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ",
      type: "error",
    });
  } finally {
    if (saveNewAddressBtn) {
      saveNewAddressBtn.disabled = false;
      saveNewAddressBtn.innerHTML = `Lưu địa chỉ`;
    }
  }
}

async function fetchAndFillUserInfo() {
  try {
    const response = await authService.fetchWithAuth("/api/profile/info");
    if (!response.ok) return;

    const result = await response.json();
    if (result.success && result.data) {
      const user = result.data;
      // Pre-fill new address form with user info
      if (newRecipientNameInput && !newRecipientNameInput.value) {
        newRecipientNameInput.value = user.full_name || "";
      }
      if (newPhoneNumberInput && !newPhoneNumberInput.value) {
        newPhoneNumberInput.value = user.phone_number || "";
      }
    }
  } catch (e) {
    console.warn("Could not pre-fill user info for checkout.");
  }
}

async function handleCheckout(event: SubmitEvent) {
  event.preventDefault();

  // Validate selected address
  if (!selectedAddressId) {
    toastManager.createToast({
      message: "Vui lòng chọn địa chỉ giao hàng",
      type: "error",
    });
    return;
  }

  const submitButton = (event.target as HTMLFormElement).querySelector(
    'button[type="submit"]'
  ) as HTMLButtonElement;
  submitButton.disabled = true;
  submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang xử lý...`;

  // Get selected address details
  const selectedAddress = userAddresses.find(
    (addr) => addr.id === selectedAddressId
  );

  if (!selectedAddress) {
    toastManager.createToast({
      message: "Địa chỉ không hợp lệ",
      type: "error",
    });
    submitButton.disabled = false;
    submitButton.innerHTML = `Xác nhận đặt hàng`;
    return;
  }

  const shippingInfo = {
    name: selectedAddress.recipient_name,
    phone: selectedAddress.phone_number,
    address: `${selectedAddress.street_address}, ${selectedAddress.ward}, ${selectedAddress.city}`,
    note: "", // Can add a note field later
  };

  const cartItems = fullProductInfo.map((p) => ({
    productId: p.id,
    quantity: p.quantity,
  }));

  try {
    const response = await authService.fetchWithAuth("/api/orders/create", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ cartItems, shippingInfo }),
    });

    const result = await response.json();

    if (result.success) {
      localStorage.removeItem("cart");
      checkoutModal.close();
      successModal.showModal();
    } else {
      toastManager.createToast({
        message: result.message || "Đặt hàng thất bại.",
        type: "error",
      });
    }
  } catch (error) {
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ.",
      type: "error",
    });
  } finally {
    submitButton.disabled = false;
    submitButton.innerHTML = `Xác nhận đặt hàng`;
  }
}

// --- Event Listeners ---
document.addEventListener("DOMContentLoaded", initializeCart);
cartItemsContainer?.addEventListener("click", handleCartActions);
checkoutForm?.addEventListener("submit", handleCheckout);
continueShoppingBtn?.addEventListener("click", () => Helpers.redirect("/"));

// Address form event listeners
addNewAddressBtn?.addEventListener("click", () => {
  showNewAddressForm();
  fetchAndFillUserInfo();
});
saveNewAddressBtn?.addEventListener("click", saveNewAddress);
cancelNewAddressBtn?.addEventListener("click", hideNewAddressForm);
newProvinceSelect?.addEventListener("change", handleProvinceChange);

// Also close the modal if clicked outside
checkoutModal.addEventListener("click", (event) => {
  if (event.target === checkoutModal) {
    checkoutModal.close();
  }
});
