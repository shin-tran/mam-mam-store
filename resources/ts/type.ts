export type ToastType = "success" | "error" | "info" | "warning";

export interface RegisterFormData {
  full_name: string;
  email: string;
  password: string;
  confirm_password: string;
  phone_number?: string;
}

export interface LoginFormData {
  email: string;
  password: string;
}

interface SuccessResponse<T> {
  success: true;
  message: string;
  data: T;
}

interface ErrorResponse<K> {
  success: false;
  message: string;
  errors?: K;
}

export type ApiResponse<T, K = Record<string, string[]>> =
  | SuccessResponse<T>
  | ErrorResponse<K>;

export interface LoginResponse {
  access_token: string;
  expires_in: number;
}
