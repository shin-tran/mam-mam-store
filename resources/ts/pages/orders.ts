const checkoutModal = document.getElementById(
  "checkout_modal"
) as HTMLDialogElement;
checkoutModal.addEventListener("click", (event) => {
  if (event.target === checkoutModal) {
    checkoutModal.close();
  }
});
