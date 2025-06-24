<?php
session_start();
include '../db.php';

// Cek login admin
if (!isset($_SESSION['admin_login'])) {
  header('Location: login.php');
  exit();
}

// Validasi NIM
if (!isset($_GET['nim'])) {
  header('Location: daftar_user.php');
  exit();
}

$nim = $_GET['nim'];
$user = mysqli_query($conn, "SELECT * FROM users WHERE nim = '$nim'");
$data = mysqli_fetch_assoc($user);

if (!$data) {
  // Set session error untuk ditampilkan di halaman daftar user
  $_SESSION['error'] = "User dengan NIM $nim tidak ditemukan";
  header('Location: daftar_user.php');
  exit();
}

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = mysqli_real_escape_string($conn, $_POST['nama']);

  $update = mysqli_query($conn, "UPDATE users SET nama='$nama' WHERE nim='$nim'");

  if ($update) {
    $_SESSION['success'] = "Data user $nim berhasil diperbarui";
    header('Location: daftar_user.php');
    exit();
  } else {
    $error_message = "Gagal menyimpan data: " . mysqli_error($conn);
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit User - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="shortcut icon" href="../img/hmif.png" type="image/x-icon">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f0f4ff 0%, #e6f0ff 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      width: 100%;
      max-width: 500px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
    }
    
    .card-header {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      color: white;
      padding: 25px 30px;
      position: relative;
    }
    
    .card-header::after {
      content: "";
      position: absolute;
      bottom: -20px;
      left: 0;
      width: 100%;
      height: 40px;
      background: linear-gradient(135deg, transparent 50%, white 50%);
    }
    
    .card-body {
      padding: 30px;
    }
    
    .form-group {
      margin-bottom: 1.8rem;
      position: relative;
    }
    
    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #4b5563;
      font-size: 0.95rem;
      padding-left: 8px;
      border-left: 3px solid #4f46e5;
    }
    
    .form-control {
      width: 100%;
      padding: 14px 18px 14px 50px;
      border: 1px solid #d1d5db;
      border-radius: 12px;
      font-size: 1rem;
      transition: all 0.3s;
      background-color: #f9fafb;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .form-control:focus {
      outline: none;
      border-color: #818cf8;
      box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.2);
      background-color: white;
    }
    
    .input-icon {
      position: absolute;
      left: 16px;
      top: 40px;
      color: #6b7280;
      font-size: 1.2rem;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 14px 28px;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      border: none;
      font-size: 1rem;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      color: white;
      box-shadow: 0 4px 8px rgba(79, 70, 229, 0.25);
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
      box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3);
      transform: translateY(-2px);
    }
    
    .btn-outline {
      background: transparent;
      color: #4f46e5;
      border: 1px solid #d1d5db;
      padding: 13px 26px;
    }
    
    .btn-outline:hover {
      background: #f5f7ff;
      border-color: #c7d2fe;
      transform: translateY(-2px);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }
    
    .btn i {
      margin-right: 10px;
    }
    
    .btn-group {
      display: flex;
      justify-content: space-between;
      margin-top: 2.5rem;
    }
    
    .error-message {
      color: #ef4444;
      background: #fef2f2;
      padding: 14px;
      border-radius: 10px;
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      font-size: 0.95rem;
      border-left: 4px solid #ef4444;
    }
    
    .error-message i {
      margin-right: 12px;
      font-size: 1.3rem;
    }
    
    .success-message {
      color: #10b981;
      background: #ecfdf5;
      padding: 14px;
      border-radius: 10px;
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      font-size: 0.95rem;
      border-left: 4px solid #10b981;
    }
    
    .success-message i {
      margin-right: 12px;
      font-size: 1.3rem;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="card-header">
      <h1 class="text-2xl font-bold flex items-center">
        <i class="fas fa-user-edit mr-3"></i>
        Edit Pengguna
      </h1>
      <p class="mt-2 text-indigo-100 opacity-90 text-sm">Perbarui informasi pengguna</p>
    </div>
    
    <div class="card-body">
      <?php if (isset($error_message)) : ?>
        <div class="error-message">
          <i class="fas fa-exclamation-circle"></i>
          <?= $error_message ?>
        </div>
      <?php endif; ?>
      
      <?php if ($data) : ?>
        <form method="POST" id="editForm">
          <div class="form-group">
            <label for="nim" class="form-label">NIM</label>
            <div class="relative">
              <i class="fas fa-id-card input-icon"></i>
              <input 
                type="text" 
                id="nim" 
                name="nim" 
                value="<?= htmlspecialchars($data['nim']) ?>" 
                class="form-control"
                readonly
              >
            </div>
          </div>
          
          <div class="form-group">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <div class="relative">
              <i class="fas fa-user input-icon"></i>
              <input 
                type="text" 
                id="nama" 
                name="nama" 
                value="<?= htmlspecialchars($data['nama']) ?>" 
                class="form-control"
                placeholder="Masukkan nama lengkap"
                required
                minlength="3"
              >
            </div>
          </div>
          
          <div class="btn-group">
            <a href="daftar_user.php" class="btn btn-outline">
              <i class="fas fa-arrow-left"></i>
              Kembali
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i>
              Simpan Perubahan
            </button>
          </div>
        </form>
      <?php else : ?>
        <div class="text-center py-6">
          <div class="mb-5">
            <i class="fas fa-user-slash text-5xl text-indigo-500"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-700">User Tidak Ditemukan</h3>
          <p class="text-gray-500 mt-2">Data user dengan NIM tersebut tidak ditemukan</p>
          <div class="mt-6">
            <a href="daftar_user.php" class="btn btn-primary">
              <i class="fas fa-arrow-left"></i>
              Kembali ke Daftar User
            </a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
  
  <script>
    // Form validation
    const editForm = document.getElementById('editForm');
    if (editForm) {
      editForm.addEventListener('submit', function(e) {
        const namaInput = document.getElementById('nama');
        const namaValue = namaInput.value.trim();
        
        if (namaValue.length < 3) {
          e.preventDefault();
          Swal.fire({
            icon: 'error',
            title: 'Nama Tidak Valid',
            text: 'Nama harus terdiri dari minimal 3 karakter',
            showConfirmButton: true,
            confirmButtonColor: '#4f46e5',
            background: '#ffffff',
            color: '#1f2937'
          });
          namaInput.focus();
        }
      });
    }
  </script>
</body>
</html>