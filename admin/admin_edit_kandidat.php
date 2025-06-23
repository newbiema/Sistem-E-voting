<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
  header('Location: login.php');
  exit();
}

// Ambil ID kandidat dari parameter URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data kandidat dari database
$query = "SELECT * FROM kandidat WHERE id = $id";
$result = mysqli_query($conn, $query);
$kandidat = mysqli_fetch_assoc($result);

// Jika kandidat tidak ditemukan, redirect ke halaman kandidat
if (!$kandidat) {
  $_SESSION['error'] = "Kandidat tidak ditemukan!";
  header('Location: kandidat.php');
  exit();
}

$errors = [];
$success = '';

// Proses form jika ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nama = mysqli_real_escape_string($conn, $_POST['nama']);
  $nim = mysqli_real_escape_string($conn, $_POST['nim']);
  $angkatan = mysqli_real_escape_string($conn, $_POST['angkatan']);
  $visi = mysqli_real_escape_string($conn, $_POST['visi']);
  $misi = mysqli_real_escape_string($conn, $_POST['misi']);
  $program_kerja = mysqli_real_escape_string($conn, $_POST['program_kerja']);
  
  // Validasi input
  if (empty($nama)) $errors[] = "Nama kandidat harus diisi!";
  if (empty($nim)) $errors[] = "NIM kandidat harus diisi!";
  if (empty($angkatan)) $errors[] = "Angkatan kandidat harus diisi!";
  if (empty($visi)) $errors[] = "Visi kandidat harus diisi!";
  if (empty($misi)) $errors[] = "Misi kandidat harus diisi!";
  
  // Tangani upload foto jika ada
  $foto = $kandidat['foto'];
  
  if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['foto'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png'];
    
    if (in_array(strtolower($ext), $allowed)) {
      // Buat nama file unik
      $new_filename = "kandidat_" . $id . "_" . time() . "." . $ext;
      $target_path = "../uploads/kandidat/" . $new_filename;
      
      // Pindahkan file ke folder uploads
      if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Hapus foto lama jika ada
        if ($foto && file_exists("../uploads/kandidat/" . $foto)) {
          unlink("../uploads/kandidat/" . $foto);
        }
        $foto = $new_filename;
      } else {
        $errors[] = "Gagal mengunggah foto!";
      }
    } else {
      $errors[] = "Format file tidak didukung! Hanya JPG, JPEG, dan PNG yang diperbolehkan.";
    }
  }
  
  // Jika tidak ada error, update data
  if (empty($errors)) {
    $update_query = "UPDATE kandidat SET 
                    nama = '$nama',
                    nim = '$nim',
                    angkatan = '$angkatan',
                    foto = '$foto',
                    visi = '$visi',
                    misi = '$misi',
                    program_kerja = '$program_kerja'
                    WHERE id = $id";
    
    if (mysqli_query($conn, $update_query)) {
      $_SESSION['success'] = "Data kandidat berhasil diperbarui!";
      header('Location: kandidat.php');
      exit();
    } else {
      $errors[] = "Error: " . mysqli_error($conn);
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Kandidat - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="shortcut icon" href="../img/hmif.png" type="image/x-icon">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .file-input-wrapper {
      position: relative;
      overflow: hidden;
      display: inline-block;
    }
    
    .file-input-wrapper input[type=file] {
      position: absolute;
      left: 0;
      top: 0;
      opacity: 0;
      cursor: pointer;
      height: 100%;
      width: 100%;
    }
    
    .preview-container {
      max-width: 200px;
      margin: 0 auto;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .preview-container img {
      width: 100%;
      height: auto;
      display: block;
    }
    
    .error-message {
      color: #e53e3e;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }
    
    .upload-label {
      display: inline-block;
      padding: 10px 20px;
      background-color: #4f46e5;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .upload-label:hover {
      background-color: #4338ca;
      transform: translateY(-2px);
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-2xl bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-700 p-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold"><i class="fas fa-user-edit mr-2"></i>Edit Kandidat</h2>
          <p class="text-indigo-200">Perbarui informasi kandidat pemilihan</p>
        </div>
        <a href="kandidat.php" class="text-white hover:text-indigo-200">
          <i class="fas fa-times text-xl"></i>
        </a>
      </div>
    </div>
    
    <div class="p-6">
      <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Terdapat masalah dengan input Anda</h3>
              <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                  <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Foto Kandidat -->
          <div class="col-span-1">
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Foto Kandidat</label>
              <div class="preview-container mb-4">
                <?php if ($kandidat['foto']): ?>
                  <img src="../uploads/kandidat/<?php echo htmlspecialchars($kandidat['foto']); ?>" 
                       alt="Foto <?php echo htmlspecialchars($kandidat['nama']); ?>">
                <?php else: ?>
                  <div class="bg-gray-200 border-2 border-dashed rounded-xl w-full h-48 flex items-center justify-center text-gray-500">
                    <i class="fas fa-user text-4xl"></i>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="file-input-wrapper">
                <label class="upload-label">
                  <i class="fas fa-upload mr-2"></i> Pilih Foto Baru
                  <input type="file" name="foto" id="foto" accept="image/*" class="hidden">
                </label>
              </div>
              <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG. Maksimal 2MB</p>
            </div>
          </div>
          
          <!-- Data Kandidat -->
          <div class="col-span-1">
            <div class="mb-4">
              <label for="nama" class="block text-gray-700 font-medium mb-2">Nama Lengkap</label>
              <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($kandidat['nama']); ?>" 
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            
            <div class="mb-4">
              <label for="nim" class="block text-gray-700 font-medium mb-2">NIM</label>
              <input type="text" id="nim" name="nim" value="<?php echo htmlspecialchars($kandidat['nim']); ?>" 
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            
            <div class="mb-4">
              <label for="angkatan" class="block text-gray-700 font-medium mb-2">Angkatan</label>
              <input type="text" id="angkatan" name="angkatan" value="<?php echo htmlspecialchars($kandidat['angkatan']); ?>" 
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
          </div>
          
          <!-- Visi, Misi, Program Kerja -->
          <div class="col-span-1 md:col-span-2">
            <div class="mb-4">
              <label for="visi" class="block text-gray-700 font-medium mb-2">Visi</label>
              <textarea id="visi" name="visi" rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required><?php echo htmlspecialchars($kandidat['visi']); ?></textarea>
            </div>
            
            <div class="mb-4">
              <label for="misi" class="block text-gray-700 font-medium mb-2">Misi</label>
              <textarea id="misi" name="misi" rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required><?php echo htmlspecialchars($kandidat['misi']); ?></textarea>
            </div>
            
            <div class="mb-6">
              <label for="program_kerja" class="block text-gray-700 font-medium mb-2">Program Kerja</label>
              <textarea id="program_kerja" name="program_kerja" rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?php echo htmlspecialchars($kandidat['program_kerja']); ?></textarea>
            </div>
          </div>
        </div>
        
        <div class="flex justify-between mt-6">
          <a href="kandidat.php" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-6 rounded-lg shadow flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
          </a>
          <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow flex items-center">
            <i class="fas fa-save mr-2"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Preview gambar saat memilih file
    document.getElementById('foto').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const previewContainer = document.querySelector('.preview-container');
          previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview foto">`;
        }
        reader.readAsDataURL(file);
      }
    });
    
    // Tampilkan notifikasi jika ada session message
    <?php if (isset($_SESSION['success'])): ?>
      Swal.fire({
        icon: 'success',
        title: 'Sukses!',
        text: '<?php echo addslashes($_SESSION['success']); ?>',
        showConfirmButton: false,
        timer: 3000
      });
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '<?php echo addslashes($_SESSION['error']); ?>',
        showConfirmButton: true
      });
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
  </script>
</body>
</html>