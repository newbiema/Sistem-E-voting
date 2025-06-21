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
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f0f9ff 0%, #e6f7ff 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .form-container {
      border-radius: 20px;
      box-shadow: 0 15px 30px rgba(79, 70, 229, 0.15);
      overflow: hidden;
      width: 100%;
      max-width: 500px;
    }
    
    .form-header {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      padding: 2rem 2rem 1.5rem;
      text-align: center;
      color: white;
      position: relative;
    }
    
    .form-icon {
      background: rgba(255, 255, 255, 0.2);
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
    }
    
    .form-content {
      padding: 2rem;
      background: white;
    }
    
    .input-group {
      margin-bottom: 1.5rem;
      position: relative;
    }
    
    .input-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #4b5563;
    }
    
    .input-group input {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      transition: all 0.3s ease;
    }
    
    .input-group input:focus {
      border-color: #4f46e5;
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
      outline: none;
    }
    
    .btn-submit {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 10px;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      width: 100%;
      border: none;
      cursor: pointer;
      font-size: 1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
      margin-top: 1rem;
    }
    
    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 10px rgba(79, 70, 229, 0.3);
    }
    
    .btn-back {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: #4f46e5;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
      justify-content: center;
      margin-top: 1rem;
    }
    
    .btn-back:hover {
      color: #4338ca;
      transform: translateX(-3px);
    }
    
    .alert {
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .alert-error {
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #fecaca;
    }
    
    .alert-success {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
    }
    
    .input-icon {
      position: absolute;
      right: 15px;
      top: 38px;
      color: #9ca3af;
    }
    
    .header-decoration {
      position: absolute;
      top: 0;
      right: 0;
      opacity: 0.1;
      font-size: 6rem;
    }
  </style>
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