<?php
// voter_list.php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
  header('Location: login.php');
  exit();
}

// voter_list.php - Perbaikan query
$query = "SELECT u.id, u.nim, u.nama, 
          (CASE WHEN v.id IS NOT NULL THEN 'Sudah Memilih' ELSE 'Belum Memilih' END) AS status_vote
          FROM users u
          LEFT JOIN votes v ON u.nim = v.nim  
          ORDER BY u.id DESC";

$voters = mysqli_query($conn, $query);
if (!$voters) {
  die("Query error: " . mysqli_error($conn));
}

$total_voters = mysqli_num_rows($voters);

// Perhitungan jumlah yang sudah memilih juga perlu disesuaikan
$voted_count_result = mysqli_query($conn, "SELECT COUNT(DISTINCT nim) as count FROM votes");
if (!$voted_count_result) {
  die("Query error: " . mysqli_error($conn));
}
$voted_count = mysqli_fetch_assoc($voted_count_result)['count'];
$not_voted = $total_voters - $voted_count;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Pemilih - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="shortcut icon" href="../img/hmif.png" type="image/x-icon">\
  <link rel="stylesheet" href="../css/daftar_user.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex">
  <!-- Sidebar -->
  <div class="w-64 bg-gradient-to-b from-indigo-800 to-purple-900 text-white min-h-screen fixed hidden md:block">
    <div class="p-5 flex items-center gap-3 border-b border-indigo-700">
      <img src="../img/hmif.png" alt="Logo" class="w-10 h-10">
      <div>
        <h1 class="font-bold text-xl">Admin Panel</h1>
        <p class="text-xs text-indigo-300">Sistem E-Voting HMIF</p>
      </div>
    </div>
    
    <nav class="mt-6">
      <a href="admin.php" class="sidebar-link flex items-center gap-3 py-3 px-6 text-sm">
        <i class="fas fa-chart-bar"></i>
        Dashboard
      </a>
      <a href="daftar_user.php" class="sidebar-link active flex items-center gap-3 py-3 px-6 text-sm">
        <i class="fas fa-users"></i>
        Daftar Pemilih
      </a>
    </nav>
    
    <div class="absolute bottom-0 w-full p-5 border-t border-indigo-700">
      <a href="logout.php" class="flex items-center gap-2 text-sm hover:text-indigo-200">
        <i class="fas fa-sign-out-alt"></i> Keluar
      </a>
    </div>
  </div>
  
  <!-- Mobile sidebar toggle -->
  <div class="md:hidden fixed top-4 left-4 z-50">
    <button id="sidebarToggle" class="bg-indigo-600 text-white p-2 rounded-lg">
      <i class="fas fa-bars"></i>
    </button>
  </div>
  
  <!-- Mobile sidebar -->
  <div id="mobileSidebar" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-40 hidden">
    <div class="w-64 bg-gradient-to-b from-indigo-800 to-purple-900 text-white min-h-screen">
      <div class="p-5 flex justify-between items-center border-b border-indigo-700">
        <div class="flex items-center gap-3">
          <img src="../img/hmif.png" alt="Logo" class="w-10 h-10">
          <div>
            <h1 class="font-bold text-xl">Admin Panel</h1>
          </div>
        </div>
        <button id="closeSidebar" class="text-white">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <nav class="mt-6">
        <a href="admin.php" class="sidebar-link flex items-center gap-3 py-3 px-6 text-sm">
          <i class="fas fa-chart-bar"></i>
          Dashboard
        </a>
        <a href="daftar_user.php" class="sidebar-link active flex items-center gap-3 py-3 px-6 text-sm">
          <i class="fas fa-users"></i>
          Daftar User
        </a>
      </nav>
      
      <div class="absolute bottom-0 w-full p-5 border-t border-indigo-700">
        <a href="logout.php" class="flex items-center gap-2 text-sm hover:text-indigo-200">
          <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <div class="flex-1 md:ml-64">
    <!-- Top bar -->
    <header class="bg-white shadow-sm sticky top-0 z-30">
      <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <div>
          <h1 class="text-xl font-bold text-gray-800"><i class="fas fa-users text-indigo-600 mr-2"></i>Daftar Pemilih</h1>
          <p class="text-xs text-gray-500">Manajemen data pemilih</p>
        </div>
        <div class="flex items-center gap-3">
          <div class="hidden md:block">
            <a href="admin_create_user.php" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2">
              <i class="fas fa-user-plus"></i> Tambah Pemilih
            </a>
          </div>
          <div class="relative group">
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50">
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user mr-2"></i> Profil</a>
              <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8 space-y-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="stats-card bg-white rounded-xl shadow p-6 flex items-center gap-4">
          <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
            <i class="fas fa-users text-xl"></i>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Total Pemilih</p>
            <h3 class="text-2xl font-bold"><?php echo $total_voters; ?></h3>
          </div>
        </div>
        
        <div class="stats-card bg-white rounded-xl shadow p-6 flex items-center gap-4">
          <div class="p-3 rounded-full bg-green-100 text-green-600">
            <i class="fas fa-check-circle text-xl"></i>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Sudah Memilih</p>
            <h3 class="text-2xl font-bold"><?php echo $voted_count; ?></h3>
          </div>
        </div>
        
        <div class="stats-card bg-white rounded-xl shadow p-6 flex items-center gap-4">
          <div class="p-3 rounded-full bg-red-100 text-red-600">
            <i class="fas fa-times-circle text-xl"></i>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Belum Memilih</p>
            <h3 class="text-2xl font-bold"><?php echo $not_voted; ?></h3>
          </div>
        </div>
      </div>
      
