import { authService } from "../services/auth-service.js";
import { toastManager } from "../toast-manager.js";
// === Xử lý đăng xuất ===
const logoutBtn = document.getElementById("btn-logout");
logoutBtn?.addEventListener("click", async () => {
    await authService.logout();
    localStorage.removeItem("cart");
});
// === Xử lý lọc và tìm kiếm sản phẩm ===
const filterButtons = document.querySelectorAll(".filter-btn");
const searchInput = document.getElementById("search-input");
const productGrid = document.getElementById("product-grid");
const productCards = document.querySelectorAll(".product-card");
let activeCategory = "all";
// Hàm để lọc sản phẩm
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
// Gắn sự kiện cho các nút lọc danh mục
filterButtons.forEach((button) => {
    button.addEventListener("click", () => {
        // Bỏ active ở nút cũ, thêm active vào nút mới
        document.querySelector(".filter-btn.active")?.classList.remove("active");
        button.classList.add("active");
        // Lấy danh mục và lọc
        activeCategory = button.dataset.category || "all";
        filterProducts();
    });
});
// Gắn sự kiện cho ô tìm kiếm
searchInput?.addEventListener("keyup", filterProducts);
// === Xử lý thêm vào giỏ hàng ===
productGrid?.addEventListener("click", (event) => {
    const target = event.target;
    const addToCartButton = target.closest(".add-to-cart-btn");
    if (addToCartButton) {
        const productId = addToCartButton.getAttribute("data-id");
        console.log(`Product ID: ${productId} added to cart.`);
        // Hiển thị thông báo
        toastManager.createToast({
            message: "Đã thêm sản phẩm vào giỏ hàng!",
            type: "success",
        });
        // TODO: Thêm logic xử lý giỏ hàng thực tế ở đây (ví dụ: gọi API, cập nhật LocalStorage...)
    }
});
