import { spinnerIcon } from "../../utils/constants.js";
import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";
import { Helpers } from "../../utils/helpers.js";

const registerForm = document.getElementById(
  "register-form"
) as HTMLFormElement;
const inputs = registerForm.querySelectorAll<HTMLInputElement>("[data-field]");

async function validateField(input: HTMLInputElement): Promise<boolean> {
  const fieldName = input.name;
  const value = input.value?.trim();
  let errorMessage = "";

  Helpers.clearError(fieldName);

  switch (fieldName) {
    case "full_name":
      if (!value) errorMessage = "Họ tên không được bỏ trống!";
      else if (value.length < 5)
        errorMessage = "Họ tên phải có ít nhất 5 ký tự!";
      break;

    case "email":
      if (!value) errorMessage = "Email không được bỏ trống!";
      else if (!Helpers.validateEmail(value))
        errorMessage = "Email không hợp lệ!";
      else if (await authService.checkEmailExists(value))
        errorMessage = "Email này đã được sử dụng!";
      break;

    case "password":
      if (!value) errorMessage = "Mật khẩu không được để trống!";
      else if (value.length < 6)
        errorMessage = "Mật khẩu phải lớn hơn 6 ký tự!";
      break;

    case "confirm_password":
      const passwordInput =
        registerForm.querySelector<HTMLInputElement>("[name='password']");
      if (!value) errorMessage = "Hãy nhập lại mật khẩu!";
      else if (passwordInput && value !== passwordInput.value)
        errorMessage = "Mật khẩu không khớp!";
      break;
  }

  if (errorMessage) {
    Helpers.displayError(fieldName, errorMessage);
    return false;
  }
  return true;
}

inputs.forEach((input) => {
  input.addEventListener("blur", () => {
    validateField(input);
  });
});

registerForm.addEventListener("submit", async (event: SubmitEvent) => {
  event.preventDefault();

  let isFormValid = true;

  const validationPromises = Array.from(inputs).map((input) =>
    validateField(input)
  );
  const results = await Promise.all(validationPromises);

  isFormValid = results.every((isValid) => isValid);

  if (!isFormValid) return;

  const submitButton = registerForm.querySelector<HTMLButtonElement>(
    'button[type="submit"]'
  );
  if (submitButton) {
    submitButton.disabled = true;
    submitButton.innerHTML = `${spinnerIcon} Đang đăng ký...`;
  }

  try {
    const formData = new FormData(registerForm);
    const result = await authService.register(formData);

    if (result.success) {
      toastManager.createToast({
        message: result.message,
        type: "success",
      });
      registerForm.reset();
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
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.innerHTML = "Đăng ký";
    }
  }
});
