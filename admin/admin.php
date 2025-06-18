<?php
// admin.php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
  header('Location: login_admin.php');
  exit();
}

// DELETE CANDIDATE HANDLER
if (isset($_GET['hapus'])) {
  $id = intval($_GET['hapus']);
  mysqli_query($conn, "DELETE FROM candidates WHERE id = $id");
  header('Location: admin.php?success=deleted');
  exit();
}

// FETCH DATA
$candidates = mysqli_query($conn, "SELECT c.*, COUNT(v.id) AS total_suara FROM candidates c LEFT JOIN votes v ON c.id = v.candidate_id GROUP BY c.id ORDER BY total_suara DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-indigo-100 min-h-screen text-gray-800">
  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fas fa-chart-bar text-indigo-600"></i> Dashboard Admin</h1>
      <div class="flex items-center gap-3">
        <a href="create.php" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2">
          <i class="fas fa-plus"></i> Tambah Kandidat
        </a>
        <a href="logout_admin.php" class="text-red-600 hover:text-red-700 text-sm font-semibold flex items-center gap-1">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 py-8 space-y-10">
    <section>
      <h2 class="text-xl font-semibold mb-4 flex items-center gap-2"><i class="fas fa-users text-indigo-600"></i> Daftar Kandidat</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($row = mysqli_fetch_assoc($candidates)): ?>
          <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
            <div class="p-6 flex flex-col items-center text-center gap-4">
              <img src="../<?php echo $row['foto']; ?>" alt="foto" class="w-24 h-24 rounded-full object-cover border-4 border-indigo-500">
              <div>
                <h3 class="font-bold text-lg"><?php echo $row['nama_ketua']; ?></h3>
                <p class="text-sm text-gray-600 -mt-1">Ketua</p>
              </div>
              <div>
                <h3 class="font-bold text-lg"><?php echo $row['nama_wakil']; ?></h3>
                <p class="text-sm text-gray-600 -mt-1">Wakil</p>
              </div>
              <p class="text-gray-700 text-sm italic line-clamp-3">“<?php echo nl2br($row['visi']); ?>”</p>
              <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-semibold">
                <i class="fas fa-vote-yea"></i> <?php echo $row['total_suara']; ?> suara
              </span>
            </div>
            <div class="border-t flex">
              <a href="admin_edit.php?id=<?php echo $row['id']; ?>" class="w-1/2 py-2 text-center text-blue-600 hover:bg-blue-50 text-sm font-medium flex items-center justify-center gap-1">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus kandidat ini?')" class="w-1/2 py-2 text-center text-red-600 hover:bg-red-50 text-sm font-medium flex items-center justify-center gap-1">
                <i class="fas fa-trash"></i> Hapus
              </a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </section>
  </main>

  <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Dihapus!',
        text: 'Kandidat berhasil dihapus.'
      });
    </script>
  <?php endif; ?>
</body>
</html>
