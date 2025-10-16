import type { ToastType } from "./type.js";

class ToastManager {
  private toastContainer: HTMLDivElement;

  constructor() {
    const findToast: HTMLDivElement | null = document.querySelector(
      ".toast.toast-top.toast-center"
    );
    if (findToast === null) {
      const body = document.body;
      this.toastContainer = document.createElement("div");
      this.toastContainer.className = "toast toast-top toast-center z-9999";
      body.prepend(this.toastContainer);
    } else this.toastContainer = findToast;
  }

  createToast({
    message,
    type = "success",
    duration = 5000,
  }: {
    message: string;
    type?: ToastType;
    duration?: number;
  }): void {
    const toastElement = document.createElement("div");
    toastElement.classList.add("custom-toast", type);
    toastElement.textContent = message;

    this.toastContainer.appendChild(toastElement);

    setTimeout(() => {
      toastElement.classList.add("show");
    }, 10);

    setTimeout(() => {
      toastElement.classList.remove("show");

      toastElement.addEventListener("transitionend", () => {
        toastElement.remove();
      });
    }, duration);
  }
}

export const toastManager = new ToastManager();
