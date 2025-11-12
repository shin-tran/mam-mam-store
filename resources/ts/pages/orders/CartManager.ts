import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";
import type { CartItem, LocalStorageCartItem } from "../../types/product.js";
import type { ShippingConfig } from "../../types/shipping.js";
import { Helpers } from "../../utils/helpers.js";

export class CartManager {
  private cartItemsContainer: HTMLElement | null;
  private subtotalEl: HTMLElement | null;
  private shippingFeeEl: HTMLElement | null;
  private totalPriceEl: HTMLElement | null;
  private checkoutBtn: HTMLButtonElement | null;
  private fullProductInfo: CartItem[] = [];
  private shippingConfig: ShippingConfig | null = null;

  constructor() {
    this.cartItemsContainer = document.getElementById("cart-items-container");
    this.subtotalEl = document.getElementById("subtotal");
    this.shippingFeeEl = document.getElementById("shipping-fee");
    this.totalPriceEl = document.getElementById("total-price");
    this.checkoutBtn = document.querySelector(".btn-block");
  }

  /**
   * Initialize the cart page
   */
  async initialize() {
    // Load shipping config first
    await this.loadShippingConfig();

    const cart: LocalStorageCartItem[] = JSON.parse(
      localStorage.getItem("cart") || "[]"
    );

    if (cart.length === 0) {
      this.displayEmptyCart();
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
        this.fullProductInfo = result.data
          .map((product: CartItem) => {
            const cartItem = cart.find(
              (item) => item.productId === String(product.id)
            );
            return { ...product, quantity: cartItem ? cartItem.quantity : 0 };
          })
          .filter((p: CartItem) => p.quantity > 0);

        this.renderCart();
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      console.error("Failed to fetch cart products:", error);
      this.displayError("Không thể tải giỏ hàng. Vui lòng thử lại.");
    }
  }

  /**
   * Load shipping configuration from API
   */
  private async loadShippingConfig() {
    try {
      const response = await fetch("/api/shipping/config");
      const result = await response.json();

      if (result.success) {
        this.shippingConfig = result.data;
      } else {
        console.error("Failed to load shipping config:", result.message);
        // Set default values if API fails
        this.shippingConfig = {
          freeShippingThreshold: 150000,
          standardShippingFee: 15000,
        };
      }
    } catch (error) {
      console.error("Error loading shipping config:", error);
      // Set default values if API fails
      this.shippingConfig = {
        freeShippingThreshold: 150000,
        standardShippingFee: 15000,
      };
    }
  }

  /**
   * Calculate shipping fee based on subtotal
   */
  private calculateShippingFee(subtotal: number): number {
    if (!this.shippingConfig) return 0;

    const { freeShippingThreshold, standardShippingFee } = this.shippingConfig;

    // Free shipping if subtotal meets threshold
    if (subtotal >= freeShippingThreshold && freeShippingThreshold > 0) {
      return 0;
    }

    return standardShippingFee;
  }

  /**
   * Renders the entire cart
   */
  private renderCart() {
    if (!this.cartItemsContainer) return;

    if (this.fullProductInfo.length === 0) {
      this.displayEmptyCart();
      return;
    }

    this.cartItemsContainer.innerHTML = this.fullProductInfo
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

    this.updateTotals();
  }

  /**
   * Handle cart actions (increase, decrease, remove)
   */
  handleCartActions(event: Event) {
    const target = event.target as HTMLElement;
    const card = target.closest(".card") as HTMLDivElement;
    if (!card) return;

    const productId = card.dataset.productId;
    if (!productId) return;

    if (target.classList.contains("btn-increase")) {
      this.updateQuantity(productId, 1);
    } else if (target.classList.contains("btn-decrease")) {
      this.updateQuantity(productId, -1);
    } else if (target.matches(".quantity-input")) {
      const input = target as HTMLInputElement;
      input.addEventListener("change", () => {
        const newQuantity = parseInt(input.value, 10);
        this.updateQuantity(productId, 0, newQuantity);
      });
    } else if (target.classList.contains("btn-remove")) {
      this.removeItem(productId);
      Helpers.updateCartBadge();
    }
  }

