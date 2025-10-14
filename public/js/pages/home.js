import { authService } from "../services/auth-service.js";
const logoutBtn = document.getElementById("btn-logout");
logoutBtn?.addEventListener("click", async () => {
    await authService.logout();
});
