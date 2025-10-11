<div
  role="article"
  lang="en"
  style="background-color:white;color:#2b2b2b;font-family:'Avenir Next',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';font-size:18px;font-weight:400;line-height:28px;margin:0 auto;max-width:600px;padding:40px 20px 40px 20px"
>
  <header>
    <h1 style="color:#130b43;font-size:32px;font-weight:700;line-height:32px;margin:32px 0;text-align:center">
      Yêu Cầu Đặt Lại Mật Khẩu
    </h1>
  </header>

  <div style="background-color:ghostwhite;border-radius:5px;padding:24px 32px">
    <p>Chào <strong><?php echo $user['full_name']; ?></strong>,</p>
    <p>Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản Ăn Vặt Shop của bạn. Vui lòng nhấn vào nút bên dưới
      để tạo một mật khẩu mới.</p>
    <div style="text-align: center; margin: 30px 0;">
      <a
        href="<?php echo $forgotPasswordLink; ?>"
        style="background-color: #dc3545; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;"
      >Đặt Lại Mật Khẩu</a>
    </div>
    <p>Vì lý do bảo mật, liên kết này sẽ chỉ có hiệu lực trong vòng <strong>5 phút</strong>.</p>
    <p style="font-size: 0.9em; color: #666;">Nếu nút trên không hoạt động, bạn có thể sao chép và dán đường link sau
      vào trình duyệt:<br>
      <a
        href="<?php echo $forgotPasswordLink; ?>"
        style="color: #007bff; word-break: break-all;"
      ><?php echo $forgotPasswordLink; ?></a>
    </p>
  </div>
  <footer style="text-align:center">
    <p style="font-size:16px;font-weight:400;line-height:24px;margin-top:16px">
      Nếu bạn không phải là người thực hiện yêu cầu này, hãy
      bỏ qua email này. Tài khoản của bạn vẫn an toàn.</p>
  </footer>
</div>