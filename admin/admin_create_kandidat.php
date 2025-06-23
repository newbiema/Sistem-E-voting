<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
  header('Location: login.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama_ketua = mysqli_real_escape_string($conn, $_POST['nama_ketua']);
  $nama_wakil = mysqli_real_escape_string($conn, $_POST['nama_wakil']);
  $visi = mysqli_real_escape_string($conn, $_POST['visi']);
  $foto = $_FILES['foto']['name'];
  $tmp = $_FILES['foto']['tmp_name'];

  move_uploaded_file($tmp, "../img/".$foto);
  mysqli_query($conn, "INSERT INTO candidates (nama_ketua, nama_wakil, visi, foto) VALUES ('$nama_ketua', '$nama_wakil', '$visi', 'img/$foto')");
  header("Location: admin.php?success=added");
  exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Kandidat</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/create_kandidat.css">

</head>
<body class="min-h-screen flex items-center justify-center p-4">
  <div class="form-container w-full max-w-2xl">
    <div class="form-header">
      <div class="form-icon">
        <i class="fas fa-user-plus text-3xl"></i>
      </div>
      <h1 class="text-2xl font-bold">Tambah Kandidat Baru</h1>
      <p class="text-indigo-100 mt-1">Isi data kandidat untuk pemilihan ketua</p>
    </div>
    
    <div class="form-content">
      <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <!-- Form Ketua -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="input-group">
            <label for="nama_ketua" class="flex items-center gap-2">
              <i class="fas fa-crown text-yellow-500"></i> Nama Ketua
            </label>
            <input type="text" id="nama_ketua" name="nama_ketua" placeholder="Masukkan nama ketua" required>
          </div>
          
          <!-- Form Wakil -->
          <div class="input-group">
            <label for="nama_wakil" class="flex items-center gap-2">
              <i class="fas fa-user-friends text-blue-500"></i> Nama Wakil
            </label>
            <input type="text" id="nama_wakil" name="nama_wakil" placeholder="Masukkan nama wakil" required>
          </div>
        </div>
        
        <!-- Visi Misi -->
        <div class="input-group">
          <label for="visi" class="flex items-center gap-2">
            <i class="fas fa-bullhorn text-purple-500"></i> Visi & Misi
          </label>
          <textarea id="visi" name="visi" rows="4" placeholder="Tuliskan visi dan misi kandidat..." required></textarea>
        </div>
        
        <!-- Foto Upload -->
        <div class="input-group">
          <label class="flex items-center gap-2">
            <i class="fas fa-camera text-indigo-500"></i> Foto Kandidat
          </label>
          <div class="file-input-container" id="dropArea">
            <label for="foto" class="file-input-label">
              <div class="file-input-icon">
                <i class="fas fa-cloud-upload-alt"></i>
              </div>
              <p class="file-input-text">Seret & lepas gambar atau klik untuk memilih</p>
              <div class="file-input-btn">
                <i class="fas fa-folder-open mr-2"></i> Pilih Gambar
              </div>
              <span id="fileName" class="file-name"></span>
            </label>
            <input type="file" id="foto" name="foto" accept="image/*" class="hidden" required>
          </div>
          <div class="preview-container" id="previewContainer">
            <img id="previewImage" class="preview-image" alt="Preview">
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
          <a href="admin.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
          </a>
          <button type="submit" class="btn-submit">
            <i class="fas fa-save"></i> Simpan Kandidat
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // File upload preview
    const fileInput = document.getElementById('foto');
    const fileName = document.getElementById('fileName');
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('previewImage');
    const dropArea = document.getElementById('dropArea');
    
    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const file = this.files[0];
        fileName.textContent = file.name;
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
          previewImage.src = e.target.result;
          previewContainer.style.display = 'block';
        }
        reader.readAsDataURL(file);
      }
    });
    
    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
      dropArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
      dropArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
      dropArea.classList.add('dragover');
    }
    
    function unhighlight() {
      dropArea.classList.remove('dragover');
    }
    
    dropArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;
      
      if (files.length) {
        fileInput.files = files;
        fileName.textContent = files[0].name;
        
        // Trigger change event for preview
        const event = new Event('change');
        fileInput.dispatchEvent(event);
      }
    }
    
    // Form submission feedback
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function() {
      const submitBtn = document.querySelector('.btn-submit');
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
      submitBtn.disabled = true;
    });
  </script>
</body>
</html>