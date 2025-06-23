<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
  header('Location: login.php');
  exit();
}

$type = isset($_GET['type']) && in_array($_GET['type'], ['ketua', 'wakil']) ? $_GET['type'] : 'ketua';
$table = $type === 'ketua' ? 'candidates_ketua' : 'candidates_wakil';

$color = $type === 'ketua' ? 'bg-yellow-500' : 'bg-blue-500';
$icon = $type === 'ketua' ? 'crown' : 'user-friends';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = mysqli_real_escape_string($conn, $_POST['nama']);
  $visi = mysqli_real_escape_string($conn, $_POST['visi']);
  $foto_name = '';

  if (!empty($_FILES['foto']['name'])) {
    $foto_name = 'uploads/' . time() . '_' . basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], '../' . $foto_name);
  }

  $query = "INSERT INTO $table (nama, visi, foto) VALUES ('$nama', '$visi', '$foto_name')";
  mysqli_query($conn, $query);
  header("Location: admin.php?success=added");
  exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Kandidat <?php echo ucfirst($type); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function(){
        const output = document.getElementById('imagePreview');
        output.src = reader.result;
        output.classList.remove('hidden');
        document.getElementById('uploadIcon').classList.add('hidden');
        document.getElementById('fileName').textContent = event.target.files[0].name;
      };
      reader.readAsDataURL(event.target.files[0]);
    }
    
    function resetForm() {
      document.getElementById('imagePreview').classList.add('hidden');
      document.getElementById('uploadIcon').classList.remove('hidden');
      document.getElementById('fileName').textContent = '';
      document.getElementById('foto').value = '';
    }
  </script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f0f9ff, #e6f7ff);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .form-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      max-width: 700px;
      width: 100%;
    }
    
    .form-header {
      background: linear-gradient(135deg, 
        <?php echo $type === 'ketua' ? '#f59e0b' : '#3b82f6'; ?>, 
        <?php echo $type === 'ketua' ? '#d97706' : '#2563eb'; ?>);
      color: white;
      padding: 30px;
      text-align: center;
      position: relative;
    }
    
    .form-header h1 {
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 10px;
      position: relative;
      z-index: 2;
    }
    
    .form-header p {
      opacity: 0.9;
      position: relative;
      z-index: 2;
    }
    
    .form-icon {
      position: absolute;
      right: 30px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 80px;
      opacity: 0.2;
      z-index: 1;
    }
    
    .form-body {
      padding: 30px;
    }
    
    .form-group {
      margin-bottom: 25px;
    }
    
    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #334155;
      font-size: 15px;
    }
    
    .form-input {
      width: 100%;
      padding: 14px;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 16px;
      transition: all 0.3s;
    }
    
    .form-input:focus {
      border-color: <?php echo $type === 'ketua' ? '#f59e0b' : '#3b82f6'; ?>;
      box-shadow: 0 0 0 4px <?php echo $type === 'ketua' ? 'rgba(245, 158, 11, 0.2)' : 'rgba(59, 130, 246, 0.2)'; ?>;
      outline: none;
    }
    
    textarea.form-input {
      min-height: 120px;
      resize: vertical;
    }
    
    .upload-area {
      border: 2px dashed #cbd5e1;
      border-radius: 12px;
      padding: 30px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s;
      background: #f8fafc;
      position: relative;
      overflow: hidden;
    }
    
    .upload-area:hover {
      border-color: <?php echo $type === 'ketua' ? '#f59e0b' : '#3b82f6'; ?>;
      background: <?php echo $type === 'ketua' ? '#fffbeb' : '#eff6ff'; ?>;
    }
    
    .upload-icon {
      font-size: 48px;
      color: #94a3b8;
      margin-bottom: 15px;
    }
    
    .upload-text {
      color: #64748b;
      margin-bottom: 10px;
    }
    
    .btn-upload {
      background: <?php echo $type === 'ketua' ? '#f59e0b' : '#3b82f6'; ?>;
      color: white;
      padding: 8px 20px;
      border-radius: 8px;
      font-weight: 500;
      display: inline-block;
      transition: all 0.3s;
    }
    
    .btn-upload:hover {
      background: <?php echo $type === 'ketua' ? '#e69008' : '#2563eb'; ?>;
      transform: translateY(-2px);
    }
    
    .file-name {
      color: #475569;
      font-size: 14px;
      margin-top: 15px;
    }
    
    .image-preview {
      max-width: 100%;
      border-radius: 8px;
      margin-top: 15px;
      display: none;
      max-height: 200px;
      object-fit: cover;
      width: 100%;
    }
    
    .btn-reset {
      background: #ef4444;
      color: white;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 14px;
      margin-top: 10px;
      display: inline-block;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .btn-reset:hover {
      background: #dc2626;
    }
    
    .form-actions {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }
    
    .btn-submit {
      background: <?php echo $type === 'ketua' ? '#f59e0b' : '#3b82f6'; ?>;
      color: white;
      padding: 14px 28px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 16px;
      flex: 1;
      transition: all 0.3s;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    
    .btn-submit:hover {
      background: <?php echo $type === 'ketua' ? '#e69008' : '#2563eb'; ?>;
      transform: translateY(-3px);
      box-shadow: 0 5px 15px <?php echo $type === 'ketua' ? 'rgba(245, 158, 11, 0.4)' : 'rgba(59, 130, 246, 0.4)'; ?>;
    }
    
    .btn-cancel {
      background: white;
      color: #64748b;
      padding: 14px 28px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 16px;
      border: 2px solid #e2e8f0;
      flex: 1;
      transition: all 0.3s;
      text-align: center;
    }
    
    .btn-cancel:hover {
      background: #f1f5f9;
      border-color: #cbd5e1;
      transform: translateY(-3px);
    }
    
    .badge {
      background: <?php echo $type === 'ketua' ? '#fef3c7' : '#dbeafe'; ?>;
      color: <?php echo $type === 'ketua' ? '#92400e' : '#1e40af'; ?>;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 14px;
      display: inline-block;
      margin-bottom: 20px;
    }
    
    @media (max-width: 640px) {
      .form-header {
        padding: 20px;
      }
      
      .form-icon {
        font-size: 60px;
        right: 20px;
      }
      
      .form-body {
        padding: 20px;
      }
      
      .form-actions {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>
  <div class="form-container">
    <div class="form-header">
      <div class="form-icon">
        <i class="fas fa-<?php echo $icon; ?>"></i>
      </div>
      <h1>Tambah Kandidat <?php echo ucfirst($type); ?></h1>
      <p>Lengkapi data kandidat <?php echo $type; ?> pemilihan HMIF</p>
    </div>
    
    <div class="form-body">
      <div class="badge">
        <i class="fas fa-<?php echo $type === 'ketua' ? 'crown' : 'user-friends'; ?> mr-2"></i>
        <?php echo $type === 'ketua' ? 'Calon Ketua' : 'Calon Wakil'; ?>
      </div>
      
      <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="nama" class="form-label">Nama Lengkap</label>
          <input type="text" name="nama" id="nama" required class="form-input" placeholder="Masukkan nama kandidat">
        </div>
        
        <div class="form-group">
          <label for="visi" class="form-label">Visi & Misi</label>
          <textarea name="visi" id="visi" required class="form-input" placeholder="Tuliskan visi dan misi kandidat"></textarea>
        </div>
        
        <div class="form-group">
          <label class="form-label">Foto Kandidat</label>
          <div class="upload-area" onclick="document.getElementById('foto').click()">
            <div id="uploadIcon" class="upload-icon">
              <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <p class="upload-text">Klik untuk mengunggah foto atau tarik file ke sini</p>
            <p class="btn-upload">Pilih File</p>
            <p class="file-name" id="fileName"></p>
            
            <img id="imagePreview" class="image-preview" alt="Preview gambar">
            
            <input type="file" name="foto" id="foto" accept="image/*" class="hidden" onchange="previewImage(event)">
          </div>
          <div id="resetContainer" class="hidden text-center">
            <div class="btn-reset" onclick="resetForm()">
              <i class="fas fa-times mr-2"></i>Hapus Gambar
            </div>
          </div>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn-submit">
            <i class="fas fa-save mr-2"></i> Simpan Kandidat
          </button>
          <a href="admin.php" class="btn-cancel">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
          </a>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Menampilkan tombol reset ketika gambar dipilih
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function(){
        const output = document.getElementById('imagePreview');
        output.src = reader.result;
        output.classList.remove('hidden');
        document.getElementById('uploadIcon').classList.add('hidden');
        document.getElementById('fileName').textContent = event.target.files[0].name;
        document.getElementById('resetContainer').classList.remove('hidden');
      };
      reader.readAsDataURL(event.target.files[0]);
    }
    
    function resetForm() {
      document.getElementById('imagePreview').classList.add('hidden');
      document.getElementById('uploadIcon').classList.remove('hidden');
      document.getElementById('fileName').textContent = '';
      document.getElementById('foto').value = '';
      document.getElementById('resetContainer').classList.add('hidden');
    }
    
    // Validasi form sebelum submit
    document.querySelector('form').addEventListener('submit', function(e) {
      const nama = document.getElementById('nama').value.trim();
      const visi = document.getElementById('visi').value.trim();
      
      if (!nama || !visi) {
        e.preventDefault();
        alert('Harap lengkapi semua kolom yang wajib diisi!');
      }
    });
  </script>
</body>
</html>