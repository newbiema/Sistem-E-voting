<?php
// admin.php - Dashboard Admin (Ketua & Wakil terpisah)
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
  header('Location: login.php');
  exit();
}

if (isset($_GET['hapus_ketua'])) {
  $id = intval($_GET['hapus_ketua']);
  mysqli_query($conn, "DELETE FROM candidates_ketua WHERE id = $id");
  header('Location: admin.php?success=deleted');
  exit();
}

if (isset($_GET['hapus_wakil'])) {
  $id = intval($_GET['hapus_wakil']);
  mysqli_query($conn, "DELETE FROM candidates_wakil WHERE id = $id");
  header('Location: admin.php?success=deleted');
  exit();
}


$ketua_q   = "SELECT ck.*, (SELECT COUNT(*) FROM votes WHERE candidate_id_ketua = ck.id) AS total_suara
              FROM candidates_ketua ck ORDER BY total_suara DESC";
$wakil_q   = "SELECT cw.*, (SELECT COUNT(*) FROM votes WHERE candidate_id_wakil = cw.id) AS total_suara
              FROM candidates_wakil cw ORDER BY total_suara DESC";

$ketua_rs  = mysqli_query($conn, $ketua_q);
$wakil_rs  = mysqli_query($conn, $wakil_q);

$ketua     = [];
while ($row = mysqli_fetch_assoc($ketua_rs)) { $ketua[] = $row; }
$wakil     = [];
while ($row = mysqli_fetch_assoc($wakil_rs)) { $wakil[] = $row; }

// Total votes
$total_votes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM votes"))['total'];

