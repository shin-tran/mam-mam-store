import { toastManager } from "../toast-manager.js";
// Lấy các elements
const quantityInput = document.getElementById("quantity");
const decreaseBtn = document.querySelector(".btn-decrease");
const increaseBtn = document.querySelector(".btn-increase");
const addToCartBtn = document.querySelector(".add-to-cart-btn");
const buyNowBtn = document.querySelector(".buy-now-btn");
if (!quantityInput ||
    !decreaseBtn ||
    !increaseBtn ||
    !addToCartBtn ||
    !buyNowBtn) {
    console.error("Không tìm thấy elements cần thiết");
}
else {
    const productId = addToCartBtn.dataset.id;
    const maxStock = parseInt(addToCartBtn.dataset.stock || "999");
    // Hàm cập nhật số lượng
    function updateQuantity(value) {
        let newValue = parseInt(quantityInput.value) + value;
        if (newValue < 1) {
            newValue = 1;
        }
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
    // Cập nhật trạng thái nút
    function updateButtonStates() {
        const currentValue = parseInt(quantityInput.value);
        decreaseBtn.disabled = currentValue <= 1;
        increaseBtn.disabled = currentValue >= maxStock;
    }
    // Xử lý giảm số lượng
    decreaseBtn.addEventListener("click", () => {
        updateQuantity(-1);
    });
    // Xử lý tăng số lượng
    increaseBtn.addEventListener("click", () => {
        updateQuantity(1);
    });
    // Validate input thủ công
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
    // Thêm vào giỏ hàng
    addToCartBtn.addEventListener("click", async () => {
        const quantity = parseInt(quantityInput.value);
        if (!productId) {
            toastManager.createToast({
                message: "Không tìm thấy sản phẩm",
                type: "error",
            });
            return;
        }
        addToCartBtn.disabled = true;
        addToCartBtn.innerHTML =
            '<span class="loading loading-spinner"></span> Đang thêm...';
        try {
            // Lấy giỏ hàng từ localStorage
            const cart = JSON.parse(localStorage.getItem("cart") || "[]");
            // Kiểm tra sản phẩm đã có trong giỏ chưa
            const existingItemIndex = cart.findIndex((item) => item.productId === productId);
            let newTotalQuantity = quantity;
            if (existingItemIndex > -1) {
                // Tính tổng số lượng mới
                newTotalQuantity = cart[existingItemIndex].quantity + quantity;
                // Kiểm tra không vượt quá stock
                if (newTotalQuantity > maxStock) {
                    toastManager.createToast({
                        message: `Không thể thêm! Giỏ hàng đã có ${cart[existingItemIndex].quantity} sản phẩm. Chỉ còn ${maxStock} sản phẩm trong kho.`,
                        type: "error",
                    });
                    addToCartBtn.disabled = false;
                    addToCartBtn.innerHTML = "Thêm vào giỏ hàng";
                    return;
                }
                // Cập nhật số lượng
                cart[existingItemIndex].quantity = newTotalQuantity;
            }
            else {
                // Thêm mới
                cart.push({
                    productId: productId,
                    quantity: quantity,
                    addedAt: new Date().toISOString(),
                });
            }
            // Lưu vào localStorage
            localStorage.setItem("cart", JSON.stringify(cart));
            // Cập nhật badge số lượng
            updateCartBadge();
            toastManager.createToast({
                message: "Đã thêm vào giỏ hàng",
            });
        }
        catch (error) {
            console.error("Error adding to cart:", error);
            toastManager.createToast({
                message: "Không thể thêm vào giỏ hàng",
                type: "error",
            });
        }
        finally {
            addToCartBtn.disabled = false;
            addToCartBtn.innerHTML = "Thêm vào giỏ hàng";
        }
    });
    // Mua ngay
    buyNowBtn.addEventListener("click", async () => {
        const quantity = parseInt(quantityInput.value);
        if (!productId) {
            toastManager.createToast({
                message: "Không tìm thấy sản phẩm",
                type: "error",
            });
            return;
        }
        buyNowBtn.disabled = true;
        buyNowBtn.innerHTML =
            '<span class="loading loading-spinner"></span> Đang xử lý...';
        try {
            // Lấy giỏ hàng từ localStorage
            const cart = JSON.parse(localStorage.getItem("cart") || "[]");
            const existingItemIndex = cart.findIndex((item) => item.productId === productId);
            let newTotalQuantity = quantity;
            if (existingItemIndex > -1) {
                // Tính tổng số lượng mới
                newTotalQuantity = cart[existingItemIndex].quantity + quantity;
                // Kiểm tra không vượt quá stock
                if (newTotalQuantity > maxStock) {
                    toastManager.createToast({
                        message: `Không thể mua! Giỏ hàng đã có ${cart[existingItemIndex].quantity} sản phẩm. Chỉ còn ${maxStock} sản phẩm trong kho.`,
                        type: "error",
                    });
                    buyNowBtn.disabled = false;
                    buyNowBtn.innerHTML = "Mua ngay";
                    return;
                }
                // Cập nhật số lượng
                cart[existingItemIndex].quantity = newTotalQuantity;
            }
            else {
                // Thêm mới
                cart.push({
                    productId: productId,
                    quantity: quantity,
                    addedAt: new Date().toISOString(),
                });
            }
            localStorage.setItem("cart", JSON.stringify(cart));
            // Chuyển hướng đến trang thanh toán
            window.location.href = "/orders";
        }
        catch (error) {
            console.error("Error buying now:", error);
            toastManager.createToast({
                message: "Không thể thực hiện mua hàng",
                type: "error",
            });
            buyNowBtn.disabled = false;
            buyNowBtn.innerHTML = "Mua ngay";
        }
    });
    // Hàm cập nhật badge giỏ hàng
    function updateCartBadge() {
        const cart = JSON.parse(localStorage.getItem("cart") || "[]");
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        const badge = document.querySelector(".indicator-item");
        if (badge) {
            badge.textContent = totalItems.toString();
        }
    }
    // Khởi tạo
    updateButtonStates();
    updateCartBadge();
}
