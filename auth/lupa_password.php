<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lupa Sandi - Singhasari Computer</title>
    <!-- Google Fonts: Full Poppins (Weights: 300, 400, 500, 600, 700, 800, 900) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
      rel="stylesheet"
    />

  <!-- Tailwind CSS & Custom Style CSS -->
  <link rel="stylesheet" href="../assets/css/tailwind.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  </head>
  <body class="min-h-screen flex items-center justify-center p-4">
    <div
      class="bg-white-light max-w-md w-full rounded-4xl shadow-sm p-8 text-center"
    >
      <!-- Logo Dummy -->
      <div
        class="mx-auto w-16 h-16 bg-orange-light-active rounded-full flex items-center justify-center mb-6"
      >
        <svg
          class="w-7 h-7 text-orange-hover"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 14 14"
        >
          <path d="M0 0h14v14H0z" fill="none" />
          <path
            fill="currentColor"
            fill-rule="evenodd"
            d="M7 .125a3.667 3.667 0 0 0-3.667 3.667v1.124a3.46 3.46 0 0 0-1.942 2.713c-.058.512-.1 1.04-.1 1.58c0 .539.042 1.067.1 1.578a3.46 3.46 0 0 0 3.298 3.048a65 65 0 0 0 4.622 0a3.46 3.46 0 0 0 3.298-3.048c.058-.511.1-1.04.1-1.579c0-.54-.042-1.067-.1-1.579a3.46 3.46 0 0 0-1.943-2.713V3.792A3.667 3.667 0 0 0 7 .125m2.166 4.451v-.784a2.167 2.167 0 1 0-4.333 0v.784A65 65 0 0 1 7 4.542c.762 0 1.474.011 2.166.034M7 7.876a.75.75 0 0 1 .75.75v.999a.75.75 0 0 1-1.5 0v-1a.75.75 0 0 1 .75-.75Z"
            clip-rule="evenodd"
          />
        </svg>
      </div>

      <h2 class="text-2xl font-bold text-gray-800 mb-2">Lupa Kata Sandi?</h2>
      <p class="text-gray-500 text-sm mb-6">
        Masukkan email yang terdaftar, dan kami akan kirimkan link untuk
        mengatur ulang kata sandi kamu.
      </p>

      <form action="#" method="POST" class="space-y-4">
        <div class="text-left">
          <label
            for="email"
            class="block text-sm font-medium text-gray-700 mb-1"
            >Email Anda</label
          >
          <input
            type="email"
            id="email"
            name="email"
            required
            placeholder="admin@singhasari.com"
            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-1 focus:border-transparent transition-all custom-input"
          />
        </div>

        <button type="submit" class="w-full mt-5 btn-submit">
          Kirim Link Reset
        </button>
      </form>

      <div class="mt-6">
        <a
          href="login.php"
          class="text-sm text-orange hover:text-orange-hover font-medium"
        >
          &larr; Kembali ke halaman Login
        </a>
      </div>
    </div>
  </body>
</html>
