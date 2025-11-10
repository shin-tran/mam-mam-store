/**
 * User address interface
 */
export interface Address {
  id: number;
  recipient_name: string;
  phone_number: string;
  street_address: string;
  ward: string;
  district?: string;
  city: string;
  is_default: number;
  created_at?: string;
  updated_at?: string;
}

/**
 * Province interface from external API
 */
export interface Province {
  province_id: number;
  code: string;
  name: string;
  type: string;
}

/**
 * Ward interface from external API
 */
export interface Ward {
  ward_id: number;
  code: string;
  name: string;
  type: string;
  province_code: string;
}
