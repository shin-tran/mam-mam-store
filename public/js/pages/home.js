import { authService } from "../services/auth-service.js";
import { toastManager } from "../toast-manager.js";
import { Helpers } from "../utils/helpers.js";
// === DOM Elements ===
const filterButtons = document.querySelectorAll(".filter-btn");
const searchInput = document.getElementById("search-input");
const productGrid = document.getElementById("product-grid");
const productCards = document.querySelectorAll(".product-card");
const cartBadge = document.getElementById("cart-badge");
let activeCategory = "all";
// === Cart Logic ===
function updateCartBadge() {
    if (!cartBadge)
        return;
    const cart = JSON.parse(localStorage.getItem("cart") || "[]");
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartBadge.textContent = totalItems.toString();
}
function addToCart(productId, quantity, maxStock) {
    if (!authService.isLoggedIn()) {
        Helpers.redirect("/login");
        return false;
    }
    try {
        const cart = JSON.parse(localStorage.getItem("cart") || "[]");
        const existingItemIndex = cart.findIndex((item) => item.productId === productId);
        let newTotalQuantity = quantity;
        if (existingItemIndex > -1) {
            newTotalQuantity += cart[existingItemIndex].quantity;
        }
        if (newTotalQuantity > maxStock) {
            toastManager.createToast({
                message: `Trong giỏ đã có ${cart[existingItemIndex]?.quantity || 0}. Không thể thêm quá ${maxStock} sản phẩm.`,
                type: "error",
            });
            return false;
        }
        if (existingItemIndex > -1) {
            cart[existingItemIndex].quantity += quantity;
        }
        else {
            cart.push({ productId, quantity, addedAt: new Date().toISOString() });
        }
        localStorage.setItem("cart", JSON.stringify(cart));
        updateCartBadge();
        return true;
    }
    catch (error) {
        console.error("Error adding to cart:", error);
        toastManager.createToast({
            message: "Không thể thêm vào giỏ hàng",
            type: "error",
        });
        return false;
    }
}
// === Event Listeners ===
function filterProducts() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    productCards.forEach((card) => {
        const cardCategory = card.dataset.category || "";
        const cardName = card.dataset.name || "";
        const categoryMatch = activeCategory === "all" || cardCategory === activeCategory;
        const searchMatch = cardName.includes(searchTerm);
        if (categoryMatch && searchMatch) {
            card.style.display = "block";
        }
        else {
            card.style.display = "none";
        }
    });
}
filterButtons.forEach((button) => {
    button.addEventListener("click", () => {
        document.querySelector(".filter-btn.active")?.classList.remove("active");
        button.classList.add("active");
        activeCategory = button.dataset.category || "all";
        filterProducts();
    });
});
searchInput?.addEventListener("keyup", filterProducts);
productGrid?.addEventListener("click", (event) => {
    const target = event.target;
    const button = target.closest(".add-to-cart-btn, .buy-now-btn");
    if (!button)
        return;
    const productId = button.dataset.id;
    const stock = parseInt(button.dataset.stock || "0", 10);
    if (!productId)
        return;
    if (stock <= 0) {
        toastManager.createToast({
            message: "Sản phẩm đã hết hàng!",
            type: "error",
        });
        return;
    }
    const isSuccess = addToCart(productId, 1, stock);
    if (isSuccess) {
        if (button.classList.contains("add-to-cart-btn")) {
            toastManager.createToast({
                message: "Đã thêm sản phẩm vào giỏ hàng!",
                type: "success",
            });
        }
        else if (button.classList.contains("buy-now-btn")) {
            Helpers.redirect("/orders");
        }
    }
});
// --- Initialization ---
document.addEventListener("DOMContentLoaded", () => {
    updateCartBadge();
});