// Data for Chart.js (gabungkan ketua & wakil)
$chart_labels = [];
$chart_votes  = [];
foreach ($ketua as $k) { $chart_labels[] = $k['nama']; $chart_votes[] = $k['total_suara']; }
foreach ($wakil as $w) { $chart_labels[] = $w['nama']; $chart_votes[] = $w['total_suara']; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin – Pemilihan HMIF</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
    }
    
    :root {
      --primary: #4f46e5;
      --secondary: #7e22ce;
      --dark: #1e293b;
      --light: #f1f5f9;
    }
    
    .dark-mode {
      --primary: #6366f1;
      --secondary: #a855f7;
      --dark: #f1f5f9;
      --light: #1e293b;
    }
    
    body {
      background-color: var(--light);
      color: var(--dark);
      transition: background-color 0.3s, color 0.3s;
    }
    
    .card {
      transition: transform 0.3s, box-shadow 0.3s;
      background-color: rgba(255, 255, 255, 0.9);
    }
    
    .dark-mode .card {
      background-color: rgba(30, 41, 59, 0.9);
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .gradient-bg {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
    }
    
    .fade-in {
      animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .toggle-checkbox:checked {
      @apply: right-0 border-green-400;
      right: 0;
      border-color: #68d391;
    }
    
    .toggle-checkbox:checked + .toggle-label {
      @apply: bg-green-400;
      background-color: #68d391;
    }
    
    .search-input {
      transition: all 0.3s;
    }
    
    .search-input:focus {
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.3);
    }
  </style>
</head>
<body class="min-h-screen flex">
  <!-- Sidebar (Desktop) -->
  <aside class="w-64 gradient-bg text-white min-h-screen fixed hidden md:block">
    <div class="p-5 flex items-center gap-3 border-b border-indigo-700">
      <img src="../img/hmif.png" alt="HMIF" class="w-10 h-10"/>
      <div>
        <h1 class="font-bold text-xl">Admin Panel</h1>
        <p class="text-xs text-indigo-300">Pemilihan HMIF</p>
      </div>
    </div>
    <nav class="mt-6 space-y-1">
      <a href="admin.php" class="flex items-center gap-3 py-3 px-6 bg-white/20"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="daftar_user.php" class="flex items-center gap-3 py-3 px-6 hover:bg-white/10"><i class="fas fa-users"></i> Daftar Pemilih</a>
    </nav>
    <div class="absolute bottom-0 w-full p-5 border-t border-indigo-700">
      <a href="logout.php" class="flex items-center gap-2 text-sm hover:text-indigo-200"><i class="fas fa-sign-out-alt"></i> Keluar</a>
    </div>
  </aside>

  <!-- Mobile Sidebar Toggle -->
  <button id="toggleSidebar" class="md:hidden fixed top-4 left-4 bg-indigo-600 text-white p-3 rounded-full z-50">
    <i class="fas fa-bars"></i>
  </button>
  <div id="mobileSidebar" class="fixed inset-0 bg-gray-900/70 z-40 hidden">
    <aside class="w-64 gradient-bg min-h-full text-white p-5 space-y-6">
      <div class="flex justify-between items-center">
        <h2 class="font-bold text-lg flex items-center gap-2"><i class="fas fa-cog"></i> Admin Panel</h2>
        <button id="closeSidebar" class="text-2xl">×</button>
      </div>
      <nav class="space-y-1">
        <a href="admin.php" class="block py-3 px-4 bg-white/20 rounded"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
        <a href="daftar_user.php" class="block py-3 px-4 hover:bg-white/10 rounded"><i class="fas fa-users mr-2"></i> Daftar Pemilih</a>
      </nav>
      <a href="logout.php" class="block mt-auto py-2 px-4 hover:bg-white/10 rounded"><i class="fas fa-sign-out-alt mr-2"></i> Keluar</a>
    </aside>
  </div>

  <!-- Main -->
  <main class="flex-1 md:ml-64 p-4 md:p-6 space-y-8">
    <!-- Header -->
    <div class="flex justify-between items-center flex-wrap gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold">Dashboard Admin</h1>
        <p class="text-gray-600 dark:text-gray-300">Kelola kandidat dan pantau hasil pemilihan</p>
      </div>
      <div class="flex items-center gap-4">
        <!-- Dark Mode Toggle -->
        <div class="flex items-center gap-2">
          <i class="fas fa-sun text-yellow-500"></i>
          <div class="relative inline-block w-12 mr-2 align-middle select-none">
            <input type="checkbox" name="dark-mode" id="dark-mode-toggle" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
            <label for="dark-mode-toggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
          </div>
          <i class="fas fa-moon text-indigo-400"></i>
        </div>
        <!-- Search -->
        <div class="relative">
          <input type="text" id="searchInput" placeholder="Cari kandidat..." class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
      </div>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 fade-in">
      <div class="card rounded-xl p-5 shadow-lg border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Kandidat</p>
            <h2 class="text-3xl font-bold mt-1"><?php echo count($ketua) + count($wakil); ?></h2>
          </div>
          <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
            <i class="fas fa-users text-yellow-500 text-xl"></i>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-gray-200 flex items-center gap-2 text-sm">
          <span class="text-green-500"><i class="fas fa-arrow-up"></i> Ketua: <?php echo count($ketua); ?></span>
          <span class="text-blue-500"><i class="fas fa-arrow-up"></i> Wakil: <?php echo count($wakil); ?></span>
        </div>
      </div>
      
      <div class="card rounded-xl p-5 shadow-lg border-l-4 border-green-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Suara</p>
            <h2 class="text-3xl font-bold mt-1"><?php echo $total_votes; ?></h2>
          </div>
          <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
            <i class="fas fa-vote-yea text-green-500 text-xl"></i>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-gray-200">
          <div class="flex justify-between text-sm">
            <span>Pemilih Terdaftar</span>
            <span class="font-medium"><?php 
              $total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];
              echo $total_users;
            ?></span>
          </div>
        </div>
      </div>
      
      <div class="card rounded-xl p-5 shadow-lg border-l-4 border-indigo-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Tanggal</p>
            <h2 class="text-2xl font-bold mt-1"><?php echo date('d M Y'); ?></h2>
            <p class="text-sm text-gray-500 mt-1"><?php echo date('H:i'); ?> WIB</p>
          </div>
          <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
            <i class="fas fa-calendar-alt text-indigo-500 text-xl"></i>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-gray-200">
