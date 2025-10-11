<div
  role="article"
  lang="en"
  style="background-color:white;color:#2b2b2b;font-family:'Avenir Next',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';font-size:18px;font-weight:400;line-height:28px;margin:0 auto;max-width:600px;padding:40px 20px 40px 20px"
>
  <header>
    <h1 style="color:#130b43;font-size:32px;font-weight:700;line-height:32px;margin:32px 0;text-align:center">
      Chào mừng bạn đến với Ăn Vặt Shop!
    </h1>
  </header>

  <div style="background-color:ghostwhite;border-radius:5px;padding:24px 32px">
    <p>Chào <strong><?php echo $_POST['full_name']; ?></strong>,</p>
    <p>Cảm ơn bạn đã tin tưởng và đăng ký tài khoản tại cửa hàng của chúng tôi. Chỉ còn một bước cuối cùng nữa thôi!</p>
    <p>Vui lòng nhấn vào nút bên dưới để kích hoạt tài khoản và bắt đầu hành trình khám phá thế giới đồ ăn vặt hấp dẫn.
    </p>

    <div style="text-align: center; margin: 30px 0;">
      <a
        href="<?php echo $activationLink; ?>"
        style="background-color: #007bff; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;"
      >Kích Hoạt Tài Khoản</a>
    </div>

    <p>Vì lý do bảo mật, liên kết này sẽ chỉ có hiệu lực trong vòng <strong>10 phút</strong>.</p>
    <p style="font-size: 0.9em; color: #666;">Nếu nút trên không hoạt động, bạn có thể sao chép và dán đường link sau
      vào trình duyệt:<br>
      <a
        href="<?php echo $activationLink; ?>"
        style="color: #007bff; word-break: break-all;"
      ><?php echo $activationLink; ?></a>
    </p>
  </div>
  <footer style="text-align:center">
    <p style="font-size:16px;font-weight:400;line-height:24px;margin-top:16px">
      Nếu bạn không phải là người thực hiện đăng ký này, vui
      lòng bỏ qua email.</p>
  </footer>
</div>