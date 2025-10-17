import { authService } from "../services/auth-service.js";
import { toastManager } from "../toast-manager.js";
import { spinnerIcon } from "../utils/constants.js";
// --- Phần Giỏ Hàng & Mua Hàng ---
const quantityInput = document.getElementById("quantity");
const decreaseBtn = document.querySelector(".btn-decrease");
const increaseBtn = document.querySelector(".btn-increase");
const addToCartBtn = document.querySelector(".add-to-cart-btn");
const buyNowBtn = document.querySelector(".buy-now-btn");
if (quantityInput && decreaseBtn && increaseBtn && addToCartBtn && buyNowBtn) {
    const productId = addToCartBtn.dataset.id;
    const maxStock = parseInt(addToCartBtn.dataset.stock || "999");
    function updateQuantity(value) {
        let newValue = parseInt(quantityInput.value) + value;
        if (newValue < 1)
            newValue = 1;
        if (newValue > maxStock) {
            toastManager.createToast({
                message: `Chỉ còn ${maxStock} sản phẩm trong kho`,
                type: "error",
            });
            newValue = maxStock;
        }
        quantityInput.value = newValue.toString();
        updateButtonStates();
    }
    function updateButtonStates() {
        const currentValue = parseInt(quantityInput.value);
        decreaseBtn.disabled = currentValue <= 1;
        increaseBtn.disabled = currentValue >= maxStock;
    }
    decreaseBtn.addEventListener("click", () => updateQuantity(-1));
    increaseBtn.addEventListener("click", () => updateQuantity(1));
    quantityInput.addEventListener("input", () => {
        let value = parseInt(quantityInput.value);
        if (isNaN(value) || value < 1) {
            quantityInput.value = "1";
        }
        else if (value > maxStock) {
            quantityInput.value = maxStock.toString();
            toastManager.createToast({
                message: `Chỉ còn ${maxStock} sản phẩm trong kho`,
                type: "error",
            });
        }
        updateButtonStates();
    });
    addToCartBtn.addEventListener("click", () => handleAddToCart(false));
    buyNowBtn.addEventListener("click", () => handleAddToCart(true));
    async function handleAddToCart(isBuyNow) {
        const quantity = parseInt(quantityInput.value);
        if (!productId) {
            toastManager.createToast({
                message: "Không tìm thấy sản phẩm",
                type: "error",
            });
            return;
        }
        const button = isBuyNow ? buyNowBtn : addToCartBtn;
        button.disabled = true;
        button.innerHTML = `<span class="loading loading-spinner"></span> ${isBuyNow ? "Đang xử lý..." : "Đang thêm..."}`;
        try {
            const cart = JSON.parse(localStorage.getItem("cart") || "[]");
            const existingItemIndex = cart.findIndex((item) => item.productId === productId);
            let newTotalQuantity = quantity;
            if (existingItemIndex > -1) {
                newTotalQuantity = cart[existingItemIndex].quantity + quantity;
                if (newTotalQuantity > maxStock) {
                    toastManager.createToast({
                        message: `Không thể thêm! Giỏ hàng đã có ${cart[existingItemIndex].quantity} sản phẩm. Chỉ còn ${maxStock} sản phẩm trong kho.`,
                        type: "error",
                    });
                    return;
                }
                cart[existingItemIndex].quantity = newTotalQuantity;
            }
            else {
                cart.push({ productId, quantity, addedAt: new Date().toISOString() });
            }
            localStorage.setItem("cart", JSON.stringify(cart));
            updateCartBadge();
            if (isBuyNow) {
                window.location.href = "/orders";
            }
            else {
                toastManager.createToast({
                    message: "Đã thêm vào giỏ hàng",
                    type: "success",
                });
            }
        }
        catch (error) {
            console.error("Error adding to cart:", error);
            toastManager.createToast({
                message: "Không thể thêm vào giỏ hàng",
                type: "error",
            });
        }
        finally {
            button.disabled = false;
            button.innerHTML = isBuyNow ? "Mua ngay" : "Thêm vào giỏ hàng";
        }
    }
    function updateCartBadge() {
        const cart = JSON.parse(localStorage.getItem("cart") || "[]");
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        const badge = document.querySelector("#cart-badge");
        if (badge)
            badge.textContent = totalItems.toString();
    }
    updateButtonStates();
    updateCartBadge();
}
// --- Phần Đánh giá ---
const reviewForm = document.getElementById("review-form");
const reviewsList = document.getElementById("reviews-list");
const noReviewsText = document.getElementById("no-reviews-text");
const productId = addToCartBtn?.dataset.id;
reviewForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!productId)
        return;
    const formData = new FormData(reviewForm);
    const submitButton = reviewForm.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = `${spinnerIcon} Đang gửi...`;
    }
    try {
        const response = await authService.fetchWithAuth(`/api/products/${productId}/reviews`, {
            method: "POST",
            body: formData,
        });
        const result = await response.json();
        if (result.success) {
            toastManager.createToast({ message: result.message, type: "success" });
            reviewForm.reset();
            window.location.reload();
        }
        else {
            toastManager.createToast({
                message: result.message || "Gửi đánh giá thất bại.",
                type: "error",
            });
        }
    }
    catch (error) {
        console.error("Error submitting review:", error);
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ.",
            type: "error",
        });
    }
    finally {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = "Gửi đánh giá";
        }
    }
});
