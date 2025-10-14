import { spinnerIcon } from "../../utils/constants.js";
import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";
import { Helpers } from "../../utils/helpers.js";
const loginForm = document.getElementById("login-form");
const inputs = loginForm.querySelectorAll("[data-field]");
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
        case "password":
            if (!value)
                errorMessage = "Mật khẩu không được để trống!";
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
loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    let isFormValid = true;
    const validationPromises = Array.from(inputs).map((input) => validateField(input));
    const results = await Promise.all(validationPromises);
    isFormValid = results.every((isValid) => isValid);
    if (!isFormValid)
        return;
    // TODO: tạo hệ thống hoặc gì đó giúp tối ưu handle loading
    const submitButton = loginForm.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = `${spinnerIcon} Đang đăng nhập...`;
    }
    try {
        const formData = new FormData(loginForm);
        const result = await authService.login(formData);
        if (result.success) {
            toastManager.createToast({
                message: result.message,
                type: "success",
            });
            Helpers.redirect();
            loginForm.reset();
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
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ!",
            type: "error",
        });
    }
    finally {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = "Đăng nhập";
        }
    }
});
