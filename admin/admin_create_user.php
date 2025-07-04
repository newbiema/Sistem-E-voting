<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
  header('Location: login.php');
  exit();
}

// Include library PhpSpreadsheet
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Handle single user addition
  if (isset($_POST['nim']) && isset($_POST['nama'])) {
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
  
  // Handle Excel import
  if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == UPLOAD_ERR_OK) {
    $file_name = $_FILES['excel_file']['name'];
    $file_tmp = $_FILES['excel_file']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validasi ekstensi file
    $allowed_ext = ['xlsx', 'xls', 'csv'];
    if (!in_array($file_ext, $allowed_ext)) {
      $error = "Format file tidak didukung. Harap upload file Excel (.xlsx, .xls) atau CSV (.csv)";
    } else {
      try {
        // Load file Excel
        $spreadsheet = IOFactory::load($file_tmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Skip header row jika ada
        $header = array_shift($rows);
        
        $imported = 0;
        $duplicates = 0;
        $errors = 0;
        
        foreach ($rows as $row) {
          // Skip row kosong
          if (empty($row[0]) || empty($row[1])) continue;
          
          $nim = mysqli_real_escape_string($conn, trim($row[0]));
          $nama = mysqli_real_escape_string($conn, trim($row[1]));
          
          // Validasi NIM
          if (!preg_match('/^[0-9]+$/', $nim)) {
            $errors++;
            continue;
          }
          
          // Cek duplikat NIM
          $check = mysqli_query($conn, "SELECT * FROM users WHERE nim = '$nim'");
          if (mysqli_num_rows($check) > 0) {
            $duplicates++;
            continue;
          }
          
          // Insert data
          if (mysqli_query($conn, "INSERT INTO users (nim, nama) VALUES ('$nim', '$nama')")) {
            $imported++;
          } else {
            $errors++;
          }
        }
        
        $success = "Import selesai!<br>";
        $success .= "Berhasil diimport: $imported user<br>";
        $success .= "Duplikat: $duplicates NIM<br>";
        $success .= "Error: $errors data";
        
      } catch (Exception $e) {
        $error = "Terjadi kesalahan saat memproses file: " . $e->getMessage();
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
  <link rel="shortcut icon" href="../img/hmif.png" type="image/x-icon">
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
      margin: 0;
    }

    .form-container {
      border-radius: 20px;
      box-shadow: 0 15px 30px rgba(79, 70, 229, 0.15);
      overflow: hidden;
      width: 100%;
      max-width: 500px;
      background: white;
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

    /* Tab Styles */
    .tab-nav {
      display: flex;
      border-bottom: 1px solid #e5e7eb;
      margin-bottom: 1.5rem;
    }

    .tab-btn {
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      font-size: 0.875rem;
      color: #6b7280;
      background: none;
      border: none;
      cursor: pointer;
      position: relative;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
    }

    .tab-btn.active {
      color: #4f46e5;
      font-weight: 600;
    }

    .tab-btn.active::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      right: 0;
      height: 2px;
      background: #4f46e5;
    }

    .tab-btn:not(.active):hover {
      color: #4f46e5;
      background: rgba(79, 70, 229, 0.05);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    /* Input Groups */
    .input-group {
      margin-bottom: 1.5rem;
    }

    .input-group label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #4b5563;
      font-size: 0.875rem;
    }

    .input-group .relative {
      position: relative;
    }

    .input-group input {
      width: 100%;
      padding: 0.75rem 1rem 0.75rem 2.5rem;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      transition: all 0.3s ease;
      font-size: 0.875rem;
    }

    .input-group input:focus {
      border-color: #4f46e5;
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
      outline: none;
    }

    .input-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
      font-size: 1rem;
    }

    /* File Input Styling */
    .file-input-container {
      position: relative;
      margin-bottom: 1.5rem;
    }

    .file-input-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #4b5563;
      font-size: 0.875rem;
      display: flex;
      align-items: center;
    }

    .file-input-wrapper {
      position: relative;
    }

    .file-input {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      background: white;
      cursor: pointer;
    }

    .file-input-icon {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
    }

    .file-instructions {
      margin-top: 1rem;
      padding: 1rem;
      background: #f9fafb;
      border-radius: 8px;
      font-size: 0.875rem;
      color: #4b5563;
    }

    .file-instructions h3 {
      font-weight: 600;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .file-instructions ul {
      padding-left: 1.25rem;
      margin: 0.5rem 0;
    }

    .file-instructions li {
      margin-bottom: 0.25rem;
    }

    .file-instructions a {
      color: #4f46e5;
      text-decoration: none;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
    }

    .file-instructions a:hover {
      text-decoration: underline;
    }

    /* Buttons */
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
      justify-content: center;
      gap: 0.5rem;
      color: #4f46e5;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
      margin-top: 1rem;
      padding: 0.75rem;
      width: 100%;
      border-radius: 10px;
    }

    .btn-back:hover {
      color: #4338ca;
      background: rgba(79, 70, 229, 0.05);
      transform: translateX(-3px);
    }

    /* Alerts */
    .alert {
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 0.875rem;
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

    .alert i {
      font-size: 1.25rem;
    }

    /* Header Decoration */
    .header-decoration {
      position: absolute;
      top: 0;
      right: 0;
      opacity: 0.1;
      font-size: 6rem;
      pointer-events: none;
    }

    /* Responsive Adjustments */
    @media (max-width: 480px) {
      .form-header {
        padding: 1.5rem 1rem 1rem;
      }
      
      .form-content {
        padding: 1.5rem;
      }
    
      .tab-btn {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
      }
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
      
      <!-- Tab Navigation -->
      <div class="tab-nav">
        <button type="button" class="tab-btn active" onclick="switchTab('single')">
          <i class="fas fa-user-plus mr-2"></i>Tambah Single User
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('import')">
          <i class="fas fa-file-import mr-2"></i>Import dari Excel
        </button>
      </div>
      
      <!-- Single User Form -->
      <div id="single-form" class="tab-content active">
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
        </form>
      </div>
      
      <!-- Import Form -->
      <div id="import-form" class="tab-content">
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
          <div class="file-input-container">
            <label for="excel_file" class="file-input-label">
              <i class="fas fa-file-excel text-green-500 mr-2"></i> File Excel/CSV
            </label>
            <div class="file-input-wrapper">
              <input type="file" id="excel_file" name="excel_file" class="file-input" accept=".xlsx,.xls,.csv" required>
              <div class="file-input-icon">
                <i class="fas fa-upload"></i>
              </div>
            </div>
            <div class="file-instructions">
              <h3><i class="fas fa-info-circle"></i> Petunjuk Format File:</h3>
              <ul>
                <li>File harus berformat Excel (.xlsx, .xls) atau CSV (.csv)</li>
                <li>Kolom pertama harus berisi NIM (hanya angka)</li>
                <li>Kolom kedua harus berisi Nama Lengkap</li>
                <li>Baris pertama akan dianggap sebagai header</li>
                <li><a href="../templates/user_template.xlsx"><i class="fas fa-download mr-1"></i>Download template Excel</a></li>
              </ul>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <button type="submit" class="btn-submit">
            <i class="fas fa-file-import"></i> Import Data
          </button>
        </form>
      </div>
      
      <a href="admin.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
      </a>
    </div>
  </div>

  <script>
    // Tab switching
    function switchTab(tabName) {
      // Hide all tab contents
      document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
      });
      
      // Deactivate all tab buttons
      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      
      // Activate selected tab
      document.getElementById(tabName + '-form').classList.add('active');
      document.querySelector(`.tab-btn[onclick="switchTab('${tabName}')"]`).classList.add('active');
    }

    // Form submission feedback
    document.querySelectorAll('form').forEach(form => {
      form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('.btn-submit');
        if (submitBtn) {
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
          submitBtn.disabled = true;
        }
      });
    });
    
    // Auto-uppercase for name field
    const nameInput = document.getElementById('nama');
    if (nameInput) {
      nameInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
      });
    }
    
    // NIM validation
    const nimInput = document.getElementById('nim');
    if (nimInput) {
      nimInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
      });
    }
  </script>
</body>
</html>