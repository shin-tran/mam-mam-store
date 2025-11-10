/**
 * Success API Response
 */
interface SuccessResponse<T> {
  success: true;
  message: string;
  data: T;
}

/**
 * Error API Response
 */
interface ErrorResponse<K> {
  success: false;
  message: string;
  errors?: K;
}

/**
 * Generic API Response type
 */
export type ApiResponse<T, K = Record<string, string[]>> =
  | SuccessResponse<T>
  | ErrorResponse<K>;
