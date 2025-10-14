import { FULL_URL } from "../../app.js";
import { spinnerIcon } from "../../utils/constants.js";
import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";
import { Helpers } from "../../utils/helpers.js";

const resetPasswordForm = document.getElementById(
  "reset-password-form"
) as HTMLFormElement;
const inputs =
  resetPasswordForm.querySelectorAll<HTMLInputElement>("[data-field]");

const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
const token = urlParams.get("token") as string;

async function validateField(input: HTMLInputElement): Promise<boolean> {
  const fieldName = input.name;
  const value = input.value?.trim();
  let errorMessage = "";

  Helpers.clearError(fieldName);

  switch (fieldName) {
    case "new_password":
      if (!value) errorMessage = "Mật khẩu không được để trống!";
      else if (value.length < 6)
        errorMessage = "Mật khẩu phải lớn hơn 6 ký tự!";
      break;

    case "confirm_password":
      const passwordInput =
        resetPasswordForm.querySelector<HTMLInputElement>("[name='password']");
      if (!value) errorMessage = "Hãy nhập lại mật khẩu!";
      else if (passwordInput && value !== passwordInput.value)
        errorMessage = "Mật khẩu không khớp!";
      break;
  }

  if (errorMessage) {
    Helpers.displayError(fieldName, errorMessage);
    return true;
  }
  return true;
}

inputs.forEach((input) => {
  input.addEventListener("blur", () => {
    validateField(input);
  });
});

resetPasswordForm.addEventListener("submit", async (event: SubmitEvent) => {
  event.preventDefault();

  let isFormValid = true;
  const validationPromises = Array.from(inputs).map((input) =>
    validateField(input)
  );
  const results = await Promise.all(validationPromises);
  isFormValid = results.every((isValid) => isValid);
  if (!isFormValid) return;

  const submitButton = resetPasswordForm.querySelector<HTMLButtonElement>(
    'button[type="submit"]'
  );
  if (submitButton) {
    submitButton.disabled = true;
    submitButton.innerHTML = `${spinnerIcon} Đang xác nhận...`;
  }

  try {
    const formData = new FormData(resetPasswordForm);
    formData.append("token", token);
    const result = await authService.resetPassword(formData);

    if (result.success) {
      toastManager.createToast({
        message: result.message,
        type: "success",
      });
      const anchorEle = document.createElement("a");
      const btnLogin = document.createElement("button");

      anchorEle.href = `${FULL_URL}/login`;
      btnLogin.type = "button";
      btnLogin.classList.add("btn", "w-full");
      btnLogin.textContent = "Đến trang đăng nhập";
      anchorEle.appendChild(btnLogin);

      resetPasswordForm.replaceChildren(anchorEle);
    } else if (result.errors) {
      Object.keys(result.errors).forEach((key) => {
        Helpers.displayError(key, result.errors![key]![0]!);
      });
    } else {
      toastManager.createToast({
        message: result.message,
        type: "error",
      });
    }
  } catch (error) {
    console.log(error);
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.innerHTML = "Xác nhận";
    }
  }
});
