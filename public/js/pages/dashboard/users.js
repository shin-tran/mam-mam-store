import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";
// --- DOM Elements ---
const usersTable = document.getElementById("users-table");
// Edit Modal Elements
const editUserModal = document.getElementById("edit_user_modal");
const editUserForm = document.getElementById("edit-user-form");
const editUserIdInput = document.getElementById("edit-user-id");
const editRoleInput = document.getElementById("edit-role");
const editFullNameInput = document.getElementById("edit-full-name");
const editPhoneNumberInput = document.getElementById("edit-phone-number");
const editAddressTextarea = document.getElementById("edit-address");
// Delete Modal Elements
const deleteUserModal = document.getElementById("delete_user_modal");
const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
const userNameToDeleteSpan = document.getElementById("user-name-to-delete");
let userIdToDelete = null;
let userIdToEdit = null;
// --- Event Listeners ---
usersTable?.addEventListener("click", (event) => {
    const target = event.target;
    const deleteButton = target.closest(".btn-delete");
    const editButton = target.closest(".btn-edit");
    if (deleteButton)
        handleDeleteButtonClick(deleteButton);
    if (editButton)
        handleEditButtonClick(editButton);
});
confirmDeleteBtn?.addEventListener("click", handleDeleteConfirm);
editUserForm?.addEventListener("submit", handleEditFormSubmit);
// --- Handler Functions ---
function handleEditButtonClick(button) {
    userIdToEdit = button.getAttribute("data-user-id");
    const userName = button.getAttribute("data-user-name");
    const userPhone = button.getAttribute("data-user-phone") || "";
    const userAddress = button.getAttribute("data-user-address") || "";
    // Không thực hiện khi không có những thứ sau
    if (!userIdToEdit || !editUserModal || !editUserIdInput || !editRoleInput)
        return;
    // Điền thông tin vào modal
    editUserIdInput.value = userIdToEdit;
    editFullNameInput.value = userName || "";
    editPhoneNumberInput.value = userPhone;
    editAddressTextarea.value = userAddress;
    editUserModal.showModal();
}
async function handleEditFormSubmit(event) {
    event.preventDefault();
    if (!userIdToEdit)
        return;
    const submitButton = event.target.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = `<span class="loading loading-spinner"></span> Đang lưu...`;
    }
    const formData = new FormData(editUserForm);
    try {
        const response = await authService.fetchWithAuth(`/api/users/update/${userIdToEdit}`, {
            method: "POST",
            body: formData,
        });
        const result = await response.json();
        if (result.success) {
            window.location.reload();
        }
        else {
            toastManager.createToast({ message: result.message, type: "error" });
        }
    }
    catch (error) {
        console.error("Lỗi khi cập nhật người dùng:", error);
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ!",
            type: "error",
        });
    }
    finally {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = "Lưu thay đổi";
        }
        userIdToEdit = null;
    }
}
function handleDeleteButtonClick(button) {
    userIdToDelete = button.getAttribute("data-user-id");
    const userName = button.getAttribute("data-user-name");
    if (userNameToDeleteSpan) {
        userNameToDeleteSpan.textContent = userName;
    }
    deleteUserModal?.showModal();
}
async function handleDeleteConfirm(event) {
    event.preventDefault();
    if (!userIdToDelete)
        return;
    const button = event.target;
    button.disabled = true;
    button.innerHTML = `<span class="loading loading-spinner"></span> Đang xóa...`;
    try {
        const response = await authService.fetchWithAuth(`/api/users/delete/${userIdToDelete}`, {
            method: "POST",
        });
        const result = await response.json();
        if (result.success) {
            toastManager.createToast({ message: result.message, type: "success" });
            const rowToDelete = usersTable?.querySelector(`tr[data-user-id="${userIdToDelete}"]`);
            rowToDelete?.remove();
        }
        else {
            toastManager.createToast({ message: result.message, type: "error" });
        }
    }
    catch (error) {
        console.error("Lỗi khi xóa người dùng:", error);
        toastManager.createToast({
            message: "Lỗi kết nối máy chủ!",
            type: "error",
        });
    }
    finally {
        button.disabled = false;
        button.innerHTML = "Xóa";
        deleteUserModal.close();
        userIdToDelete = null;
    }
}
