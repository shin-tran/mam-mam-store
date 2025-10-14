import { FULL_URL } from "../../app.js";
import { authService } from "../../services/auth-service.js";
const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
const token = urlParams.get("token");
const activateNoti = document.getElementById("activate-noti");
try {
    if (token) {
        const formData = new FormData();
        formData.append("token", token);
        const result = await authService.activateAccount(formData);
        if (result.success) {
            const anchorEle = document.createElement("a");
            const btnLogin = document.createElement("button");
            anchorEle.href = `${FULL_URL}/login`;
            btnLogin.type = "button";
            btnLogin.classList.add("btn", "w-full");
            btnLogin.textContent = "Đến trang đăng nhập";
            anchorEle.appendChild(btnLogin);
            activateNoti.after(anchorEle);
        }
        activateNoti.textContent = result.message;
    }
    else
        activateNoti.textContent = "Token xác thực không được cung cấp.";
}
catch (error) {
    console.log(error);
}
