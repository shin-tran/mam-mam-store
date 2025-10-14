import { AppConfig } from "../app.js";
import type { ApiResponse, LoginResponse } from "../type.js";
import { Helpers } from "../utils/helpers.js";

class AuthService {
  private accessToken: string | null = null;
  private refreshTokenPromise: Promise<any> | null = null;
  private refreshTimeoutId: ReturnType<typeof setTimeout> | null = null;

  constructor() {
    this.accessToken = localStorage.getItem("access_token");
    this.scheduleNextTokenRefresh();
  }

  async checkEmailExists(email: string) {
    const formData = new FormData();
    formData.append("email", email);
    const url = `${AppConfig.BASE_URL}/api/check-email`;
    try {
      const result: ApiResponse<{ exists: boolean }> = await fetch(url, {
        method: "post",
        body: formData,
      }).then((res) => res.json());
      if (result.success) return result.data.exists;
      return false;
    } catch (error) {
      console.error(error);
    }
  }

  async checkPhoneNumberExists(phoneNumber: string) {
    const formData = new FormData();
    formData.append("phone_number", phoneNumber);
    const url = `${AppConfig.BASE_URL}/api/check-phone-number`;
    try {
      const result: ApiResponse<{ exists: boolean }> = await fetch(url, {
        method: "post",
        body: formData,
      }).then((res) => res.json());
      if (result.success) return result.data.exists;
      return false;
    } catch (error) {
      console.error(error);
    }
  }

  isLoggedIn(): boolean {
    return this.accessToken !== null;
  }

  async register(formData: FormData) {
    const url = `${AppConfig.BASE_URL}/api/register`;

    const result: ApiResponse<null> = await fetch(url, {
      method: "post",
      body: formData,
    }).then((res) => res.json());

    return result;
  }

  async activateAccount(formData: FormData) {
    const url = `${AppConfig.BASE_URL}/api/activate`;

    const result: ApiResponse<null> = await fetch(url, {
      method: "post",
      body: formData,
    }).then((res) => res.json());

    return result;
  }

  async login(formData: FormData) {
    const url = `${AppConfig.BASE_URL}/api/login`;

    const result: ApiResponse<LoginResponse> = await fetch(url, {
      method: "post",
      body: formData,
    }).then((res) => res.json());

    if (result.success) {
      this.accessToken = result.data.access_token;
      localStorage.setItem("access_token", result.data.access_token);
      localStorage.setItem("token_expires_at", String(result.data.expires_in));
    }
    return result;
  }

  async forgotPassword(formData: FormData) {
    const url = `${AppConfig.BASE_URL}/api/forgot-password`;

    const result: ApiResponse<null> = await fetch(url, {
      method: "post",
      body: formData,
    }).then((res) => res.json());

    return result;
  }

  async resetPassword(formData: FormData) {
    const url = `${AppConfig.BASE_URL}/api/reset-password`;

    const result: ApiResponse<null> = await fetch(url, {
      method: "post",
      body: formData,
    }).then((res) => res.json());

    return result;
  }

  async logout() {
    const accessToken = this.accessToken;

    this.accessToken = null;
    this.stopTokenRefreshTimer();
    localStorage.removeItem("access_token");
    localStorage.removeItem("token_expires_at");

    try {
      const url = `${AppConfig.BASE_URL}/api/logout`;
      await fetch(url, {
        method: "post",
        headers: {
          Authorization: `Bearer ${accessToken}`,
        },
      }).then((res) => res.json());
    } catch (error) {
      console.error("Lỗi khi gọi API logout:", error);
    }
    Helpers.redirect("/login");
  }

  async fetchWithAuth(
    url: string,
    options: RequestInit = {}
  ): Promise<Response> {
    const token = this.accessToken;

    const headers = new Headers(options.headers || {});
    if (token) {
      headers.set("Authorization", `Bearer ${token}`);
    }
    options.headers = headers;

    let response = await fetch(url, options);

    if (response.status === 401) {
      try {
        const newAccessToken = await this.refreshToken();
        headers.set("Authorization", `Bearer ${newAccessToken}`);
        options.headers = headers;

        console.log("Token refreshed. Retrying the original request...");
        response = await fetch(url, options);
      } catch (error) {
        console.error("Lấy token thất bại. Đăng xuất.", error);
        this.logout();
        Helpers.redirect("/login");
      }
    }

    return response;
  }

  private async refreshToken(): Promise<string> {
    if (!this.refreshTokenPromise) {
      const url = `${AppConfig.BASE_URL}/api/refresh-token`;
      this.refreshTokenPromise = fetch(url, { method: "POST" })
        .then(async (res) => {
          if (!res.ok)
            throw new Error("Refresh token không hợp lệ hoặc đã hết hạn.");
          const result: ApiResponse<LoginResponse> = await res.json();
          if (result.success) {
            this.accessToken = result.data.access_token;
            localStorage.setItem("access_token", this.accessToken!);
            localStorage.setItem(
              "token_expires_at",
              String(result.data.expires_in)
            );
            this.scheduleNextTokenRefresh();
            return this.accessToken;
          } else {
            throw new Error(
              result.message || "Đã xảy ra lỗi từ refresh token API"
            );
          }
        })
        .finally(() => {
          this.refreshTokenPromise = null;
        });
    }
    return this.refreshTokenPromise;
  }

  private stopTokenRefreshTimer() {
    if (this.refreshTimeoutId) {
      clearTimeout(this.refreshTimeoutId);
      this.refreshTimeoutId = null;
    }
  }

  private async scheduleNextTokenRefresh() {
    this.stopTokenRefreshTimer();

    const expiresAtString = localStorage.getItem("token_expires_at");
    if (!expiresAtString) return;

    const expiresAt = parseInt(expiresAtString, 10) * 1000; // Chuyển sang mili-giây
    const now = Date.now();

    const safeMargin = 60 * 1000; // 1 phút

    const timeoutDuration = expiresAt - now - safeMargin;

    if (timeoutDuration > 0) {
      this.refreshTimeoutId = setTimeout(async () => {
        try {
          await this.refreshToken();
        } catch (error) {
          this.logout();
        }
      }, timeoutDuration);
    } else {
      try {
        await this.refreshToken();
      } catch (error) {
        this.logout();
      }
    }
  }
}

export const authService = new AuthService();
