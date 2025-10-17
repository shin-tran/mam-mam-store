import { authService } from "../services/auth-service.js";
import { toastManager } from "../toast-manager.js";

// --- Avatar Upload ---
const avatarUploadInput = document.getElementById(
  "avatar-upload"
) as HTMLInputElement;
const avatarPreview = document.getElementById(
  "avatar-preview"
) as HTMLImageElement;

avatarUploadInput?.addEventListener("change", () => {
  const file = avatarUploadInput.files?.[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      avatarPreview.src = e.target?.result as string;
      // Automatically submit after preview
      uploadAvatar(file);
    };
    reader.readAsDataURL(file);
  }
});

async function uploadAvatar(file: File) {
  const formData = new FormData();
  formData.append("avatar", file);

  try {
    toastManager.createToast({ message: "Đang tải ảnh lên...", type: "info" });
    const response = await authService.fetchWithAuth("/api/profile/avatar", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      toastManager.createToast({
        message: "Cập nhật ảnh đại diện thành công!",
        type: "success",
      });
      // Update avatar in header as well
      const headerAvatar = document.querySelector(
        "#nav-user-avatar img"
      ) as HTMLImageElement;
      if (headerAvatar) {
        headerAvatar.src = `/public${result.data.avatar_path}`;
      }
    } else {
      toastManager.createToast({
        message: result.message || "Tải ảnh lên thất bại.",
        type: "error",
      });
    }
  } catch (error) {
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ.",
      type: "error",
    });
  }
}

// --- Profile Info ---
const profileInfoForm = document.getElementById(
  "profile-info-form"
) as HTMLFormElement;
profileInfoForm?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const submitButton = profileInfoForm.querySelector<HTMLButtonElement>(
    'button[type="submit"]'
  );
  if (submitButton) {
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang lưu...`;
  }

  try {
    const formData = new FormData(profileInfoForm);
    const response = await authService.fetchWithAuth("/api/profile/update", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      toastManager.createToast({ message: result.message, type: "success" });
      window.location.reload();
    } else {
      toastManager.createToast({
        message: result.message || "Cập nhật thất bại.",
        type: "error",
      });
    }
  } catch (error) {
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ.",
      type: "error",
    });
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.innerHTML = `Lưu thay đổi`;
    }
  }
});

// --- Change Password ---
const changePasswordForm = document.getElementById(
  "change-password-form"
) as HTMLFormElement;
changePasswordForm?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const submitButton = changePasswordForm.querySelector<HTMLButtonElement>(
    'button[type="submit"]'
  );
  if (submitButton) {
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang xử lý...`;
  }

  try {
    const formData = new FormData(changePasswordForm);
    const response = await authService.fetchWithAuth(
      "/api/profile/change-password",
      {
        method: "POST",
        body: formData,
      }
    );
    const result = await response.json();

    if (result.success) {
      toastManager.createToast({ message: result.message, type: "success" });
      changePasswordForm.reset();
    } else {
      toastManager.createToast({
        message: result.message || "Đổi mật khẩu thất bại.",
        type: "error",
      });
    }
  } catch (error) {
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ.",
      type: "error",
    });
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.innerHTML = `Đổi mật khẩu`;
    }
  }
});
