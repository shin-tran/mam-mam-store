import { authService } from "../../services/auth-service.js";
import { toastManager } from "../../toast-manager.js";

export class AddressManager {
  private addressesContainer: HTMLElement | null;
  private newAddressForm: HTMLElement | null;
  private addNewAddressBtn: HTMLElement | null;
  private saveNewAddressBtn: HTMLButtonElement | null;
  private cancelNewAddressBtn: HTMLElement | null;

  // Form inputs
  private newRecipientNameInput: HTMLInputElement | null;
  private newPhoneNumberInput: HTMLInputElement | null;
  private newStreetAddressInput: HTMLInputElement | null;
  private newProvinceSelect: HTMLSelectElement | null;
  private newWardSelect: HTMLSelectElement | null;
  private newProvinceCodeInput: HTMLInputElement | null;
  private newIsDefaultCheckbox: HTMLInputElement | null;

  // State
  private userAddresses: any[] = [];
  private selectedAddressId: number | null = null;
  private provinces: any[] = [];
  private wards: any[] = [];

  constructor() {
    this.addressesContainer = document.getElementById("addresses-container");
    this.newAddressForm = document.getElementById("new-address-form");
    this.addNewAddressBtn = document.getElementById("add-new-address-btn");
    this.saveNewAddressBtn = document.getElementById(
      "save-new-address-btn"
    ) as HTMLButtonElement;
    this.cancelNewAddressBtn = document.getElementById("cancel-new-address-btn");

    this.newRecipientNameInput = document.getElementById(
      "new-recipient-name"
    ) as HTMLInputElement;
    this.newPhoneNumberInput = document.getElementById(
      "new-phone-number"
    ) as HTMLInputElement;
    this.newStreetAddressInput = document.getElementById(
      "new-street-address"
    ) as HTMLInputElement;
    this.newProvinceSelect = document.getElementById(
      "new-province-select"
    ) as HTMLSelectElement;
    this.newWardSelect = document.getElementById(
      "new-ward-select"
    ) as HTMLSelectElement;
    this.newProvinceCodeInput = document.getElementById(
      "new-province-code"
    ) as HTMLInputElement;
    this.newIsDefaultCheckbox = document.getElementById(
      "new-is-default"
    ) as HTMLInputElement;

    this.setupEventListeners();
  }

  /**
   * Setup event listeners
   */
  private setupEventListeners() {
    this.addNewAddressBtn?.addEventListener("click", () => {
      this.showNewAddressForm();
      this.fetchAndFillUserInfo();
    });

    this.saveNewAddressBtn?.addEventListener("click", () => {
      this.saveNewAddress();
    });

    this.cancelNewAddressBtn?.addEventListener("click", () => {
      this.hideNewAddressForm();
    });

    this.newProvinceSelect?.addEventListener("change", () => {
      this.handleProvinceChange();
    });
  }

  /**
   * Fetch user's saved addresses from API
   */
  async fetchUserAddresses() {
    try {
      const response = await authService.fetchWithAuth("/api/users/addresses");
      if (!response.ok) {
        this.renderAddressesError();
        return;
      }

      const result = await response.json();
      if (result.success && result.data) {
        this.userAddresses = result.data;
        this.renderAddresses();

        // Auto-select default address
        const defaultAddress = this.userAddresses.find(
          (addr) => addr.is_default === 1
        );
        if (defaultAddress) {
          this.selectedAddressId = defaultAddress.id;
        } else if (this.userAddresses.length > 0) {
          this.selectedAddressId = this.userAddresses[0].id;
        }
      } else {
        this.renderAddressesEmpty();
      }
    } catch (error) {
      console.error("Failed to fetch addresses:", error);
      this.renderAddressesError();
    }
  }

