import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";
import { Helpers } from "../../utils/helpers.js";
import { AddressManager } from "./AddressManager.js";

export class CheckoutManager {
  private checkoutModal: HTMLDialogElement | null;
  private checkoutForm: HTMLFormElement | null;
  private successModal: HTMLDialogElement | null;
  private continueShoppingBtn: Element | null;
  private addressManager: AddressManager;

  constructor(addressManager: AddressManager) {
    this.addressManager = addressManager;
    this.checkoutModal = document.getElementById(
      "checkout_modal"
    ) as HTMLDialogElement;
    this.checkoutForm = document.getElementById(
      "checkout-form"
    ) as HTMLFormElement;
    this.successModal = document.getElementById(
      "success_modal"
    ) as HTMLDialogElement;
    this.continueShoppingBtn = this.successModal?.querySelector("button");

    this.setupEventListeners();
  }

  /**
   * Setup event listeners
   */
  private setupEventListeners() {
    this.checkoutForm?.addEventListener("submit", (e) => {
      this.handleCheckout(e as SubmitEvent);
    });

    this.continueShoppingBtn?.addEventListener("click", () =>
      Helpers.redirect("/")
    );

    // Close modal if clicked outside
    this.checkoutModal?.addEventListener("click", (event) => {
      if (event.target === this.checkoutModal) {
        this.checkoutModal?.close();
      }
    });
  }

  /**
   * Handle checkout process
   */
  private async handleCheckout(event: SubmitEvent) {
    event.preventDefault();

    // Validate selected address
    const selectedAddressId = this.addressManager.getSelectedAddressId();
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
    const selectedAddress = this.addressManager.getSelectedAddress();

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
      note: "",
    };

    // Get cart items from localStorage
    const cart = JSON.parse(localStorage.getItem("cart") || "[]");
    const cartItems = cart.map((item: any) => ({
      productId: item.productId,
      quantity: item.quantity,
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
        if (this.checkoutModal) this.checkoutModal.close();
        if (this.successModal) this.successModal.showModal();
        Helpers.updateCartBadge();
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
}
