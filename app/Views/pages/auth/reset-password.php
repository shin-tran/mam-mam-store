<?php if ($user): ?>
  <form
    id="reset-password-form"
    novalidate
  >
    <div class="flex flex-col mx-auto md:w-96 w-full">
      <h1 class="text-2xl font-bold mb-4 text-center">Đặt lại mật khẩu</h1>
      <div class="flex flex-col gap-2 mb-4">
        <label
          for="new_password"
          class="required"
        >Mật khẩu mới</label>
        <input
          name="new_password"
          type="password"
          placeholder="Đặt mật khẩu mới"
          class="input w-full"
          data-field="new_password"
          required
        />
        <div
          class="error-log hidden"
          data-field="new_password"
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
        >Xác nhận</button>
      </div>
    </div>
  </form>

<?php else: ?>
  <h1 class="text-2xl font-bold mb-4 text-center">Token đã hết hạn hoặc không hợp lệ!</h1>
<?php endif; ?>
<script
  type="module"
  src="<?php echo _HOST_URL_PUBLIC ?>/js/pages/auth/reset-password.js"
></script>