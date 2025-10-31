<?php
namespace App\Helpers;

use App\Models\User;

class Validator {
  private array $errors = [];
  private array $data = [];

  public function __construct(array $data = []) {
    $this->data = $data;
  }

  /**
   * Validate required field
   */
  public function required(string $field, ?string $message = null): self {
    if (empty($this->data[$field])) {
      $this->errors[$field][] = $message ?? ucfirst($field)." không được để trống!";
    }
    return $this;
  }

  /**
   * Validate minimum length
   */
  public function minLength(string $field, int $length, ?string $message = null): self {
    if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
      $this->errors[$field][] = $message ?? ucfirst($field)." phải có ít nhất {$length} ký tự!";
    }
    return $this;
  }

  /**
   * Validate maximum length
   */
  public function maxLength(string $field, int $length, ?string $message = null): self {
    if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
      $this->errors[$field][] = $message ?? ucfirst($field)." không được vượt quá {$length} ký tự!";
    }
    return $this;
  }

  /**
   * Validate email format
   */
  public function email(string $field, ?string $message = null): self {
    if (isset($this->data[$field]) && !empty($this->data[$field])) {
      if (!Helpers::validateEmail($this->data[$field])) {
        $this->errors[$field][] = $message ?? "Email không hợp lệ!";
      }
    }
    return $this;
  }

  /**
   * Check if email already exists in database
   */
  public function emailUnique(string $field, ?string $message = null): self {
    if (isset($this->data[$field]) && !empty($this->data[$field])) {
      $userModel = new User();
      if ($userModel->emailExists($this->data[$field])) {
        $this->errors[$field][] = $message ?? "Email đã tồn tại!";
      }
    }
    return $this;
  }

  /**
   * Validate phone number
   */
  public function phone(string $field, ?string $message = null): self {
    if (isset($this->data[$field]) && !empty($this->data[$field])) {
      if (!Helpers::isPhone($this->data[$field])) {
        $this->errors[$field][] = $message ?? "Số điện thoại không hợp lệ!";
      }
    }
    return $this;
  }

  /**
   * Validate email or phone
   */
  public function emailOrPhone(string $field, ?string $message = null): self {
    if (isset($this->data[$field]) && !empty($this->data[$field])) {
      $value = $this->data[$field];
      $isValid = false;

      if (is_numeric($value)) {
        $isValid = Helpers::isPhone($value);
        if (!$isValid) {
          $this->errors[$field][] = $message ?? "Số điện thoại không hợp lệ!";
        }
      } else {
        $isValid = Helpers::validateEmail($value);
        if (!$isValid) {
          $this->errors[$field][] = $message ?? "Email không hợp lệ!";
        }
      }
    }
    return $this;
  }

  /**
   * Validate field matches another field
   */
  public function matches(string $field, string $matchField, ?string $message = null): self {
    if (isset($this->data[$field]) && isset($this->data[$matchField])) {
      if ($this->data[$field] !== $this->data[$matchField]) {
        $this->errors[$field][] = $message ?? ucfirst($field)." không khớp!";
      }
    }
    return $this;
  }

  /**
   * Validate numeric value
   */
  public function numeric(string $field, ?string $message = null): self {
    if (isset($this->data[$field]) && !empty($this->data[$field])) {
      if (!is_numeric($this->data[$field])) {
        $this->errors[$field][] = $message ?? ucfirst($field)." phải là số!";
      }
    }
    return $this;
  }

  /**
   * Validate minimum value
   */
  public function min(string $field, $min, ?string $message = null): self {
    if (isset($this->data[$field]) && !empty($this->data[$field])) {
      if ($this->data[$field] < $min) {
        $this->errors[$field][] = $message ?? ucfirst($field)." phải lớn hơn hoặc bằng {$min}!";
      }
    }
    return $this;
  }

  /**
   * Validate maximum value
   */
  public function max(string $field, $max, ?string $message = null): self {
    if (isset($this->data[$field]) && !empty($this->data[$field])) {
      if ($this->data[$field] > $max) {
        $this->errors[$field][] = $message ?? ucfirst($field)." phải nhỏ hơn hoặc bằng {$max}!";
      }
    }
    return $this;
  }

  /**
   * Add custom validation rule
   */
  public function custom(string $field, callable $callback, string $message): self {
    if (isset($this->data[$field])) {
      $isValid = $callback($this->data[$field]);
      if (!$isValid) {
        $this->errors[$field][] = $message;
      }
    }
    return $this;
  }

  /**
   * Check if validation has errors
   */
  public function fails(): bool {
    return !empty($this->errors);
  }

  /**
   * Check if validation passed
   */
  public function passes(): bool {
    return empty($this->errors);
  }

  /**
   * Get all validation errors
   */
  public function getErrors(): array {
    return $this->errors;
  }

  /**
   * Get errors for a specific field
   */
  public function getError(string $field): ?array {
    return $this->errors[$field] ?? null;
  }

  /**
   * Add a custom error message
   */
  public function addError(string $field, string $message): self {
    $this->errors[$field][] = $message;
    return $this;
  }

  /**
   * Static method to create validator instance
   */
  public static function make(array $data): self {
    return new self($data);
  }
}
