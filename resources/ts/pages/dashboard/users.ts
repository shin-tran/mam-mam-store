import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";

// --- DOM Elements ---
const usersTable = document.getElementById("users-table");

// Edit Modal Elements
const editUserModal = document.getElementById(
  "edit_user_modal"
) as HTMLDialogElement;
const editUserForm = document.getElementById(
  "edit-user-form"
) as HTMLFormElement;
const editUserIdInput = document.getElementById(
  "edit-user-id"
) as HTMLInputElement;
const editRoleInput = document.getElementById("edit-role") as HTMLSelectElement;
const editFullNameInput = document.getElementById(
  "edit-full-name"
) as HTMLInputElement;
const editEmailInput = document.getElementById(
  "edit-email"
) as HTMLInputElement;
const editIsActivatedInput = document.getElementById(
  "edit-is-activated"
) as HTMLSelectElement;
const editNewPasswordInput = document.getElementById(
  "edit-new-password"
) as HTMLInputElement;
const editConfirmPasswordInput = document.getElementById(
  "edit-confirm-password"
) as HTMLInputElement;

// Delete Modal Elements
const deleteUserModal = document.getElementById(
  "delete_user_modal"
) as HTMLDialogElement;
const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
const userNameToDeleteSpan = document.getElementById("user-name-to-delete");

let userIdToDelete: string | null = null;
let userIdToEdit: string | null = null;

// --- Event Listeners ---
usersTable?.addEventListener("click", (event) => {
  const target = event.target as HTMLElement;
  const deleteButton = target.closest(".btn-delete");
  const editButton = target.closest(".btn-edit");

  if (deleteButton) handleDeleteButtonClick(deleteButton);
  if (editButton) handleEditButtonClick(editButton);
});

confirmDeleteBtn?.addEventListener("click", handleDeleteConfirm);
editUserForm?.addEventListener("submit", handleEditFormSubmit);

// --- Handler Functions ---
function handleEditButtonClick(button: Element) {
  userIdToEdit = button.getAttribute("data-user-id");
  const userName = button.getAttribute("data-user-name");
  const userEmail = button.getAttribute("data-user-email");
  const userRole = button.getAttribute("data-user-role");
  const userActivated = button.getAttribute("data-user-activated");

  if (!userIdToEdit || !editUserModal) return;

  // Điền thông tin vào modal
  if (editUserIdInput) editUserIdInput.value = userIdToEdit;
  if (editFullNameInput) editFullNameInput.value = userName || "";
  if (editEmailInput) editEmailInput.value = userEmail || "";
  if (editIsActivatedInput) editIsActivatedInput.value = userActivated || "1";

  // Set role select
  if (editRoleInput && userRole) {
    const roleOptions = editRoleInput.querySelectorAll("option");
    roleOptions.forEach((option) => {
      if (option.textContent?.toLowerCase().trim() === userRole.toLowerCase()) {
        option.selected = true;
      }
    });
  }

  // Clear password fields
  if (editNewPasswordInput) editNewPasswordInput.value = "";
  if (editConfirmPasswordInput) editConfirmPasswordInput.value = "";

  editUserModal.showModal();
}

async function handleEditFormSubmit(event: SubmitEvent) {
  event.preventDefault();
  if (!userIdToEdit) return;

  // Validate password fields
  const newPassword = editNewPasswordInput?.value || "";
  const confirmPassword = editConfirmPasswordInput?.value || "";

  if (newPassword || confirmPassword) {
    if (newPassword.length < 6) {
      toastManager.createToast({
        message: "Mật khẩu phải có ít nhất 6 ký tự!",
        type: "error",
      });
      return;
    }

    if (newPassword !== confirmPassword) {
      toastManager.createToast({
        message: "Mật khẩu xác nhận không khớp!",
        type: "error",
      });
      return;
    }
  }

  const submitButton = (
    event.target as HTMLFormElement
  ).querySelector<HTMLButtonElement>('button[type="submit"]');
  if (submitButton) {
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang lưu...`;
  }

  const formData = new FormData(editUserForm);

  // Remove password fields if empty
  if (!newPassword) {
    formData.delete("new_password");
    formData.delete("confirm_password");
  }

  try {
    const response = await authService.fetchWithAuth(
      `/api/users/update/${userIdToEdit}`,
      {
        method: "POST",
        body: formData,
      }
    );
    const result = await response.json();

    if (result.success) {
      toastManager.createToast({
        message: "Cập nhật người dùng thành công!",
        type: "success",
      });
      window.location.reload()
    } else {
      toastManager.createToast({ message: result.message, type: "error" });
    }
  } catch (error) {
    console.error("Lỗi khi cập nhật người dùng:", error);
    toastManager.createToast({
      message: "Lỗi kết nối máy chủ!",
      type: "error",
    });
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.innerHTML = "Lưu thay đổi";
    }
    userIdToEdit = null;
  }
}

function handleDeleteButtonClick(button: Element) {
  userIdToDelete = button.getAttribute("data-user-id");
  const userName = button.getAttribute("data-user-name");

  if (userNameToDeleteSpan) {
    userNameToDeleteSpan.textContent = userName;
  }
  deleteUserModal?.showModal();
}

async function handleDeleteConfirm(event: Event) {
  event.preventDefault();
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
    userIdToDelete = null;
  }
}
