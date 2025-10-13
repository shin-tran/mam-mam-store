# **Hướng Dẫn Cấu Hình Tên Miền Ảo (Virtual Host) trên XAMPP**

Tài liệu này sẽ hướng dẫn bạn từng bước để thiết lập một tên miền ảo cho dự án PHP của mình trên XAMPP. Việc này giúp bạn có thể truy cập dự án qua một tên miền thân thiện (ví dụ: `http://mammamstore.local`) thay vì đường dẫn dài (`http://localhost/mam-mam-store/`).

## **Bước 0: Chuẩn bị**

Trước khi bắt đầu, hãy quyết định:

1. **Tên miền ảo bạn muốn sử dụng:** Chúng ta sẽ sử dụng `mammamstore.local` làm ví dụ.
2. **Đường dẫn tuyệt đối đến thư mục dự án:** Ví dụ `C:/xampp/htdocs/mam-mam-store`.

## **Bước 1: Sửa file Cấu hình Virtual Hosts của Apache**

File này sẽ "bảo" Apache rằng khi có yêu cầu đến tên miền `mammamstore.local`, nó phải trỏ đến thư mục dự án bạn.

1. Mở file tại đường dẫn sau bằng trình soạn thảo văn bản (VS Code, Notepad++...):
  `C:\\xampp\\apache\\conf\\extra\\httpd-vhosts.conf`

2. Thêm đoạn mã sau vào **cuối** file:

   ```bash
   # Cấu hình cho localhost (QUAN TRỌNG: để không mất localhost)
   <VirtualHost *:80>
       DocumentRoot "C:/xampp/htdocs"
       ServerName localhost
   </VirtualHost>

   # Cấu hình cho dự án Măm Măm Store
   <VirtualHost *:80>
       # Đường dẫn đến thư mục dự án bạn
       DocumentRoot "D:\Workspace\php\mam-mam-store"
       # Tên miền ảo bạn đã chọn
       ServerName mammamstore.local
       # Cấp quyền cho thư mục, cho phép .htaccess hoạt động
       <Directory "D:\Workspace\php\mam-mam-store">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

   **Lưu ý quan trọng:** Hãy đảm bảo bạn đã thay đổi đường dẫn `DocumentRoot` và `<Directory>` cho đúng với vị trí thực tế của dự án trên máy tính của bạn.

## **Bước 2: Kích hoạt file Virtual Hosts**

Bạn cần đảm bảo file cấu hình chính của Apache đã được lệnh đọc file `httpd-vhosts.conf`.

1. Mở file cấu hình chính của Apache:
   `C:\\xampp\\apache\\conf\\httpd.conf`

2. Sử dụng chức năng tìm kiếm (Ctrl + F) để tìm dòng sau:
   `#Include conf/extra/httpd-vhosts.conf`

3. **Xóa dấu thăng (`#`)** ở đầu dòng để kích hoạt nó. Dòng đó sau khi sửa sẽ trông như thế này:
   `Include conf/extra/httpd-vhosts.conf`

## **Bước 3: Sửa file hosts của Windows**

File này sẽ "bảo" máy tính của bạn rằng tên miền `mammamstore.local` trỏ về chính máy tính của bạn (địa chỉ 127.0.0.1), thay vì tìm kiếm trên Internet.

1. **QUAN TRỌNG: Mở với quyền Administrator**
   * Tìm "Notepad" trong Start Menu.
   * Nhấp chuột phải vào biểu tượng Notepad và chọn **"Run as administrator"**.
2. Trong Notepad (đã chạy với quyền admin), vào **File \-\> Open**.
3. Dán đường dẫn sau vào ô "File name" và nhấn Enter:
   `C:\\Windows\\System32\\drivers\\etc\\hosts`

4. Thêm dòng sau vào **cuối** file hosts:
   `127.0.0.1    mammamstore.local`

5. Lưu file lại (Ctrl + S).

## **Bước 4: Khởi động lại Apache**

Để tất cả các thay đổi có hiệu lực, bạn phải khởi động lại Apache.

1. Mở XAMPP Control Panel.
2. Trong dòng của module "Apache", nhấn nút **Stop**.
3. Sau khi Apache đã dừng, nhấn nút **Start** lại.

Bây giờ, bạn có thể mở trình duyệt và truy cập vào địa chỉ `http://mammamstore.local`.

## **Xử lý Sự cố Thường gặp**

* **Vẫn thấy trang Dashboard của XAMPP:**
  * **Nguyên nhân:** Apache chưa nhận diện được cấu hình Virtual Host của bạn.
  * **Giải pháp:** Kiểm tra lại Bước 2 (đã bỏ dấu `#` chưa) và đảm bảo bạn đã khởi động lại Apache.
* **Lỗi "Server not found" hoặc "Can't reach this page":**
  * **Nguyên nhân:** File `hosts` của Windows chưa được cấu hình đúng.
  * **Giải pháp:** Kiểm tra lại Bước 3 . Đảm bảo bạn đã thêm đúng dòng `127.0.0.1 mammamstore.local` và đã lưu file thành công (không có lỗi "Permission Denied").
* **Mất quyền truy cập `http://localhost`:**
  * **Nguyên nhân:** Bạn đã quên thêm khối `<VirtualHost>` cho localhost ở Bước 1.
  * **Giải pháp:** Thêm lại khối cấu hình cho `localhost` vào file `httpd-vhosts.conf` và khởi động lại Apache.
