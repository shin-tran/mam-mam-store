<form
  id="register-form"
  novalidate
>
  <div class="flex flex-col mx-auto md:w-96 w-full">
    <h1 class="text-2xl font-bold mb-4 text-center">Đăng ký</h1>

    <div class="flex flex-col gap-2 mb-4">
      <label
        for="full_name"
        class="required"
      >Họ Tên</label>
      <input
        type="text"
        name="full_name"
        placeholder="Nhập tên"
        class="input w-full"
        data-field="full_name"
        required
      />
      <div
        class="error-log hidden"
        data-field="full_name"
      ></div>
    </div>

    <div class="flex flex-col gap-2 mb-4">
      <label
        for="email"
        class="required"
      >Email</label>
      <input
        name="email"
        type="email"
        placeholder="Nhập Email"
        class="input w-full"
        data-field="email"
        required
      />
      <div
        class="error-log hidden"
        data-field="email"
      ></div>
    </div>

    <div class="flex flex-col gap-2 mb-4">
      <label for="phone_number">Số điện thoại</label>
      <input
        name="phone_number"
        type="tel"
        placeholder="Nhập số điện thoại"
        class="input w-full"
        data-field="phone_number"
      />
      <div
        class="error-log hidden"
        data-field="phone_number"
      ></div>
    </div>

    <div class="flex flex-col gap-2 mb-4">
      <label
        for="password"
        class="required"
      >Mật khẩu</label>
      <input
        name="password"
        type="password"
        placeholder="Đặt mật khẩu"
        class="input w-full"
        data-field="password"
        required
      />
      <div
        class="error-log hidden"
        data-field="password"
      ></div>
    </div>

    <div class="flex flex-col gap-2">
      <label
        for="confirm_password"
        class="required"
      >
        Xác nhận mật khẩu
      </label>
      <input
        name="confirm_password"
        type="password"
        placeholder="Xác nhận mật khẩu"
        class="input w-full"
        data-field="confirm_password"
        required
      />
      <div
        class="error-log hidden"
        data-field="confirm_password"
      ></div>
    </div>

    <div class="border-t h-[1px] my-6"></div>

    <div class="flex flex-col gap-2 items-center">
      <button
        type="submit"
        class="btn w-full"
      >Đăng ký</button>
      <div>
        <span>Đã có tài khoản?
          <a
            href="<?php echo _HOST_URL; ?>/login"
            class="underline"
          >Đăng nhập</a>
        </span>
      </div>
    </div>
  </div>
</form>
<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/auth/register.js"
></script>