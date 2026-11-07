document.addEventListener('DOMContentLoaded', () => {
    const btnLacak = document.getElementById('btn-lacak');
    const modalPopUp = document.getElementById('modal-verifikasi-hp');
    const btnTutupModal = document.getElementById('btn-tutup-modal');
    
    // Logika buka tutup modal
    if (btnLacak && modalPopUp) {
        btnLacak.addEventListener('click', (e) => {
            e.preventDefault();
            modalPopUp.classList.remove('hidden');
        });

        if (btnTutupModal) {
            btnTutupModal.addEventListener('click', () => {
                modalPopUp.classList.add('hidden');
            });
        }
    }

    // Logika 4 Kotak PIN
    const pinInputs = document.querySelectorAll('.pin-input');
    const btnSubmitPin = document.getElementById('btn-submit-pin');

    if (pinInputs.length > 0) {
        pinInputs.forEach((input, index) => {
            // Cegah input huruf, cuma boleh angka
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Kalau udah keisi angka, otomatis pindah ke kotak kanan
                if (this.value !== '' && index < pinInputs.length - 1) {
                    pinInputs[index + 1].focus();
                }
                checkPinComplete();
            });

            // Kalau dipencet backspace, hapus dan pindah ke kiri
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    pinInputs[index - 1].focus();
                }
            });
        });
    }

    // Cek apakah 4 kotak udah keisi semua buat nyalain tombol
    function checkPinComplete() {
        let isComplete = true;
        pinInputs.forEach(input => {
            if (input.value === '') isComplete = false;
        });

        if (isComplete) {
            // Ubah tombol jadi Oranye nyala dan bisa diklik
            btnSubmitPin.classList.remove('bg-[#E8E4D9]', 'text-[#938F85]', 'pointer-events-none');
            btnSubmitPin.classList.add('bg-[#F26B21]', 'text-white', 'hover:bg-[#e05a12]', 'cursor-pointer');
        } else {
            // Balikin tombol jadi Krem mati
            btnSubmitPin.classList.add('bg-[#E8E4D9]', 'text-[#938F85]', 'pointer-events-none');
            btnSubmitPin.classList.remove('bg-[#F26B21]', 'text-white', 'hover:bg-[#e05a12]', 'cursor-pointer');
        }
    }

    // Tambahin ini di bawah logika PIN
    if (btnSubmitPin) {
        btnSubmitPin.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Animasi loading kecil (opsional biar kerasa nyata)
            btnSubmitPin.innerHTML = 'Mengecek...';
            btnSubmitPin.classList.add('opacity-70');

            // Jeda setengah detik biar kerasa kayak ngecek data beneran, lalu pindah halaman
            setTimeout(() => {
                window.location.href = 'hasil.php';
            }, 500);
        });
    }
});