  /**
   * Render user addresses as radio buttons
   */
  private renderAddresses() {
    if (!this.addressesContainer) return;

    if (this.userAddresses.length === 0) {
      this.renderAddressesEmpty();
      return;
    }

    this.addressesContainer.innerHTML = this.userAddresses
      .map(
        (address) => `
      <div class="form-control">
        <label class="label cursor-pointer justify-start gap-3 border rounded-lg p-4 hover:bg-base-200 transition-colors ${
          address.is_default === 1
            ? "border-primary bg-primary/5"
            : "border-base-300"
        }">
          <input
            type="radio"
            name="selected-address"
            value="${address.id}"
            class="radio radio-primary"
            ${address.is_default === 1 ? "checked" : ""}
          />
          <div class="flex-1">
            <div class="font-semibold">
              ${address.recipient_name}
              ${
                address.is_default === 1
                  ? '<span class="badge badge-primary badge-sm ml-2">Mặc định</span>'
                  : ""
              }
            </div>
            <div class="text-sm opacity-70">${address.phone_number}</div>
            <div class="text-sm opacity-70">${address.street_address}, ${
          address.ward
        }, ${address.city}</div>
          </div>
        </label>
      </div>
    `
      )
      .join("");

    // Add event listeners to radio buttons
    const radioButtons = this.addressesContainer.querySelectorAll(
      'input[name="selected-address"]'
    );
    radioButtons.forEach((radio) => {
      radio.addEventListener("change", (e) => {
        this.selectedAddressId = parseInt(
          (e.target as HTMLInputElement).value
        );
      });
    });
  }

  /**
   * Render empty state
   */
  private renderAddressesEmpty() {
    if (!this.addressesContainer) return;
    this.addressesContainer.innerHTML = `
    <div class="text-center py-4 text-sm opacity-70">
      Bạn chưa có địa chỉ nào. Vui lòng thêm địa chỉ mới.
    </div>
  `;
  }

  /**
   * Render error state
   */
  private renderAddressesError() {
    if (!this.addressesContainer) return;
    this.addressesContainer.innerHTML = `
    <div class="alert alert-error">
      <span>Không thể tải danh sách địa chỉ. Vui lòng thử lại.</span>
    </div>
  `;
  }

  /**
   * Show new address form
   */
  private showNewAddressForm() {
    if (this.newAddressForm) {
      this.newAddressForm.classList.remove("hidden");
    }
    this.loadProvinces();
  }

  /**
   * Hide new address form
   */
  private hideNewAddressForm() {
    if (this.newAddressForm) {
      this.newAddressForm.classList.add("hidden");
      this.clearNewAddressForm();
    }
  }

  /**
   * Load provinces from API
   */
  private async loadProvinces() {
    if (!this.newProvinceSelect) return;

    try {
      const response = await fetch(
        "https://tinhthanhpho.com/api/v1/new-provinces?limit=36"
      );
      const result = await response.json();

      if (result.success && result.data) {
        this.provinces = result.data;
        this.newProvinceSelect.innerHTML =
          '<option value="">-- Chọn Tỉnh/Thành phố --</option>';
        this.provinces.forEach((province: any) => {
          const option = document.createElement("option");
          option.value = `${province.type} ${province.name}`;
          option.setAttribute("data-code", province.code);
          option.textContent = province.name;
          this.newProvinceSelect!.appendChild(option);
        });
      }
    } catch (error) {
      console.error("Failed to load provinces:", error);
      toastManager.createToast({
        message: "Không thể tải danh sách tỉnh/thành phố",
        type: "error",
      });
    }
  }

  /**
   * Handle province change to load wards
   */
  private async handleProvinceChange() {
    if (
      !this.newProvinceSelect ||
      !this.newWardSelect ||
      !this.newProvinceCodeInput
    )
      return;

    const selectedOption =
      this.newProvinceSelect.options[this.newProvinceSelect.selectedIndex];
    if (!selectedOption) return;

    const provinceCode = selectedOption.getAttribute("data-code");

    if (!provinceCode) {
      this.newWardSelect.disabled = true;
      this.newWardSelect.innerHTML =
        '<option value="">-- Chọn Phường/Xã --</option>';
      this.newProvinceCodeInput.value = "";
      return;
    }

    this.newProvinceCodeInput.value = provinceCode;
    this.newWardSelect.disabled = true;
    this.newWardSelect.innerHTML = '<option value="">Đang tải...</option>';

    try {
      const response = await fetch(
        `https://tinhthanhpho.com/api/v1/new-provinces/${provinceCode}/wards?limit=170`
      );
      const result = await response.json();

      if (result.success && result.data) {
        this.wards = result.data;
        this.newWardSelect.innerHTML =
          '<option value="">-- Chọn Phường/Xã --</option>';
        this.wards.forEach((ward: any) => {
          const option = document.createElement("option");
          option.value = `${ward.type} ${ward.name}`;
          option.textContent = ward.name;
          this.newWardSelect!.appendChild(option);
        });
        this.newWardSelect.disabled = false;
      }
    } catch (error) {
      console.error("Failed to load wards:", error);
      this.newWardSelect.innerHTML =
        '<option value="">Lỗi khi tải phường/xã</option>';
      toastManager.createToast({
        message: "Không thể tải danh sách phường/xã",
        type: "error",
      });
    }
  }

