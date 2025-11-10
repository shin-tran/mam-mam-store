/**
 * Product interface from API
 */
export interface Product {
  id: number;
  product_name: string;
  description: string;
  price: string;
  image_path: string;
  stock_quantity: number;
  category_id: number;
  category_name: string;
  inventory_type: "in_stock" | "out_of_stock" | "pre_order";
  created_at: string;
  updated_at: string;
}

/**
 * Cart item interface (Product + quantity)
 */
export interface CartItem extends Product {
  quantity: number;
}

/**
 * Cart item in localStorage
 */
export interface LocalStorageCartItem {
  productId: string;
  quantity: number;
}

/**
 * Product detail (simplified version)
 */
export interface ProductDetail {
  id: number;
  product_name: string;
  price: number;
  image_path: string;
  stock_quantity: number;
}
