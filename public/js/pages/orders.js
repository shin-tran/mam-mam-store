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
const checkoutModal = document.getElementById("checkout_modal");
const checkoutForm = document.getElementById("checkout-form");
const successModal = document.getElementById("success_modal");
const continueShoppingBtn = successModal?.querySelector("button");
// User Info inputs in modal
const nameInput = checkoutForm.querySelector('input[type="text"]');
const phoneInput = checkoutForm.querySelector('input[type="tel"]');
const addressTextarea = checkoutForm.querySelector("textarea");
// State
let fullProductInfo = []; // To store product details fetched from API
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
        const productIds = cart.map((item) => item.productId);
        const response = await authService.fetchWithAuth("/api/products/cart", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ productIds }),
        });
        const result = await response.json();
        if (result.success) {
            // Combine API data with localStorage quantities
            fullProductInfo = result.data
                .map((product) => {
                const cartItem = cart.find((item) => item.productId === String(product.id));
                return { ...product, quantity: cartItem ? cartItem.quantity : 0 };
            })
                .filter((p) => p.quantity > 0);
            renderCart();
            fetchAndFillUserInfo();
        }
        else {
            throw new Error(result.message);
        }
    }
    catch (error) {
        console.error("Failed to fetch cart products:", error);
        displayError("Không thể tải giỏ hàng. Vui lòng thử lại.");
    }
}
/**
 * Renders the entire cart based on the `fullProductInfo` state
 */
function renderCart() {
    if (!cartItemsContainer)
        return;
    if (fullProductInfo.length === 0) {
        displayEmptyCart();
        return;
    }
    cartItemsContainer.innerHTML = fullProductInfo
        .map((product) => `
        <div class="card card-side bg-base-100 shadow-md" data-product-id="${product.id}">
            <figure class="w-24 md:w-32 flex-shrink-0">
                <img src="/public/${product.image_path}" alt="${product.product_name}" class="w-full h-full object-cover"/>
            </figure>
            <div class="card-body p-4">
                <h2 class="card-title text-base md:text-lg">${product.product_name}</h2>
                <p class="text-primary font-semibold">${Number(product.price).toLocaleString("vi-VN")} ₫</p>
                <div class="card-actions items-center justify-between mt-2">
                    <div class="join">
                        <button class="btn btn-sm join-item btn-decrease">-</button>
                        <input type="number" value="${product.quantity}" min="1" max="${product.stock_quantity}" class="input input-sm join-item w-12 text-center quantity-input" />
                        <button class="btn btn-sm join-item btn-increase">+</button>
                    </div>
                    <button class="btn btn-ghost btn-sm text-error btn-remove">Xóa</button>
                </div>
            </div>
        </div>
    `)
        .join("");
    updateTotals();
}
/**
 * Handles clicks on quantity buttons and remove button
 */
function handleCartActions(event) {
    const target = event.target;
    const card = target.closest(".card");
    if (!card)
        return;
    const productId = card.dataset.productId;
    if (!productId)
        return;
    if (target.classList.contains("btn-increase")) {
        updateQuantity(productId, 1);
    }
    else if (target.classList.contains("btn-decrease")) {
        updateQuantity(productId, -1);
    }
    else if (target.matches(".quantity-input")) {
        const input = target;
        input.addEventListener("change", () => {
            const newQuantity = parseInt(input.value, 10);
            updateQuantity(productId, 0, newQuantity); // 0 means set to newQuantity
        });
    }
    else if (target.classList.contains("btn-remove")) {
        removeItem(productId);
        Helpers.updateCartBadge();
    }
}
/**
 * Updates the quantity of a product in the cart
 */
function updateQuantity(productId, change, absoluteValue) {
    const productIndex = fullProductInfo.findIndex((p) => p.id == productId);
    if (productIndex === -1)
        return;
    const product = fullProductInfo[productIndex];
    let newQuantity = absoluteValue !== undefined ? absoluteValue : product.quantity + change;
    if (newQuantity < 1)
        newQuantity = 1;
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
    const cartItemIndex = cart.findIndex((item) => item.productId === productId);
    if (cartItemIndex > -1) {
        cart[cartItemIndex].quantity = newQuantity;
        localStorage.setItem("cart", JSON.stringify(cart));
    }
    // Re-render the specific item's quantity input and totals
    const input = cartItemsContainer?.querySelector(`[data-product-id="${productId}"] .quantity-input`);
    if (input)
        input.value = newQuantity.toString();
    updateTotals();
}
/**
 * Removes an item from the cart
 */
function removeItem(productId) {
    // Remove from state
    fullProductInfo = fullProductInfo.filter((p) => p.id != productId);
    // Remove from localStorage
    let cart = JSON.parse(localStorage.getItem("cart") || "[]");
    cart = cart.filter((item) => item.productId !== productId);
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
    const subtotal = fullProductInfo.reduce((sum, p) => sum + p.price * p.quantity, 0);
    const shipping = 0; // Fixed for now
    if (subtotalEl)
        subtotalEl.textContent = `${subtotal.toLocaleString("vi-VN")} ₫`;
    if (shippingFeeEl)
        shippingFeeEl.textContent = `${shipping.toLocaleString("vi-VN")} ₫`;
    if (totalPriceEl)
        totalPriceEl.textContent = `${(subtotal + shipping).toLocaleString("vi-VN")} ₫`;
    // Disable checkout if cart is empty
    if (checkoutBtn)
        checkoutBtn.disabled = fullProductInfo.length === 0;
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
function displayError(message) {
    if (cartItemsContainer) {
        cartItemsContainer.innerHTML = `<div class="alert alert-error">${message}</div>`;
    }
}
async function fetchAndFillUserInfo() {
    try {
        const response = await authService.fetchWithAuth("/api/profile/info");
        if (!response.ok)
            return;
        const result = await response.json();
        if (result.success && result.data) {
            const user = result.data;
            if (nameInput)
                nameInput.value = user.full_name || "";
            if (phoneInput)
                phoneInput.value = user.phone_number || "";
            if (addressTextarea)
                addressTextarea.value = user.address || "";
        }
    }
    catch (e) {
        console.warn("Could not pre-fill user info for checkout.");
    }
}
async function handleCheckout(event) {
    event.preventDefault();
    const submitButton = event.target.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang xử lý...`;
    const shippingInfo = {
        name: nameInput.value,
        phone: phoneInput.value,
        address: addressTextarea.value,
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
        }
        else {
            toastManager.createToast({
                message: result.message || "Đặt hàng thất bại.",
                type: "error",
            });
        }
    }
    catch (error) {
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ.",
            type: "error",
        });
    }
    finally {
        submitButton.disabled = false;
        submitButton.innerHTML = `Xác nhận đặt hàng`;
    }
}
// --- Event Listeners ---
document.addEventListener("DOMContentLoaded", initializeCart);
cartItemsContainer?.addEventListener("click", handleCartActions);
checkoutForm?.addEventListener("submit", handleCheckout);
continueShoppingBtn?.addEventListener("click", () => Helpers.redirect("/"));
// Also close the modal if clicked outside
checkoutModal.addEventListener("click", (event) => {
    if (event.target === checkoutModal) {
        checkoutModal.close();
    }
});
