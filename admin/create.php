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
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
  <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg">
    <h2 class="text-2xl font-bold text-center mb-6 text-indigo-700"><i class="fas fa-user-plus"></i> Tambah Kandidat</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Nama Ketua</label>
        <input type="text" name="nama_ketua" class="w-full mt-1 border p-2 rounded" required>
      </div>
      <div>
        <label class="block text-sm font-medium">Nama Wakil</label>
        <input type="text" name="nama_wakil" class="w-full mt-1 border p-2 rounded" required>
      </div>
      <div>
        <label class="block text-sm font-medium">Visi</label>
        <textarea name="visi" class="w-full mt-1 border p-2 rounded" required></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium">Foto</label>
        <input type="file" name="foto" accept="image/*" class="w-full mt-1" required>
      </div>
      <div class="flex justify-between items-center">
        <a href="admin.php" class="text-sm text-gray-600 hover:underline"><i class="fas fa-arrow-left"></i> Kembali</a>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded flex items-center gap-2">
          <i class="fas fa-save"></i> Simpan
        </button>
      </div>
    </form>
  </div>
</body>
</html>
