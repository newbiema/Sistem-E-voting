<?php
// login.php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nim = mysqli_real_escape_string($conn, $_POST['nim']);

  // Cek apakah NIM terdaftar
  $query = mysqli_query($conn, "SELECT * FROM users WHERE nim='$nim'");
  if (mysqli_num_rows($query) > 0) {
    $_SESSION['nim'] = $nim;
    header("Location: user/view.php");
    exit();
  } else {
    $error = "NIM tidak terdaftar.";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Pemilih</title>
  <!-- Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient-to-br from-blue-200 via-indigo-200 to-purple-200 min-h-screen flex items-center justify-center">
  <div class="bg-white/70 backdrop-blur-lg border border-white/40 p-8 rounded-2xl shadow-2xl w-full max-w-md">
    <div class="flex flex-col items-center mb-6">
      <i class="fas fa-vote-yea text-4xl text-blue-600 mb-2"></i>
      <h2 class="text-3xl font-extrabold text-gray-800">Login Pemilih</h2>
      <p class="text-sm text-gray-600 mt-1">Sistem E‑Voting Himpunan TI</p>
    </div>

    <form method="POST" class="space-y-5">
      <div class="relative">
        <i class="fas fa-id-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input
          type="text"
          name="nim"
          placeholder="Masukkan NIM"
          required
          class="pl-11 pr-4 py-2 w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:outline-none shadow-sm"
        >
      </div>

      <button
        type="submit"
        class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition duration-200 shadow-md"
      >
        <i class="fas fa-sign-in-alt"></i> Masuk
      </button>
    </form>

    <p class="text-xs text-gray-500 mt-6 text-center">Gunakan NIM aktif untuk login ke sistem e‑voting.</p>
  </div>

  <?php if (isset($error)): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: '<?php echo addslashes($error); ?>'
    });
  </script>
  <?php endif; ?>
</body>
</html>