<!-- Voter List -->
      <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <h2 class="text-lg font-semibold"><i class="fas fa-list text-indigo-600 mr-2"></i>Daftar Pemilih</h2>
          <div class="flex gap-3">
            <div class="search-container">
              <i class="fas fa-search search-icon"></i>
              <input type="text" placeholder="Cari nama atau NIM..." class="search-input w-full md:w-64 border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="block md:hidden">
              <a href="admin_create_user.php" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2">
                <i class="fas fa-user-plus"></i>
              </a>
            </div>
          </div>
        </div>
        
        <div class="overflow-x-auto">
          <table class="voter-table">
            <thead>
              <tr>
                <th class="rounded-tl-lg">No</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Status Pemilihan</th>
                <th class="rounded-tr-lg">Aksi</th> <!-- Kolom baru -->
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($voters)) : ?>
                <?php $no = 1; while ($row = mysqli_fetch_assoc($voters)) : ?>
                  <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['nim']; ?></td>
                    <td class="font-medium"><?php echo $row['nama']; ?></td>
                    <td>
                      <span class="status-badge <?php echo $row['status_vote'] == 'Sudah Memilih' ? 'voted' : 'not-voted'; ?>">
                        <?php echo $row['status_vote']; ?>
                      </span>
                    </td>
                    <td>
                      <div class="flex gap-2 justify-center">
                        <!-- Tombol Edit -->
                        <a href="admin_edit_user.php?nim=<?php echo $row['nim']; ?>" class="edit-button">
                          <i class="fas fa-edit"></i>
                        </a>

                        
                        <!-- Tombol Delete (diubah) -->
                        <form action="delete_user.php" method="POST" class="delete-form">
                          <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                          <button type="button" onclick="confirmDelete(event, this.closest('form'))" class="delete-button">
                            <i class="fas fa-trash-alt"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else : ?>
                <tr>
                  <td colspan="6" class="text-center py-8 text-gray-500">
                    <i class="fas fa-users-slash text-4xl mb-3 text-gray-300"></i>
                    <p>Belum ada data pemilih</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <script>
    // Mobile sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      document.getElementById('mobileSidebar').classList.remove('hidden');
    });
    
    document.getElementById('closeSidebar').addEventListener('click', function() {
      document.getElementById('mobileSidebar').classList.add('hidden');
    });
    
    // Close sidebar when clicking outside
    document.getElementById('mobileSidebar').addEventListener('click', function(e) {
      if (e.target === this) {
        this.classList.add('hidden');
      }
    });
    
    // Search functionality
    const searchInput = document.querySelector('.search-input');
    const tableRows = document.querySelectorAll('.voter-table tbody tr');
    
    if (searchInput && tableRows.length > 0) {
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        tableRows.forEach(function(row) {
          const nim = row.cells[1].textContent.toLowerCase();
          const nama = row.cells[2].textContent.toLowerCase();
          
          if (nim.includes(searchTerm) || nama.includes(searchTerm)) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      });
    }

    // SweetAlert untuk konfirmasi delete
    function confirmDelete(event, form) {
      event.preventDefault();
      
      Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "User akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    }
  </script>

  <?php
  // Tampilkan notifikasi - DIPINDAHKAN KE BAWAH
  if (isset($_SESSION['success'])) {
    echo '<script>
      Swal.fire({
        icon: "success",
        title: "Sukses!",
        text: '.json_encode($_SESSION['success']).',
        showConfirmButton: true,
        timer: 3000
      });
    </script>';
    unset($_SESSION['success']);
  }

  if (isset($_SESSION['error'])) {
    echo '<script>
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: '.json_encode($_SESSION['error']).',
        showConfirmButton: true
      });
    </script>';
    unset($_SESSION['error']);
  }
  ?>
  </script>
</body>
</html>