<?php
// admin.php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
  header('Location: login.php');
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

// Get total votes
$total_votes = mysqli_query($conn, "SELECT COUNT(*) as total FROM votes");
$total_votes = mysqli_fetch_assoc($total_votes)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - Pemilihan Ketua</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="shortcut icon" href="../img/hmif.png" type="image/x-icon">
  <link rel="stylesheet" href="../css/admin_dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex">
  <!-- Sidebar -->
  <div class="w-64 bg-gradient-to-b from-indigo-800 to-purple-900 text-white min-h-screen fixed hidden md:block">
    <div class="p-5 flex items-center gap-3 border-b border-indigo-700">
      <img src="../img/hmif.png" alt="Logo" class="w-10 h-10">
      <div>
        <h1 class="font-bold text-xl">Admin Panel</h1>
        <p class="text-xs text-indigo-300">Pemilihan Ketua HMIF</p>
      </div>
    </div>
    
    <nav class="mt-6">
      <a href="admin.php" class="sidebar-link active flex items-center gap-3 py-3 px-6 text-sm">
        <i class="fas fa-chart-bar"></i>
        Dashboard
      </a>
      <a href="daftar_user.php" class="sidebar-link flex items-center gap-3 py-3 px-6 text-sm">
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
        <a href="admin.php" class="sidebar-link active flex items-center gap-3 py-3 px-6 text-sm">
          <i class="fas fa-chart-bar"></i>
          Dashboard
        </a>
        <a href="#" class="sidebar-link flex items-center gap-3 py-3 px-6 text-sm">
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
          <h1 class="text-xl font-bold text-gray-800"><i class="fas fa-chart-bar text-indigo-600 mr-2"></i>Dashboard Admin</h1>
          <p class="text-xs text-gray-500">Manajemen kandidat dan hasil pemilihan</p>
        </div>
        <div class="flex items-center gap-3">
          <div class="hidden md:block">
            <a href="admin_create_kandidat.php" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2">
              <i class="fas fa-plus"></i> Tambah Kandidat
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

    <main class="max-w-7xl mx-auto px-4 py-8 space-y-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="stats-card bg-white rounded-xl shadow p-6 flex items-center gap-4">
          <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
            <i class="fas fa-users text-xl"></i>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Total Kandidat</p>
            <h3 class="text-2xl font-bold"><?php echo mysqli_num_rows($candidates); ?></h3>
          </div>
        </div>
        
        <div class="stats-card bg-white rounded-xl shadow p-6 flex items-center gap-4">
          <div class="p-3 rounded-full bg-green-100 text-green-600">
            <i class="fas fa-vote-yea text-xl"></i>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Total Suara</p>
            <h3 class="text-2xl font-bold"><?php echo $total_votes; ?></h3>
          </div>
        </div>
        
        <div class="stats-card bg-white rounded-xl shadow p-6 flex items-center gap-4">
          <div class="p-3 rounded-full bg-blue-100 text-blue-600">
            <i class="fas fa-user-check text-xl"></i>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Pengguna Aktif</p>
            <h3 class="text-2xl font-bold"><?php echo rand(120, 250); ?></h3>
          </div>
        </div>
      </div>
      
      <!-- Chart Section -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-lg font-semibold"><i class="fas fa-chart-pie text-indigo-600 mr-2"></i>Distribusi Suara</h2>
          <div class="flex gap-2">
            <button class="text-xs bg-indigo-100 text-indigo-700 px-3 py-1 rounded">Semua</button>
          </div>
        </div>
        <div class="h-64">
          <canvas id="votesChart"></canvas>
        </div>
      </div>
            
      <!-- Candidates Section -->
      <section>
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold flex items-center gap-2"><i class="fas fa-users text-indigo-600"></i> Daftar Kandidat</h2>
          <div class="md:hidden">
            <a href="create.php" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-3 py-2 rounded-lg shadow flex items-center gap-2">
              <i class="fas fa-plus"></i> 
            </a>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php 
          $colors = ['from-indigo-500 to-indigo-700', 'from-purple-500 to-purple-700', 'from-pink-500 to-pink-700', 'from-blue-500 to-blue-700'];
          $i = 0;
          mysqli_data_seek($candidates, 0); // Reset pointer
          while ($row = mysqli_fetch_assoc($candidates)): 
            $color = $colors[$i % count($colors)];
            $i++;
          ?>
            <div class="candidate-card bg-white rounded-xl shadow overflow-hidden transition-all duration-300">
              <div class="h-2 bg-gradient-to-r <?php echo $color; ?>"></div>
              <div class="p-6 flex flex-col items-center text-center gap-4">
                <div class="relative">
                  <img src="../<?php echo $row['foto']; ?>" alt="foto" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow">
                  <div class="absolute -bottom-2 -right-2 bg-indigo-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold text-sm shadow">
                    #<?php echo $i; ?>
                  </div>
                </div>
                <div>
                  <h3 class="font-bold text-lg"><?php echo $row['nama_ketua']; ?></h3>
                  <p class="text-sm text-gray-600 -mt-1">Ketua</p>
                </div>
                <div>
                  <h3 class="font-bold text-lg"><?php echo $row['nama_wakil']; ?></h3>
                  <p class="text-sm text-gray-600 -mt-1">Wakil</p>
                </div>
                <p class="text-gray-700 text-sm italic line-clamp-3">"<?php echo nl2br($row['visi']); ?>"</p>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                  <div class="bg-gradient-to-r <?php echo $color; ?> h-2.5 rounded-full" 
                       style="width: <?php echo $total_votes > 0 ? ($row['total_suara']/$total_votes)*100 : 0; ?>%"></div>
                </div>
                <div class="flex justify-between w-full text-xs">
                  <span><?php echo $row['total_suara']; ?> suara</span>
                  <span><?php echo $total_votes > 0 ? number_format(($row['total_suara']/$total_votes)*100, 1) : 0; ?>%</span>
                </div>
              </div>
              <div class="border-t flex">
                <a href="admin_edit.php?id=<?php echo $row['id']; ?>" class="w-1/2 py-3 text-center text-blue-600 hover:bg-blue-50 text-sm font-medium flex items-center justify-center gap-1">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <a href="?hapus=<?php echo $row['id']; ?>" 
                   onclick="return confirmDelete(event, '<?php echo $row['nama_ketua']; ?>')" 
                   class="w-1/2 py-3 text-center text-red-600 hover:bg-red-50 text-sm font-medium flex items-center justify-center gap-1">
                  <i class="fas fa-trash"></i> Hapus
                </a>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </section>
    </main>
  </div>

  <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Dihapus!',
        text: 'Kandidat berhasil dihapus.',
        timer: 3000,
        showConfirmButton: false
      });
    </script>
  <?php endif; ?>
  
  <script>
    // Mobile sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      document.getElementById('mobileSidebar').classList.remove('hidden');
    });
    
    document.getElementById('closeSidebar').addEventListener('click', function() {
      document.getElementById('mobileSidebar').classList.add('hidden');
    });
    
    // Confirm delete with SweetAlert
    function confirmDelete(event, candidateName) {
      event.preventDefault();
      const url = event.currentTarget.getAttribute('href');
      
      Swal.fire({
        title: 'Hapus Kandidat?',
        html: `Anda yakin ingin menghapus <b>${candidateName}</b>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = url;
        }
      });
    }
    
    // Chart initialization
    document.addEventListener('DOMContentLoaded', function() {
      const ctx = document.getElementById('votesChart').getContext('2d');
      
      // Get candidate data for chart
      const candidateNames = [];
      const votesData = [];
      const backgroundColors = [
        'rgba(99, 241, 156, 0.8)',
        'rgba(139, 92, 246, 0.8)',
        'rgba(236, 72, 153, 0.8)',
        'rgba(59, 130, 246, 0.8)'
      ];
      
      <?php 
      mysqli_data_seek($candidates, 0);
      while ($row = mysqli_fetch_assoc($candidates)): ?>
        candidateNames.push('<?php echo $row["nama_ketua"]; ?>');
        votesData.push(<?php echo $row["total_suara"]; ?>);
      <?php endwhile; ?>
      
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: candidateNames,
          datasets: [{
            label: 'Jumlah Suara',
            data: votesData,
            backgroundColor: backgroundColors,
            borderColor: backgroundColors.map(color => color.replace('0.8', '1')),
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            }
          },
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });
    });
  </script>
</body>
</html>