  /**
   * Clear new address form inputs
   */
  private clearNewAddressForm() {
    if (this.newRecipientNameInput) this.newRecipientNameInput.value = "";
    if (this.newPhoneNumberInput) this.newPhoneNumberInput.value = "";
    if (this.newStreetAddressInput) this.newStreetAddressInput.value = "";
    if (this.newProvinceSelect) this.newProvinceSelect.selectedIndex = 0;
    if (this.newWardSelect) {
      this.newWardSelect.selectedIndex = 0;
      this.newWardSelect.disabled = true;
    }
    if (this.newProvinceCodeInput) this.newProvinceCodeInput.value = "";
    if (this.newIsDefaultCheckbox) this.newIsDefaultCheckbox.checked = false;
  }

  /**
   * Save new address
   */
  private async saveNewAddress() {
    const recipientName = this.newRecipientNameInput?.value.trim();
    const phoneNumber = this.newPhoneNumberInput?.value.trim();
    const streetAddress = this.newStreetAddressInput?.value.trim();
    const ward = this.newWardSelect?.value.trim();
    const city = this.newProvinceSelect?.value.trim();
    const isDefault = this.newIsDefaultCheckbox?.checked ? 1 : 0;

    // Validation
    if (!recipientName || !phoneNumber || !streetAddress || !ward || !city) {
      toastManager.createToast({
        message: "Vui lòng điền đầy đủ thông tin địa chỉ",
        type: "error",
      });
      return;
    }

    try {
      if (this.saveNewAddressBtn) {
        this.saveNewAddressBtn.disabled = true;
        this.saveNewAddressBtn.innerHTML = `<span class="loading loading-spinner loading-sm"></span> Đang lưu...`;
      }

      const response = await authService.fetchWithAuth(
        "/api/users/addresses/create",
        {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
            recipient_name: recipientName,
            phone_number: phoneNumber,
            street_address: streetAddress,
            ward: ward,
            city: city,
            is_default: isDefault.toString(),
          }),
        }
      );

      const result = await response.json();

      if (result.success) {
        toastManager.createToast({
          message: "Thêm địa chỉ thành công",
          type: "success",
        });
        this.hideNewAddressForm();
        await this.fetchUserAddresses();
      } else {
        toastManager.createToast({
          message: result.message || "Thêm địa chỉ thất bại",
          type: "error",
        });
      }
    } catch (error) {
      toastManager.createToast({
        message: "Lỗi kết nối máy chủ",
        type: "error",
      });
    } finally {
      if (this.saveNewAddressBtn) {
        this.saveNewAddressBtn.disabled = false;
        this.saveNewAddressBtn.innerHTML = `Lưu địa chỉ`;
      }
    }
  }

  /**
   * Fetch and fill user info
   */
  private async fetchAndFillUserInfo() {
    try {
      const response = await authService.fetchWithAuth("/api/profile/info");
      if (!response.ok) return;

      const result = await response.json();
      if (result.success && result.data) {
        const user = result.data;
        // Pre-fill new address form with user info
        if (
          this.newRecipientNameInput &&
          !this.newRecipientNameInput.value
        ) {
          this.newRecipientNameInput.value = user.full_name || "";
        }
        if (this.newPhoneNumberInput && !this.newPhoneNumberInput.value) {
          this.newPhoneNumberInput.value = user.phone_number || "";
        }
      }
    } catch (e) {
      console.warn("Could not pre-fill user info for checkout.");
    }
  }

  /**
   * Get selected address
   */
  getSelectedAddress() {
    return this.userAddresses.find(
      (addr) => addr.id === this.selectedAddressId
    );
  }

  /**
   * Get selected address ID
   */
  getSelectedAddressId() {
    return this.selectedAddressId;
  }
}
