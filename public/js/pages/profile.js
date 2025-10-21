import { authService } from "../services/auth-service.js";
import { toastManager } from "../toast-manager.js";
// --- DOM Elements ---
const detailsForm = document.getElementById("details-form");
const avatarForm = document.getElementById("avatar-form");
const avatarInput = document.getElementById("avatar-input");
const passwordForm = document.getElementById("password-form");
const avatarPreview = document.getElementById("avatar-preview");
// --- Event Listeners ---
detailsForm?.addEventListener("submit", handleUpdateDetails);
passwordForm?.addEventListener("submit", handleUpdatePassword);
avatarInput?.addEventListener("change", handleAvatarPreview);
avatarForm?.addEventListener("submit", handleAvatarUpload);
// --- Handler Functions ---
async function handleUpdateDetails(e) {
    e.preventDefault();
    const formData = new FormData(detailsForm);
    const submitButton = detailsForm.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang lưu...`;
    try {
        const response = await authService.fetchWithAuth("/api/users/update-details", {
            method: "POST",
            body: formData,
        });
        const result = await response.json();
        if (result.success) {
            toastManager.createToast({ message: result.message, type: "success" });
            // Update user's name in the header if it exists
            const fullName = formData.get("full_name");
            const headerNameElements = document.querySelectorAll(".navbar .inline-block");
            headerNameElements.forEach((element) => {
                if (element) {
                    element.textContent = `Xin chào ${fullName}!`;
                }
            });
        }
        else {
            toastManager.createToast({
                message: result.message || "Cập nhật thất bại",
                type: "error",
            });
        }
    }
    catch (error) {
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ!",
            type: "error",
        });
    }
    finally {
        submitButton.disabled = false;
        submitButton.innerHTML = "Lưu thay đổi";
    }
}
async function handleUpdatePassword(e) {
    e.preventDefault();
    const formData = new FormData(passwordForm);
    const submitButton = passwordForm.querySelector('button[type="submit"]');
    if (formData.get("new_password") !== formData.get("confirm_password")) {
        toastManager.createToast({
            message: "Mật khẩu mới không khớp!",
            type: "error",
        });
        return;
    }
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang lưu...`;
    try {
        const response = await authService.fetchWithAuth("/api/users/update-password", {
            method: "POST",
            body: formData,
        });
        const result = await response.json();
        if (result.success) {
            toastManager.createToast({ message: result.message, type: "success" });
            passwordForm.reset();
        }
        else {
            toastManager.createToast({
                message: result.message || "Cập nhật thất bại",
                type: "error",
            });
        }
    }
    catch (error) {
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ!",
            type: "error",
        });
    }
    finally {
        submitButton.disabled = false;
        submitButton.innerHTML = "Đổi mật khẩu";
    }
}
function handleAvatarPreview() {
    const file = avatarInput.files?.[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.src = e.target?.result;
        };
        reader.readAsDataURL(file);
        // Hiển thị nút submit khi đã chọn ảnh
        const submitButton = avatarForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.classList.remove("hidden");
        }
    }
}
async function handleAvatarUpload(e) {
    e.preventDefault();
    const file = avatarInput.files?.[0];
    if (!file) {
        toastManager.createToast({
            message: "Vui lòng chọn một ảnh!",
            type: "warning",
        });
        return;
    }
    const formData = new FormData();
    formData.append("avatar", file);
    const submitButton = avatarForm.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang tải lên...`;
    try {
        const response = await authService.fetchWithAuth("/api/users/update-avatar", {
            method: "POST",
            body: formData,
        });
        const result = await response.json();
        if (result.success) {
            toastManager.createToast({ message: result.message, type: "success" });
            // The API now returns the correct public path
            const newAvatarUrl = `/public${result.data.avatar_path}`;
            // Update all avatar images on the page
            const avatarImages = document.querySelectorAll("#header-avatar-image, #dashboard-header-avatar-image, #avatar-preview");
            avatarImages.forEach((img) => {
                if (img)
                    img.src = newAvatarUrl;
            });
            // Ẩn nút submit sau khi upload thành công
            submitButton.classList.add("hidden");
            // Reset input file
            avatarInput.value = "";
        }
        else {
            toastManager.createToast({
                message: result.message || "Tải lên thất bại",
                type: "error",
            });
        }
    }
    catch (error) {
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ!",
            type: "error",
        });
    }
    finally {
        submitButton.disabled = false;
        submitButton.innerHTML = "Lưu ảnh";
    }
}
