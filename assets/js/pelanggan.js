document.addEventListener("DOMContentLoaded", () => {
  const btnLacak = document.getElementById("btn-lacak");
  const modalPopUp = document.getElementById("modal-verifikasi-hp");
  const btnTutupModal = document.getElementById("btn-tutup-modal");

  const formLacak = document.getElementById("form-lacak");
  const pinInputs = document.querySelectorAll(".pin-input");
  const btnSubmitPin = document.getElementById("btn-submit-pin");

  // Buka modal verifikasi PIN
  if (btnLacak && modalPopUp) {
    btnLacak.addEventListener("click", (e) => {
      e.preventDefault();

      modalPopUp.classList.remove("hidden");

      if (pinInputs.length > 0) {
        pinInputs[0].focus();
      }
    });
  }

  // Tutup modal
  if (btnTutupModal && modalPopUp) {
    btnTutupModal.addEventListener("click", () => {
      modalPopUp.classList.add("hidden");
    });
  }

  // Input PIN empat digit
  pinInputs.forEach((input, index) => {
    input.addEventListener("input", function () {
      this.value = this.value.replace(/[^0-9]/g, "").slice(0, 1);

      if (this.value !== "" && index < pinInputs.length - 1) {
        pinInputs[index + 1].focus();
      }

      checkPinComplete();
    });

    input.addEventListener("keydown", function (e) {
      if (e.key === "Backspace" && this.value === "" && index > 0) {
        pinInputs[index - 1].focus();
      }
    });
  });

  // Periksa kelengkapan PIN
  function checkPinComplete() {
    const isComplete =
      pinInputs.length === 4 &&
      [...pinInputs].every((input) => input.value !== "");

    if (!btnSubmitPin) return;

    btnSubmitPin.disabled = !isComplete;

    if (isComplete) {
      btnSubmitPin.classList.remove(
        "bg-[#E8E4D9]",
        "text-[#938F85]",
        "pointer-events-none",
      );

      btnSubmitPin.classList.add(
        "bg-[#F26B21]",
        "text-white",
        "hover:bg-[#e05a12]",
        "cursor-pointer",
      );
    } else {
      btnSubmitPin.classList.add(
        "bg-[#E8E4D9]",
        "text-[#938F85]",
        "pointer-events-none",
      );

      btnSubmitPin.classList.remove(
        "bg-[#F26B21]",
        "text-white",
        "hover:bg-[#e05a12]",
        "cursor-pointer",
      );
    }
  }

  checkPinComplete();

  // Kirim PIN ke lacak.php
  if (btnSubmitPin && formLacak) {
    btnSubmitPin.addEventListener("click", (e) => {
      e.preventDefault();

      const pin = [...pinInputs].map((input) => input.value).join("");

      if (pin.length !== 4) return;

      // Siapkan input PIN tersembunyi
      let hiddenPin = formLacak.querySelector('input[name="pin_verifikasi"]');

      if (!hiddenPin) {
        hiddenPin = document.createElement("input");
        hiddenPin.type = "hidden";
        hiddenPin.name = "pin_verifikasi";
        formLacak.appendChild(hiddenPin);
      }

      hiddenPin.value = pin;

      // Kirim kembali ke halaman lacak.php
      btnSubmitPin.disabled = true;
      btnSubmitPin.textContent = "Memverifikasi...";

      formLacak.method = "POST";
      formLacak.action = window.location.pathname;

      formLacak.requestSubmit();
    });
  }
});
