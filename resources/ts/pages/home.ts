import { authService } from "../services/auth-service";

const logoutBtn = document.getElementById("btn-logout");

logoutBtn?.addEventListener("click", async () => {
  await authService.logout();
});
