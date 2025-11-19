# 🛒 Măm Măm Store

Hệ thống quản lý cửa hàng thực phẩm trực tuyến được xây dựng bằng PHP thuần, TypeScript, và TailwindCSS (DaisyUI).

## 📋 Mục lục

- [Yêu cầu hệ thống](#️-yêu-cầu-hệ-thống)
- [Cài đặt môi trường Development](#-cài-đặt-môi-trường-development)
- [Cấu trúc dự án](#️-cấu-trúc-dự-án)
- [Cấu hình](#️-cấu-hình)
- [Chạy dự án](#-chạy-dự-án)
- [Build cho Production](#-build-cho-production)
- [CI/CD Pipeline](#-cicd-pipeline)
- [Deployment](#-deployment)
- [Troubleshooting](#-troubleshooting)

---

## 🖥️ Yêu cầu hệ thống

### Development Environment

- **PHP**: >= 8.0
- **Composer**: >= 2.0
- **Node.js**: >= 18.0
- **NPM/Bun**: Latest version
- **MySQL**: >= 8.0
- **Web Server**: Apache (XAMPP) hoặc Nginx

### Extensions PHP cần thiết

```text
- php-mbstring
- php-pdo
- php-pdo_mysql
- php-openssl
- php-json
- php-curl
```

---

## 🚀 Cài đặt môi trường Development

### Bước 1: Clone dự án

```bash
git clone https://github.com/yourusername/mam-mam-store.git
cd mam-mam-store
```

### Bước 2: Cài đặt dependencies

#### Backend (PHP)

```bash
composer install
```

#### Frontend (TypeScript + TailwindCSS)

```bash
# Với NPM
npm install

# Hoặc với Bun
bun install
```

### Bước 3: Cấu hình Database

Lưu ý: Nếu bạn làm theo cách này thì hay vào `database/schema.sql` và xoá 7 dòng đầu đi.

#### 3.1. Tạo database MySQL

```sql
CREATE DATABASE mam_mam_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 3.2. Import schema và dữ liệu mẫu

```bash
# Import schema
mysql -u root -p mam_mam_store < database/schema.sql

# Import dữ liệu mẫu
# Hãy đăng ký tài khoản rồi vào database chỉnh role thành admin
mysql -u root -p mam_mam_store < database/data.sql
```

### Bước 4: Cấu hình Virtual Host (Tùy chọn nhưng khuyến nghị)

#### Với Apache (XAMPP - Windows)

**Lưu ý**:

- Hãy vào `C:\\xampp\\apache\\conf\\httpd.conf` tìm `Include conf/extra/httpd-vhosts.conf` đã được bật chưa nhé nếu chưa thì bật lên (**loại bỏ #**)
- Hãy chắc rằng **Apache** chạy ở port **80** và **443** nhé

**File:** `C:\xampp\apache\conf\extra\httpd-vhosts.conf`

```apache
<VirtualHost *:80>
    ServerName mammamstore.local
    DocumentRoot "D:/Workspace/php/mam-mam-store"

    <Directory "D:/Workspace/php/mam-mam-store">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "logs/mammamstore-error.log"
    CustomLog "logs/mammamstore-access.log" common
</VirtualHost>
```

**File:** `C:\Windows\System32\drivers\etc\hosts` (chạy Notepad as Administrator)

```txt
127.0.0.1    mammamstore.local
```

Khởi động lại Apache.

#### Với Nginx

**File:** `/etc/nginx/sites-available/mammamstore.local`

```nginx
server {
  listen 80;
  server_name mammamstore.local;
  root /var/www/mam-mam-store/;
  index index.php;

  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }

  location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
  }

  location ~ /\.ht {
    deny all;
  }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/mammamstore.local /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🗂️ Cấu trúc dự án

```txt
mam-mam-store/
├── app/
│   ├── Configs/          # Cấu hình ứng dụng
│   ├── Controllers/      # Controllers (MVC)
│   │   └── Api/         # API Controllers
│   ├── Core/            # Core classes (Router, Database, View)
│   ├── Helpers/         # Helper functions & Validator
│   ├── Middleware/      # Middleware (Auth, Permission, Sanitize)
│   ├── Models/          # Models (Database interactions)
│   └── Views/           # PHP Views
├── database/
│   ├── schema.sql       # Database schema
│   ├── data.sql         # Sample data
│   └── queries.sql      # Useful queries
├── public/              # Public web root
│   ├── index.php        # Entry point
│   ├── css/             # Compiled CSS
│   ├── js/              # Compiled JavaScript
│   └── uploads/         # User uploads
├── resources/           # Source files
│   ├── css/             # Source CSS
│   └── ts/              # TypeScript source
├── vendor/              # Composer dependencies
├── .env                 # Environment variables
├── composer.json        # PHP dependencies
├── package.json         # Node dependencies
└── tsconfig.json        # TypeScript config
```

---

## ⚙️ Cấu hình

### File `.env`

Copy file mẫu và điều chỉnh:

```bash
cp .env.example .env
```

Hoặc tạo file `.env` mới:

```env
# PHPMailer
EMAIL_SENDER = "your-email@gmail.com"
EMAIL_PASSWORD = "your-app-password"

# Database
DB_HOST = "localhost"
DB_PORT = "3306"
DB_DB = "mam_mam_store"
DB_USER = "root"
DB_PASS = ""
DB_DRIVER = "mysql"

# Application
HOST_URL = "http://mammamstore.local"
APP_ENV = "development"  # development or production

# JWT Secrets
ACCESS_TOKEN_SECRET = "your-access-token-secret-change-this"
REFRESH_TOKEN_SECRET = "your-refresh-token-secret-change-this"
DEVICE_LOGIN_LIMIT = 5

# Token Lifetimes
ACCESS_TOKEN_LIFETIME = "1 day"
REFRESH_TOKEN_LIFETIME = "P30D"
ACTIVATE_EMAIL_TOKEN_LIFETIME = "PT10M"
FORGOT_PASSWORD_TOKEN_LIFETIME = "PT5M"

# Default Role
DEFAULT_USER_ROLE = "customer"
```

### File `resources/ts/app.ts`

```typescript
export const AppConfig = {
  BASE_URL: window.location.origin, // Tự động lấy từ URL hiện tại
};
```

---

## 🎯 Chạy dự án

### Development Mode

#### 1. Start Database Server (MySQL)

Khởi động MySQL qua XAMPP Control Panel hoặc:

```bash
# Linux/Mac
sudo systemctl start mysql

# Windows (XAMPP)
# Mở XAMPP Control Panel và click Start MySQL
```

#### 2. Start Web Server

**Với Apache (XAMPP):**

- Mở XAMPP Control Panel
- Click **Start** Apache

**Với PHP Built-in Server (Không khuyến nghị):**

```bash
php -S localhost:8000 -t public
```

#### 3. Compile TypeScript & TailwindCSS (Watch mode)

```bash
# Với NPM
npm run dev

# Với Bun
bun run dev
```

Lệnh này sẽ:

- Compile TypeScript → JavaScript
- Compile TailwindCSS → CSS
- Watch file changes và tự động rebuild

#### 4. Truy cập ứng dụng

```txt
http://mammamstore.local
# Hoặc
http://localhost/mam-mam-store/public
```

---

## 📦 Build cho Production

### Bước 1: Build assets

```bash
# Với NPM
npm run build

# Với Bun
bun run build
```

### Bước 2: Cập nhật cấu hình

**File `.env`:**

```env
APP_ENV = "production"
HOST_URL = "https://yourdomain.com"
```

**File `app.ts`:** (Đã tự động dùng `window.location.origin`)

### Bước 3: Optimize Composer

```bash
composer install --no-dev --optimize-autoloader
```

### Bước 4: Clear cache (nếu có)

```bash
# Xóa các file cache, logs không cần thiết
rm -rf logs/*.log
```

---

## 🔄 CI/CD Pipeline

Tham khảo upload lên hosting thông qua FTP [ở đây](.github/workflows/deploy.yml)

### GitHub Actions Workflow

Tạo file `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  build-and-deploy:
    runs-on: ubuntu-latest

    steps:
    # Checkout code
    - name: Checkout repository
      uses: actions/checkout@v3

    # Setup PHP
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.0'
        extensions: mbstring, pdo, pdo_mysql, openssl, json, curl

    # Install Composer dependencies
    - name: Install Composer dependencies
      run: composer install --no-dev --optimize-autoloader --prefer-dist

    # Setup Node.js
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'

    # Install NPM dependencies
    - name: Install NPM dependencies
      run: npm ci

    # Build assets
    - name: Build production assets
      run: npm run build

    # Run tests (nếu có)
    - name: Run tests
      run: |
        # php vendor/bin/phpunit
        echo "No tests configured yet"

    # Deploy to server via SSH
    - name: Deploy to Production Server
      uses: easingthemes/ssh-deploy@main
      env:
        SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
        REMOTE_HOST: ${{ secrets.REMOTE_HOST }}
        REMOTE_USER: ${{ secrets.REMOTE_USER }}
        TARGET: ${{ secrets.REMOTE_PATH }}
        EXCLUDE: "/node_modules/, /.git/, /.github/, /resources/, /.env.example"
```

### Secrets cần thiết (GitHub Repository Settings)

```txt
SSH_PRIVATE_KEY: Your SSH private key
REMOTE_HOST: your-server.com
REMOTE_USER: username
REMOTE_PATH: /var/www/mam-mam-store
```

### GitLab CI/CD

Tạo file `.gitlab-ci.yml`:

```yaml
stages:
  - build
  - test
  - deploy

variables:
  COMPOSER_CACHE_DIR: "$CI_PROJECT_DIR/.composer-cache"

cache:
  paths:
    - .composer-cache/
    - node_modules/

build:
  stage: build
  image: php:8.0-cli
  before_script:
    - apt-get update -qq && apt-get install -y -qq git curl
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    - apt-get install -y nodejs
  script:
    - composer install --no-dev --optimize-autoloader --prefer-dist
    - npm ci
    - npm run build
  artifacts:
    paths:
      - vendor/
      - public/css/
      - public/js/
    expire_in: 1 hour

deploy_production:
  stage: deploy
  image: alpine:latest
  before_script:
    - apk add --no-cache openssh-client rsync
    - eval $(ssh-agent -s)
    - echo "$SSH_PRIVATE_KEY" | tr -d '\r' | ssh-add -
    - mkdir -p ~/.ssh
    - chmod 700 ~/.ssh
    - ssh-keyscan $REMOTE_HOST >> ~/.ssh/known_hosts
  script:
    - rsync -avz --delete
        --exclude='.git'
        --exclude='node_modules'
        --exclude='resources'
        --exclude='.env.example'
        ./ $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH
    - ssh $REMOTE_USER@$REMOTE_HOST "cd $REMOTE_PATH && chmod -R 755 storage"
  only:
    - main
  environment:
    name: production
    url: https://yourdomain.com
```

---

## 🌐 Deployment

### Shared Hosting (cPanel)

1. **Upload files:**
   - Zip dự án (trừ `node_modules`, `.git`)
   - Upload qua File Manager hoặc FTP
   - Extract vào thư mục `public_html`

2. **Setup Database:**
   - Tạo database qua cPanel → MySQL Databases
   - Import `database/schema.sql` và `data.sql` qua phpMyAdmin

3. **Cấu hình `.env`:**

   ```env
   DB_HOST = "localhost"
   DB_DB = "cpanel_username_dbname"
   DB_USER = "cpanel_username_dbuser"
   DB_PASS = "your_db_password"
   HOST_URL = "https://yourdomain.com"
   APP_ENV = "production"
   ```

4. **Setup `.htaccess`** (nếu DocumentRoot không phải `/public`):

   **Root `.htaccess`:**

   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_URI} !^/public/
   RewriteRule ^(.*)$ /public/$1 [L]
   ```

### VPS (Ubuntu/Debian)

```bash
# 1. Update system
sudo apt update && sudo apt upgrade -y

# 2. Install LAMP Stack
sudo apt install apache2 mysql-server php8.0 php8.0-{mbstring,xml,curl,zip,mysql} -y

# 3. Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 4. Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# 5. Clone project
cd /var/www
sudo git clone https://github.com/yourusername/mam-mam-store.git
cd mam-mam-store

# 6. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 7. Set permissions
sudo chown -R www-data:www-data /var/www/mam-mam-store
sudo chmod -R 755 /var/www/mam-mam-store
sudo chmod -R 775 /var/www/mam-mam-store/public/uploads

# 8. Configure Apache Virtual Host (xem phần Virtual Host ở trên)

# 9. Enable site & restart
sudo a2ensite mammamstore.local
sudo systemctl restart apache2

# 10. Setup SSL (Let's Encrypt)
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d yourdomain.com
```

---

## 🔧 Scripts NPM/Bun

```json
{
  "scripts": {
    "dev": "npm run css:watch & npm run ts:watch",
    "build": "npm run css:build && npm run ts:build",
    "css:watch": "tailwindcss -i ./resources/css/app.css -o ./public/css/app.css --watch",
    "css:build": "tailwindcss -i ./resources/css/app.css -o ./public/css/app.css --minify",
    "ts:watch": "tsc --watch",
    "ts:build": "tsc"
  }
}
```

---

## 🐛 Troubleshooting

### Lỗi "Class not found"

```bash
composer dump-autoload
```

### Lỗi 404 - Routes không hoạt động

**Apache:** Bật `mod_rewrite`

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Nginx:** Kiểm tra `try_files` trong config

### Lỗi kết nối Database

- Kiểm tra MySQL đang chạy
- Xác nhận thông tin trong `.env`
- Test connection:

  ```bash
  mysql -u root -p -e "SHOW DATABASES;"
  ```

### Assets không load (CSS/JS)

```bash
npm run build
# Kiểm tra file có tồn tại trong public/css và public/js
```

### Permission denied trên uploads/

```bash
sudo chmod -R 775 public/uploads
sudo chown -R www-data:www-data public/uploads
```

---

## 📚 Tài liệu tham khảo

- [PHP Documentation](https://www.php.net/docs.php)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/)
- [TailwindCSS Docs](https://tailwindcss.com/docs)
- [DaisyUI Components](https://daisyui.com/components/)

---

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Author

**Your Name**
GitHub: [@shin-tran](https://github.com/shin-tran)

---

**Happy Coding! 🚀**
