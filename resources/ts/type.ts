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

export interface CartItem {
  productId: string;
  quantity: number;
}

export interface ProductDetail {
  id: number;
  product_name: string;
  price: number;
  image_path: string;
  stock_quantity: number;
}

export interface Address {
  id: number;
  recipient_name: string;
  phone_number: string;
  street_address: string;
  ward: string;
  district: string;
  city: string;
  is_default: number;
}

export interface Province {
  province_id: number;
  code: string;
  name: string;
  type: string;
}

export interface Ward {
  ward_id: number;
  code: string;
  name: string;
  type: string;
  province_code: string;
}
