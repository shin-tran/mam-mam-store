# Validator Helper - Hướng dẫn sử dụng

## Giới thiệu

Class `Validator` giúp xử lý validation dữ liệu một cách đơn giản và tránh lặp code.

## Cách sử dụng cơ bản

### 1. Import class

```php
use App\Helpers\Validator;
```

### 2. Tạo instance và validate

```php
$validator = Validator::make($_POST);

$validator->required('email', 'Email không được để trống!')
          ->email('email')
          ->required('password', 'Mật khẩu không được để trống!')
          ->minLength('password', 6);

if ($validator->fails()) {
    // Có lỗi validation
    $errors = $validator->getErrors();
    // Xử lý lỗi...
}
```

## Các method validation có sẵn

### required($field, $message = null)

Kiểm tra field có giá trị không (không rỗng)

```php
$validator->required('full_name', 'Họ tên không được bỏ trống!');
```

### minLength($field, $length, $message = null)

Kiểm tra độ dài tối thiểu

```php
$validator->minLength('password', 6, 'Mật khẩu phải có ít nhất 6 ký tự!');
```

### maxLength($field, $length, $message = null)

Kiểm tra độ dài tối đa

```php
$validator->maxLength('username', 50, 'Tên người dùng không được quá 50 ký tự!');
```

### email($field, $message = null)

Kiểm tra định dạng email

```php
$validator->email('email', 'Email không hợp lệ!');
```

### emailUnique($field, $message = null)

Kiểm tra email đã tồn tại trong database chưa

```php
$validator->emailUnique('email', 'Email đã được sử dụng!');
```

### phone($field, $message = null)

Kiểm tra định dạng số điện thoại Việt Nam

```php
$validator->phone('phone_number', 'Số điện thoại không hợp lệ!');
```

### emailOrPhone($field, $message = null)

Kiểm tra giá trị là email hoặc số điện thoại hợp lệ

```php
$validator->emailOrPhone('contact', 'Email hoặc số điện thoại không hợp lệ!');
```

### matches($field, $matchField, $message = null)

Kiểm tra 2 field có giá trị giống nhau không

```php
$validator->matches('confirm_password', 'password', 'Mật khẩu không khớp!');
```

### numeric($field, $message = null)

Kiểm tra giá trị có phải là số không

```php
$validator->numeric('price', 'Giá phải là số!');
```

### min($field, $min, $message = null)

Kiểm tra giá trị tối thiểu

```php
$validator->min('age', 18, 'Tuổi phải từ 18 trở lên!');
```

### max($field, $max, $message = null)

Kiểm tra giá trị tối đa

```php
$validator->max('quantity', 100, 'Số lượng không được vượt quá 100!');
```

### custom($field, callable $callback, $message)

Tạo rule validation tùy chỉnh

```php
$validator->custom('username', function($value) {
    return preg_match('/^[a-zA-Z0-9_]+$/', $value);
}, 'Tên người dùng chỉ được chứa chữ, số và dấu gạch dưới!');
```

## Ví dụ thực tế

### Ví dụ 1: Đăng ký tài khoản

```php
public function handleRegister() {
    $validator = Validator::make($_POST);

    $validator->required('full_name', 'Họ tên không được bỏ trống!')
              ->minLength('full_name', 5, 'Họ tên phải có ít nhất 5 ký tự!')
              ->required('email', 'Email không được bỏ trống!')
              ->email('email')
              ->emailUnique('email')
              ->required('password', 'Mật khẩu không được để trống!')
              ->minLength('password', 6, 'Mật khẩu phải lớn hơn 6 ký tự!')
              ->required('confirm_password', 'Hãy nhập lại mật khẩu!')
              ->matches('confirm_password', 'password', 'Mật khẩu không khớp!');

    if ($validator->fails()) {
        Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', $validator->getErrors(), 422);
    }

    // Xử lý đăng ký...
}
```

### Ví dụ 2: Đăng nhập

```php
public function handleLogin() {
    $validator = Validator::make($_POST);

    $validator->required('email', 'Email không được bỏ trống!')
              ->email('email')
              ->required('password', 'Mật khẩu không được để trống!');

    if ($validator->fails()) {
        Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', $validator->getErrors(), 422);
    }

    // Xử lý đăng nhập...
}
```

### Ví dụ 3: Tạo sản phẩm với validation tùy chỉnh

```php
public function createProduct() {
    $validator = Validator::make($_POST);

    $validator->required('name', 'Tên sản phẩm không được để trống!')
              ->minLength('name', 3)
              ->maxLength('name', 200)
              ->required('price', 'Giá không được để trống!')
              ->numeric('price')
              ->min('price', 0, 'Giá phải lớn hơn 0!')
              ->required('quantity')
              ->numeric('quantity')
              ->min('quantity', 1)
              ->custom('sku', function($value) {
                  // SKU phải là duy nhất
                  $productModel = new Product();
                  return !$productModel->skuExists($value);
              }, 'Mã SKU đã tồn tại!');

    if ($validator->fails()) {
        Helpers::sendJsonResponse(false, 'Dữ liệu không hợp lệ.', $validator->getErrors(), 422);
    }

    // Xử lý tạo sản phẩm...
}
```

## Các method tiện ích

### fails()

Kiểm tra có lỗi validation không

```php
if ($validator->fails()) {
    // Có lỗi
}
```

### passes()

Kiểm tra validation có pass không (ngược với fails)

```php
if ($validator->passes()) {
    // Không có lỗi
}
```

### getErrors()

Lấy tất cả lỗi validation

```php
$errors = $validator->getErrors();
// ['email' => ['Email không hợp lệ!'], 'password' => ['Mật khẩu quá ngắn!']]
```

### getError($field)

Lấy lỗi của một field cụ thể

```php
$emailErrors = $validator->getError('email');
// ['Email không hợp lệ!']
```

### addError($field, $message)

Thêm lỗi thủ công

```php
$validator->addError('email', 'Email này đã bị khóa!');
```

## Chaining Methods

Tất cả các validation methods đều return `$this` nên có thể chain liên tiếp:

```php
$validator->required('email')
          ->email('email')
          ->emailUnique('email')
          ->required('password')
          ->minLength('password', 6);
```

## Lưu ý

- Nếu không truyền `$message`, validator sẽ sử dụng message mặc định
- Validation chỉ chạy khi field tồn tại trong data (trừ `required`)
- Có thể kết hợp nhiều rule cho cùng một field
- Message có thể là `null` để sử dụng message mặc định
