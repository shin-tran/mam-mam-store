const checkoutModal = document.getElementById("checkout_modal");
checkoutModal.addEventListener("click", (event) => {
    if (event.target === checkoutModal) {
        checkoutModal.close();
    }
});
