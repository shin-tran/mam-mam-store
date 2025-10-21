import { AppConfig } from "../app.js";

export class Helpers {
  static validateEmail(email: string): boolean {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }

  static isPhone(phone: string): boolean {
    let cleaned = phone.replace(/[^0-9+]/g, "");
    if (cleaned.startsWith("+84")) cleaned = "0" + cleaned.slice(3);
    const regex = /^(0)(3[2-9]|5[689]|7[06-9]|8[1-689]|9[0-46-9])[0-9]{7}$/;
    return regex.test(cleaned);
  }

  static displayError(fieldName: string, message: string) {
    const errorElement = document.querySelector(
      `.error-log[data-field="${fieldName}"]`
    );
    if (errorElement) {
      message === ""
        ? errorElement.classList.add("hidden")
        : errorElement.classList.remove("hidden");
      errorElement.textContent = message;
    }
  }

  static clearError(fieldName: string) {
    this.displayError(fieldName, "");
  }

  static redirect(path: string = "") {
    window.location.href = `${AppConfig.BASE_URL}${path}`;
  }

  static updateCartBadge() {
    const cartBadge = document.getElementById("cart-badge");

    if (!cartBadge) return;
    const cart = JSON.parse(localStorage.getItem("cart") || "[]");
    const totalItems = cart.reduce(
      (sum: number, item: any) => sum + item.quantity,
      0
    );

    cartBadge.textContent = totalItems.toString();
  }
}
