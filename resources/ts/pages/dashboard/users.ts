import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";

const usersTable = document.getElementById("users-table");
const deleteUserModal = document.getElementById(
  "delete_user_modal"
) as HTMLDialogElement;
const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
const userNameToDeleteSpan = document.getElementById("user-name-to-delete");

let userIdToDelete: string | null = null;

// Sử dụng event delegation để xử lý click trên toàn bộ bảng
usersTable?.addEventListener("click", (event) => {
  const target = event.target as HTMLElement;
  const deleteButton = target.closest(".btn-delete");

  if (deleteButton) {
    userIdToDelete = deleteButton.getAttribute("data-user-id");
    const userName = deleteButton.getAttribute("data-user-name");

    if (userNameToDeleteSpan) {
      userNameToDeleteSpan.textContent = userName;
    }
    deleteUserModal?.showModal();
  }
});

// Xử lý khi nhấn nút xác nhận xóa trong modal
confirmDeleteBtn?.addEventListener("click", async (event) => {
  event.preventDefault(); // Ngăn form tự đóng modal ngay lập tức
  if (!userIdToDelete) return;

  const button = event.target as HTMLButtonElement;
  button.disabled = true;
  button.innerHTML = `<span class="loading loading-spinner"></span> Đang xóa...`;

  try {
    const response = await authService.fetchWithAuth(
      `/api/users/delete/${userIdToDelete}`,
      {
        method: "POST",
      }
    );
    const result = await response.json();

    if (result.success) {
      toastManager.createToast({ message: result.message, type: "success" });
      // Xóa hàng trong bảng khỏi giao diện
      const rowToDelete = usersTable?.querySelector(
        `tr[data-user-id="${userIdToDelete}"]`
      );
      rowToDelete?.remove();
    } else {
      toastManager.createToast({ message: result.message, type: "error" });
    }
  } catch (error) {
    console.error("Lỗi khi xóa người dùng:", error);
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
    button.disabled = false;
    button.innerHTML = "Xóa";
    deleteUserModal.close();
    userIdToDelete = null; // Reset ID
  }
});