  /**
   * Update product quantity
   */
  private updateQuantity(
    productId: string,
    change: number,
    absoluteValue?: number
  ) {
    const productIndex = this.fullProductInfo.findIndex(
      (p) => p.id === Number(productId)
    );
    if (productIndex === -1) return;

    const product = this.fullProductInfo[productIndex];
    if (!product) return;

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
    const cart: LocalStorageCartItem[] = JSON.parse(
      localStorage.getItem("cart") || "[]"
    );
    const cartItemIndex = cart.findIndex(
      (item) => item.productId === productId
    );
    if (cartItemIndex > -1 && cart[cartItemIndex]) {
      cart[cartItemIndex].quantity = newQuantity;
      localStorage.setItem("cart", JSON.stringify(cart));
    }

    // Re-render the specific item's quantity input and totals
    const input = this.cartItemsContainer?.querySelector(
      `[data-product-id="${productId}"] .quantity-input`
    ) as HTMLInputElement;
    if (input) input.value = newQuantity.toString();
    this.updateTotals();
  }

  /**
   * Remove item from cart
   */
  private removeItem(productId: string) {
    // Remove from state
    this.fullProductInfo = this.fullProductInfo.filter(
      (p) => p.id !== Number(productId)
    );

    // Remove from localStorage
    let cart: LocalStorageCartItem[] = JSON.parse(
      localStorage.getItem("cart") || "[]"
    );
    cart = cart.filter((item) => item.productId !== productId);
    localStorage.setItem("cart", JSON.stringify(cart));

    toastManager.createToast({
      message: "Đã xóa sản phẩm khỏi giỏ hàng",
      type: "success",
    });

    this.renderCart();
  }

  /**
   * Update totals (subtotal, shipping, total)
   */
  private updateTotals() {
    const subtotal = this.fullProductInfo.reduce(
      (sum, p) => sum + parseFloat(p.price) * p.quantity,
      0
    );
    const shipping = this.calculateShippingFee(subtotal);

    if (this.subtotalEl)
      this.subtotalEl.textContent = `${subtotal.toLocaleString("vi-VN")} ₫`;
    if (this.shippingFeeEl) {
      if (shipping === 0 && subtotal > 0 && this.shippingConfig) {
        this.shippingFeeEl.innerHTML = `<span class="text-success">Miễn phí</span>`;
      } else {
        this.shippingFeeEl.textContent = `${shipping.toLocaleString("vi-VN")} ₫`;
      }
    }
    if (this.totalPriceEl)
      this.totalPriceEl.textContent = `${(subtotal + shipping).toLocaleString(
        "vi-VN"
      )} ₫`;

    // Disable checkout if cart is empty
    if (this.checkoutBtn) {
      this.checkoutBtn.disabled = this.fullProductInfo.length === 0;
    }
  }

  /**
   * Display empty cart state
   */
  private displayEmptyCart() {
    if (this.cartItemsContainer) {
      this.cartItemsContainer.innerHTML = `
      <div class="card bg-base-100 shadow-xl">
          <div class="card-body items-center text-center">
              <p class="text-lg">Giỏ hàng của bạn đang trống.</p>
              <div class="card-actions mt-4">
                  <a href="/" class="btn btn-primary">Tiếp tục mua sắm</a>
              </div>
          </div>
      </div>`;
    }
    this.updateTotals();
  }

  /**
   * Display error message
   */
  private displayError(message: string) {
    if (this.cartItemsContainer) {
      this.cartItemsContainer.innerHTML = `<div class="alert alert-error">${message}</div>`;
    }
  }

  /**
   * Get current cart products
   */
  getProducts(): CartItem[] {
    return this.fullProductInfo;
  }
}
