import { FULL_URL } from "../app";
export class Helpers {
    static validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    static isPhone(phone) {
        let cleaned = phone.replace(/[^0-9+]/g, "");
        if (cleaned.startsWith("+84"))
            cleaned = "0" + cleaned.slice(3);
        const regex = /^(0)(3[2-9]|5[689]|7[06-9]|8[1-689]|9[0-46-9])[0-9]{7}$/;
        return regex.test(cleaned);
    }
    static displayError(fieldName, message) {
        const errorElement = document.querySelector(`.error-log[data-field="${fieldName}"]`);
        if (errorElement) {
            message === ""
                ? errorElement.classList.add("hidden")
                : errorElement.classList.remove("hidden");
            errorElement.textContent = message;
        }
    }
    static clearError(fieldName) {
        this.displayError(fieldName, "");
    }
    static redirect(path = "") {
        window.location.href = `${FULL_URL}${path}`;
    }
}