=
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg fade-in">
      <h2 class="text-xl font-bold mb-4">Statistik Cepat</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white/20 p-4 rounded-xl">
          <div class="text-2xl font-bold"><?php 
            $max_ketua = 0;
            if(count($ketua) > 0) $max_ketua = max(array_column($ketua, 'total_suara'));
            echo $max_ketua;
          ?></div>
          <div class="text-sm mt-1">Suara Tertinggi Ketua</div>
        </div>
        <div class="bg-white/20 p-4 rounded-xl">
          <div class="text-2xl font-bold"><?php 
            $max_wakil = 0;
            if(count($wakil) > 0) $max_wakil = max(array_column($wakil, 'total_suara'));
            echo $max_wakil;
          ?></div>
          <div class="text-sm mt-1">Suara Tertinggi Wakil</div>
        </div>
        <div class="bg-white/20 p-4 rounded-xl">
          <div class="text-2xl font-bold"><?php 
            $min_ketua = 0;
            if(count($ketua) > 0) $min_ketua = min(array_column($ketua, 'total_suara'));
            echo $min_ketua;
          ?></div>
          <div class="text-sm mt-1">Suara Terendah Ketua</div>
        </div>
        <div class="bg-white/20 p-4 rounded-xl">
          <div class="text-2xl font-bold"><?php 
            $min_wakil = 0;
            if(count($wakil) > 0) $min_wakil = min(array_column($wakil, 'total_suara'));
            echo $min_wakil;
          ?></div>
          <div class="text-sm mt-1">Suara Terendah Wakil</div>
        </div>
      </div>
    </div>

    <!-- Kandidat Ketua -->
    <section class="fade-in">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold flex items-center gap-2">
          <i class="fas fa-crown text-yellow-500"></i>
          Calon Ketua
        </h2>
        <div class="flex gap-3">
          <button id="sortKetua" class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-sm">
            <i class="fas fa-sort-amount-down mr-1"></i> Urutkan
          </button>
          <a href="admin_create_kandidat.php?type=ketua" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded text-sm flex items-center gap-1">
            <i class="fas fa-plus"></i> Tambah
          </a>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php $i=1; foreach ($ketua as $row): ?>
        <div class="card rounded-xl overflow-hidden">
          <div class="relative">
            <img src="../<?php echo $row['foto']; ?>" class="w-full h-48 object-cover" alt="foto"/>
            <div class="absolute top-3 right-3 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">#<?php echo $i; ?></div>
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3">
              <h3 class="font-bold text-lg text-white"><?php echo $row['nama']; ?></h3>
            </div>
          </div>
          <div class="p-4 space-y-3">
            <div>
              <p class="text-sm text-gray-600 line-clamp-2">"<?php echo $row['visi']; ?>"</p>
            </div>
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm">Suara: <span class="font-semibold"><?php echo $row['total_suara']; ?></span></p>
                <div class="w-32 h-2 bg-gray-200 rounded-full mt-1">
                  <div class="h-full bg-yellow-500 rounded-full" style="width: <?php echo $total_votes ? ($row['total_suara'] / $total_votes * 100) : 0; ?>%"></div>
                </div>
                <span class="text-xs text-gray-500"><?php echo $total_votes ? number_format($row['total_suara'] / $total_votes * 100, 1) : 0; ?>%</span>
              </div>
              <div class="flex gap-2">
                <a href="admin_edit_kandidat.php?id=<?php echo $row['id']; ?>&type=ketua" class="w-8 h-8 flex items-center justify-center bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-full" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <button onclick="confirmDelete('ketua', <?php echo $row['id']; ?>, '<?php echo $row['nama']; ?>')" class="w-8 h-8 flex items-center justify-center bg-red-100 hover:bg-red-200 text-red-600 rounded-full" title="Hapus">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <?php $i++; endforeach; ?>
      </div>
    </section>

    <!-- Kandidat Wakil -->
    <section class="fade-in">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold flex items-center gap-2">
          <i class="fas fa-user-friends text-blue-500"></i>
          Calon Wakil
        </h2>
        <div class="flex gap-3">
          <button id="sortWakil" class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-sm">
            <i class="fas fa-sort-amount-down mr-1"></i> Urutkan
          </button>
          <a href="admin_create_kandidat.php?type=wakil" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm flex items-center gap-1">
            <i class="fas fa-plus"></i> Tambah
          </a>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php $i=1; foreach ($wakil as $row): ?>
        <div class="card rounded-xl overflow-hidden">
          <div class="relative">
            <img src="../<?php echo $row['foto']; ?>" class="w-full h-48 object-cover" alt="foto"/>
            <div class="absolute top-3 right-3 bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded">#<?php echo $i; ?></div>
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3">
              <h3 class="font-bold text-lg text-white"><?php echo $row['nama']; ?></h3>
            </div>
          </div>
          <div class="p-4 space-y-3">
            <div>
              <p class="text-sm text-gray-600 line-clamp-2">"<?php echo $row['visi']; ?>"</p>
            </div>
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm">Suara: <span class="font-semibold"><?php echo $row['total_suara']; ?></span></p>
                <div class="w-32 h-2 bg-gray-200 rounded-full mt-1">
                  <div class="h-full bg-blue-500 rounded-full" style="width: <?php echo $total_votes ? ($row['total_suara'] / $total_votes * 100) : 0; ?>%"></div>
                </div>
                <span class="text-xs text-gray-500"><?php echo $total_votes ? number_format($row['total_suara'] / $total_votes * 100, 1) : 0; ?>%</span>
              </div>
              <div class="flex gap-2">
                <a href="admin_edit_kandidat.php?id=<?php echo $row['id']; ?>&type=wakil" class="w-8 h-8 flex items-center justify-center bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-full" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <button onclick="confirmDelete('wakil', <?php echo $row['id']; ?>, '<?php echo $row['nama']; ?>')" class="w-8 h-8 flex items-center justify-center bg-red-100 hover:bg-red-200 text-red-600 rounded-full" title="Hapus">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <?php $i++; endforeach; ?>
      </div>
    </section>

    <!-- Charts Section -->
    <section class="fade-in">
      <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
        <i class="fas fa-chart-bar text-purple-500"></i>
        Statistik Pemilihan
      </h2>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card rounded-xl p-6">
          <h3 class="font-semibold text-lg mb-4">Distribusi Suara Ketua</h3>
          <canvas id="ketuaChart" height="250"></canvas>
        </div>
        <div class="card rounded-xl p-6">
          <h3 class="font-semibold text-lg mb-4">Distribusi Suara Wakil</h3>
          <canvas id="wakilChart" height="250"></canvas>
        </div>
      </div>
      <div class="card rounded-xl p-6 mt-6">
        <h3 class="font-semibold text-lg mb-4">Rekap Suara Semua Kandidat</h3>
        <canvas id="votesChart" height="120"></canvas>
      </div>
    </section>
  </main>

  <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: 'Kandidat dihapus',
      timer: 2500,
      showConfirmButton: false
    });
  </script>
  <?php endif; ?>

  <script>
    // Mobile sidebar toggle
    const sidebarBtn = document.getElementById('toggleSidebar');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const closeSidebar = document.getElementById('closeSidebar');
    sidebarBtn.addEventListener('click',() => mobileSidebar.classList.remove('hidden'));
    closeSidebar.addEventListener('click',() => mobileSidebar.classList.add('hidden'));
    
    // Dark mode toggle
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    darkModeToggle.addEventListener('change', function() {
      document.documentElement.classList.toggle('dark-mode');
      // Save preference to localStorage
      localStorage.setItem('darkMode', this.checked);
    });
    
    // Check saved dark mode preference
    if (localStorage.getItem('darkMode') === 'true') {
      document.documentElement.classList.add('dark-mode');
      darkModeToggle.checked = true;
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      document.querySelectorAll('.card').forEach(card => {
        const name = card.querySelector('h3').textContent.toLowerCase();
        if (name.includes(searchTerm)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
    
    // Sort functionality
    let sortAscending = true;
    document.getElementById('sortKetua').addEventListener('click', function() {
      sortCards('ketua', sortAscending);
      sortAscending = !sortAscending;
      this.innerHTML = sortAscending ? 
        '<i class="fas fa-sort-amount-down mr-1"></i> Suara Tertinggi' : 
        '<i class="fas fa-sort-amount-up mr-1"></i> Suara Terendah';
    });
    
    document.getElementById('sortWakil').addEventListener('click', function() {
      sortCards('wakil', sortAscending);
      sortAscending = !sortAscending;
      this.innerHTML = sortAscending ? 
        '<i class="fas fa-sort-amount-down mr-1"></i> Suara Tertinggi' : 
        '<i class="fas fa-sort-amount-up mr-1"></i> Suara Terendah';
    });
    
    function sortCards(type, ascending) {
      const container = document.querySelector(`.grid:has(.card[data-type="${type}"])`);
      const cards = Array.from(container.querySelectorAll('.card'));
      
      cards.sort((a, b) => {
        const votesA = parseInt(a.querySelector('.font-semibold').textContent);
        const votesB = parseInt(b.querySelector('.font-semibold').textContent);
        return ascending ? votesB - votesA : votesA - votesB;
      });
      
      // Re-append cards in sorted order
      cards.forEach(card => container.appendChild(card));
    }
    
    // Confirm delete function
    function confirmDelete(type, id, name) {
      Swal.fire({
        title: `Hapus Kandidat?`,
        html: `Anda yakin ingin menghapus <b>${name}</b>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = `?hapus_${type}=${id}`;
        }
      });
    }
    
    // Charts initialization
    document.addEventListener('DOMContentLoaded', function() {
      // Combined chart
      const labels = <?php echo json_encode($chart_labels); ?>;
      const votes  = <?php echo json_encode($chart_votes); ?>;
      const ctx = document.getElementById('votesChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Jumlah Suara',
            data: votes,
            backgroundColor: [
              'rgba(255, 159, 64, 0.7)',
              'rgba(153, 102, 255, 0.7)',
              'rgba(75, 192, 192, 0.7)',
              'rgba(255, 99, 132, 0.7)',
              'rgba(54, 162, 235, 0.7)',
              'rgba(255, 206, 86, 0.7)',
              'rgba(155, 89, 182, 0.7)',
              'rgba(46, 204, 113, 0.7)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return `Suara: ${context.parsed.y}`;
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { precision: 0 }
            }
          }
        }
      });
      
      // Ketua chart
      const ketuaData = {
        labels: <?php echo json_encode(array_column($ketua, 'nama')); ?>,
        datasets: [{
          label: 'Suara Ketua',
          data: <?php echo json_encode(array_column($ketua, 'total_suara')); ?>,
          backgroundColor: 'rgba(245, 158, 11, 0.7)',
          borderColor: 'rgba(245, 158, 11, 1)',
          borderWidth: 1
        }]
      };
      
      new Chart(document.getElementById('ketuaChart'), {
        type: 'bar',
        data: ketuaData,
        options: {
          responsive: true,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { precision: 0 }
            }
          }
        }
      });
      
      // Wakil chart
      const wakilData = {
        labels: <?php echo json_encode(array_column($wakil, 'nama')); ?>,
        datasets: [{
          label: 'Suara Wakil',
          data: <?php echo json_encode(array_column($wakil, 'total_suara')); ?>,
          backgroundColor: 'rgba(59, 130, 246, 0.7)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 1
        }]
      };
      
      new Chart(document.getElementById('wakilChart'), {
        type: 'bar',
        data: wakilData,
        options: {
          responsive: true,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { precision: 0 }
            }
          }
        }
      });
    });
  </script>
</body>
</html>