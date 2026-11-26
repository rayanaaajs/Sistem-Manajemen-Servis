document.addEventListener("DOMContentLoaded", () => {
  const table = document.getElementById("tableSparepart");

  // Jalankan hanya di halaman yang memiliki tabel sparepart.
  if (!table) return;

  const PER_PAGE = 10;

  const rows = [...document.querySelectorAll("#tbodySparepart .row-item")];

  const searchEl = document.getElementById("searchNotaInput");
  const emptyEl = document.getElementById("emptySparepart");
  const infoEl = document.getElementById("infoSparepart");
  const pagerEl = document.getElementById("pagerSparepart");

  const filterButtons = [...document.querySelectorAll(".filter-sparepart-btn")];

  let page = 1;
  let query = "";
  let currentFilter = "all";

  const PAGE_BTN =
    "w-9 h-9 rounded-full text-blue-darker hover:bg-white-hover font-medium text-xs flex items-center justify-center transition cursor-pointer";

  const PAGE_ACTIVE =
    "w-9 h-9 rounded-full bg-orange text-white-light font-bold text-xs flex items-center justify-center";

  const FILTER_INACTIVE =
    "filter-sparepart-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap";

  const FILTER_ACTIVE =
    "filter-sparepart-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold transition cursor-pointer whitespace-nowrap";

  // PAGINATION
  function render() {
    const filteredRows = rows.filter((row) => {
      const matchesSearch = row.dataset.search.includes(query);

      const matchesStatus =
        currentFilter === "all" || row.dataset.status === currentFilter;

      return matchesSearch && matchesStatus;
    });

    const total = filteredRows.length;
    const pages = Math.max(1, Math.ceil(total / PER_PAGE));

    page = Math.min(page, pages);

    rows.forEach((row) => row.classList.add("hidden"));

    filteredRows
      .slice((page - 1) * PER_PAGE, page * PER_PAGE)
      .forEach((row) => row.classList.remove("hidden"));

    emptyEl.classList.toggle("hidden", total > 0);

    const start = total ? (page - 1) * PER_PAGE + 1 : 0;
    const end = Math.min(page * PER_PAGE, total);

    infoEl.textContent = `Menampilkan ${start}-${end} dari ${total} sparepart`;

    pagerEl.replaceChildren();

    function addPageButton(label, target, active, disabled) {
      const button = document.createElement("button");

      button.type = "button";
      button.textContent = label;
      button.className = active ? PAGE_ACTIVE : PAGE_BTN;
      button.disabled = disabled;

      if (disabled) {
        button.classList.add("opacity-35", "cursor-default");
      }

      if (!active && !disabled) {
        button.addEventListener("click", () => {
          page = target;
          render();
        });
      }

      pagerEl.appendChild(button);
    }

    // Nomor halaman tetap tampil meskipun hanya 1 halaman.
    if (pages > 1) {
      addPageButton("‹", page - 1, false, page === 1);
    }

    for (let i = 1; i <= pages; i++) {
      addPageButton(String(i), i, i === page, false);
    }

    if (pages > 1) {
      addPageButton("›", page + 1, false, page === pages);
    }
  }

  // PENCARIAN
  if (searchEl) {
    searchEl.addEventListener("input", () => {
      query = searchEl.value.toLocaleLowerCase("id-ID").trim();
      page = 1;
      render();
    });
  }

  // FILTER STATUS
  filterButtons.forEach((button) => {
    button.addEventListener("click", () => {
      filterButtons.forEach((item) => {
        item.className = FILTER_INACTIVE;
      });

      button.className = FILTER_ACTIVE;
      currentFilter = button.dataset.filter;
      page = 1;

      render();
    });
  });

  render();

  // MODAL PEMAKAIAN SPAREPART
  const modal = document.getElementById("modalPakai");

  // Halaman index.php tidak memiliki modal pemakaian.
  if (!modal) return;

  const backdrop = document.getElementById("modalPakaiBackdrop");
  const modalBox = modal.querySelector(".modal-box");

  const inputJumlah = document.getElementById("inputJumlah");
  const inputId = document.getElementById("inputIdSparepart");

  const errorJumlah = document.getElementById("errJumlah");
  const btnSimpan = document.getElementById("btnSimpanPakai");

  let stokMax = 0;
  let hargaSatuan = 0;

  const rupiah = (value) =>
    "Rp " + new Intl.NumberFormat("id-ID").format(Math.round(value));

  const jumlah = () => Number(inputJumlah.value);

  // VALIDASI JUMLAH
  function hitung() {
    const n = jumlah();

    const invalid = !Number.isSafeInteger(n) || n < 1 || n > stokMax;

    errorJumlah.textContent = invalid
      ? `Jumlah harus antara 1 sampai ${stokMax}.`
      : "";

    btnSimpan.disabled = invalid;

    document.getElementById("modalTotal").textContent = rupiah(
      (invalid ? 0 : n) * hargaSatuan,
    );
  }

  // BUKA MODAL
  document.querySelectorAll(".btn-pakai").forEach((button) => {
    button.addEventListener("click", () => {
      stokMax = Number(button.dataset.stok);
      hargaSatuan = Number(button.dataset.harga);

      inputId.value = button.dataset.id;
      inputJumlah.max = String(stokMax);
      inputJumlah.value = "1";

      document.getElementById("modalNama").textContent = button.dataset.nama;

      document.getElementById("modalStok").textContent = `${stokMax} unit`;

      document.getElementById("modalHarga").textContent = rupiah(hargaSatuan);

      hitung();

      modal.classList.remove("hidden");
      modal.classList.add("flex");

      requestAnimationFrame(() => {
        backdrop.classList.remove("opacity-0");

        modalBox.classList.remove("opacity-0", "scale-95");
      });

      inputJumlah.focus();
    });
  });

  // TUTUP MODAL
  function tutupModal() {
    backdrop.classList.add("opacity-0");

    modalBox.classList.add("opacity-0", "scale-95");

    setTimeout(() => {
      modal.classList.add("hidden");
      modal.classList.remove("flex");
    }, 300);
  }

  document
    .getElementById("btnBatalModal")
    .addEventListener("click", tutupModal);

  backdrop.addEventListener("click", tutupModal);

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !modal.classList.contains("hidden")) {
      tutupModal();
    }
  });

  // TOMBOL KURANG
  document.getElementById("btnKurang").addEventListener("click", () => {
    inputJumlah.value = String(Math.max(1, jumlah() - 1));

    hitung();
  });

  // TOMBOL TAMBAH
  document.getElementById("btnTambah").addEventListener("click", () => {
    inputJumlah.value = String(Math.min(stokMax, jumlah() + 1));

    hitung();
  });

  inputJumlah.addEventListener("input", hitung);

  // VALIDASI SEBELUM SUBMIT
  document.getElementById("formPakai").addEventListener("submit", (event) => {
    hitung();

    if (btnSimpan.disabled) {
      event.preventDefault();
    }
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const formHapus = document.getElementById("formHapusSparepart");

  if (!formHapus) return;

  formHapus.addEventListener("submit", (event) => {
    const konfirmasi = window.confirm(
      "Hapus pemakaian sparepart ini? Stok akan dikembalikan.",
    );

    if (!konfirmasi) {
      event.preventDefault();
    }
  });
});
