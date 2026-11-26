
document.addEventListener('DOMContentLoaded', () => {
    const btnLacak = document.getElementById('btn-lacak');
    const inputNota = document.getElementById('input-nota');
    const modal = document.getElementById('modal-verifikasi-hp');
    if (!modal) return; // Stop script kalau modal ga dirender (di halaman hasil)

    const btnTutup = document.getElementById('btn-tutup-modal');
    const pinInputs = document.querySelectorAll('.pin-input');
    const btnSubmitPin = document.getElementById('btn-submit-pin');
    const formLacak = document.getElementById('form-lacak');
    const hiddenPin = document.getElementById('hidden-pin');

    btnLacak.addEventListener('click', () => {
        if (inputNota.value.trim() === '') {
            alert('Masukkan nomor nota terlebih dahulu!');
            return;
        }
        modal.classList.remove('hidden');
        pinInputs[0].focus();
    });

    btnTutup.addEventListener('click', () => {
        modal.classList.add('hidden');
        pinInputs.forEach(input => input.value = '');
        btnSubmitPin.classList.add('bg-[#E7E2D6]', 'text-[#938F85]', 'pointer-events-none');
        btnSubmitPin.classList.remove('bg-orange', 'text-white');
    });

    pinInputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value && index < pinInputs.length - 1) pinInputs[index + 1].focus();

            let isComplete = Array.from(pinInputs).every(inp => inp.value.length === 1);
            if (isComplete) {
                btnSubmitPin.classList.remove('bg-[#E7E2D6]', 'text-[#938F85]', 'pointer-events-none');
                btnSubmitPin.classList.add('bg-orange', 'text-white');
            } else {
                btnSubmitPin.classList.add('bg-[#E7E2D6]', 'text-[#938F85]', 'pointer-events-none');
                btnSubmitPin.classList.remove('bg-orange', 'text-white');
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                pinInputs[index - 1].focus();
            }
        });
    });

    btnSubmitPin.addEventListener('click', () => {
        hiddenPin.value = Array.from(pinInputs).map(inp => inp.value).join('');
        formLacak.submit();
    });
});


