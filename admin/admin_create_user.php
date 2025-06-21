<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
  header('Location: login.php');
  exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nim = mysqli_real_escape_string($conn, $_POST['nim']);
  $nama = mysqli_real_escape_string($conn, $_POST['nama']);
  
  // Validasi NIM
  if (empty($nim) || empty($nama)) {
    $error = "NIM dan Nama harus diisi!";
  } else if (!preg_match('/^[0-9]+$/', $nim)) {
    $error = "NIM harus berupa angka!";
  } else {
    // Cek apakah NIM sudah ada
    $check = mysqli_query($conn, "SELECT * FROM users WHERE nim = '$nim'");
    if (mysqli_num_rows($check) > 0) {
      $error = "NIM sudah terdaftar!";
    } else {
      // Tambahkan user baru
      if (mysqli_query($conn, "INSERT INTO users (nim, nama) VALUES ('$nim', '$nama')")) {
        $success = "User berhasil ditambahkan!";
      } else {
        $error = "Terjadi kesalahan: " . mysqli_error($conn);
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah User</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/admin_create_user.css">
</head>
<body>
  <div class="form-container">
    <div class="form-header">
      <div class="header-decoration">
        <i class="fas fa-user-plus"></i>
      </div>
      <div class="form-icon">
        <i class="fas fa-user-graduate text-3xl"></i>
      </div>
      <h1 class="text-2xl font-bold">Tambah User Baru</h1>
      <p class="text-indigo-100 mt-1">Tambahkan user untuk hak voting</p>
    </div>
    
    <div class="form-content">
      <?php if ($error): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle text-xl"></i>
          <div><?php echo $error; ?></div>
        </div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle text-xl"></i>
          <div><?php echo $success; ?></div>
        </div>
      <?php endif; ?>
      
      <form method="POST" class="space-y-4">
        <!-- Form NIM -->
        <div class="input-group">
          <label for="nim" class="flex items-center gap-2">
            <i class="fas fa-id-card text-indigo-500"></i> NIM
          </label>
          <div class="relative">
            <input type="text" id="nim" name="nim" placeholder="Masukkan NIM" required>
            <div class="input-icon">
              <i class="fas fa-hashtag"></i>
            </div>
          </div>
          <p class="text-sm text-gray-500 mt-1">*NIM harus unik dan hanya berisi angka</p>
        </div>
        
        <!-- Form Nama -->
        <div class="input-group">
          <label for="nama" class="flex items-center gap-2">
            <i class="fas fa-user text-indigo-500"></i> Nama Lengkap
          </label>
          <div class="relative">
            <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
            <div class="input-icon">
              <i class="fas fa-signature"></i>
            </div>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <button type="submit" class="btn-submit">
          <i class="fas fa-user-plus"></i> Tambahkan User
        </button>
        
        <a href="admin.php" class="btn-back">
          <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
      </form>
    </div>
  </div>

  <script>
    // Form submission feedback
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function() {
      const submitBtn = document.querySelector('.btn-submit');
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
      submitBtn.disabled = true;
    });
    
    // Auto-uppercase for name field
    const nameInput = document.getElementById('nama');
    nameInput.addEventListener('input', function() {
      this.value = this.value.toUpperCase();
    });
    
    // NIM validation
    const nimInput = document.getElementById('nim');
    nimInput.addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '');
    });
  </script>
</body>
</html>