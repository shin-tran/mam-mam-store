import { CartManager } from "./CartManager.js";
import { AddressManager } from "./AddressManager.js";
import { CheckoutManager } from "./CheckoutManager.js";

// Initialize managers
const cartManager = new CartManager();
const addressManager = new AddressManager();
// CheckoutManager tự động setup event listeners trong constructor
new CheckoutManager(addressManager);

// Get cart items container
const cartItemsContainer = document.getElementById("cart-items-container");

/**
 * Initialize the orders page
 */
async function initializeOrdersPage() {
  await cartManager.initialize();
  await addressManager.fetchUserAddresses();
}

// Đợi trang load hoàn toàn rồi mới chạy
document.addEventListener("DOMContentLoaded", initializeOrdersPage);

// Cart actions handler
cartItemsContainer?.addEventListener("click", (e) => {
  cartManager.handleCartActions(e);
});
