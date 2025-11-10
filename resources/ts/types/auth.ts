/**
 * Register form data
 */
export interface RegisterFormData {
  full_name: string;
  email: string;
  password: string;
  confirm_password: string;
  phone_number?: string;
}

/**
 * Login form data
 */
export interface LoginFormData {
  email: string;
  password: string;
}

/**
 * Login response from API
 */
export interface LoginResponse {
  access_token: string;
  expires_in: number;
}
