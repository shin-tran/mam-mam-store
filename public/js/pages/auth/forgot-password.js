import { spinnerIcon } from "../../utils/constants.js";
import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";
import { Helpers } from "../../utils/helpers.js";
const forgotForm = document.getElementById("forgot-form");
const inputs = forgotForm.querySelectorAll("[data-field]");
// TODO: hàm được dùng nhiều nơi nên xây dựng thành class helper
async function validateField(input) {
    const fieldName = input.name;
    const value = input.value?.trim();
    let errorMessage = "";
    Helpers.clearError(fieldName);
    switch (fieldName) {
        case "email_phone_number":
            if (!value)
                errorMessage = "Email / Số điện thoại không được bỏ trống!";
            else if (Number(value)) {
                if (!Helpers.isPhone(value))
                    errorMessage = "Số điện thoại không hợp lệ!";
            }
            else if (!Helpers.validateEmail(value))
                errorMessage = "Email không hợp lệ!";
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
// FIX: chỉ cho user gửi lại sau 1p
forgotForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    let isFormValid = true;
    const validationPromises = Array.from(inputs).map((input) => validateField(input));
    const results = await Promise.all(validationPromises);
    isFormValid = results.every((isValid) => isValid);
    if (!isFormValid)
        return;
    // TODO: tạo hệ thống hoặc gì đó giúp tối ưu handle loading
    const submitButton = forgotForm.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = `${spinnerIcon} Đang gửi...`;
    }
    try {
        const formData = new FormData(forgotForm);
        const result = await authService.forgotPassword(formData);
        if (result.success) {
            toastManager.createToast({
                message: result.message,
                type: "success",
            });
            forgotForm.reset();
        }
        else if (result.errors) {
            Object.keys(result.errors).forEach((key) => {
                Helpers.displayError(key, result.errors[key][0]);
            });
        }
        else {
            toastManager.createToast({
                message: result.message,
                type: "error",
            });
        }
    }
    catch (error) {
        console.log(error);
    }
    finally {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = "Gửi";
        }
    }
});
