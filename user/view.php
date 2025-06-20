<?php
session_start();
include '../db.php';

if (!isset($_SESSION['nim'])) {
  header('Location: login.php');
  exit();
}

$nim = $_SESSION['nim'];

// Cek apakah user sudah voting
$sudahVote = mysqli_num_rows(mysqli_query($conn, "SELECT 1 FROM votes WHERE nim = '$nim'")) > 0;
// Ambil semua kandidat
$candidates = mysqli_query($conn, "SELECT * FROM candidates");

// Hitung total pemilih
$totalPemilih = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT nim) as total FROM votes"))['total'];
$totalMahasiswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$persentasePemilih = $totalMahasiswa > 0 ? round(($totalPemilih / $totalMahasiswa) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vote Kandidat – HMIF</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Perbaikan: Gunakan CDN yang valid untuk Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <style>
    :root {
      --primary: #4f46e5;
      --primary-dark: #4338ca;
      --accent: #7c3aed;
      --success: #10b981;
      --warning: #f59e0b;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9fafb;
    }
    
    .card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .hero {
      background: linear-gradient(135deg, var(--primary), var(--accent));
      color: white;
      border-radius: 12px;
      overflow: hidden;
      position: relative;
    }
    
    .hero::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(to right, rgba(0,0,0,0.2), rgba(0,0,0,0.05));
    }
    
    .badge {
      display: inline-block;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      padding: 5px 12px;
      font-size: 0.8rem;
      font-weight: 500;
    }
    
    .user-badge {
      display: inline-flex;
      align-items: center;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 8px;
      padding: 8px 16px;
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
      background: var(--primary);
      color: white;
      border: none;
      cursor: pointer;
    }
    
    .btn:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .btn:active {
      transform: translateY(1px);
    }
    
    .candidate-photo {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid white;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .progress-bar {
      height: 8px;
      background: #e5e7eb;
      border-radius: 4px;
      overflow: hidden;
    }
    
    .progress-fill {
      height: 100%;
      border-radius: 4px;
    }
    
    @media (max-width: 768px) {
      .hero-content {
        flex-direction: column;
        text-align: center;
      }
      
      .hero-image {
        margin-top: 20px;
        max-width: 200px;
      }
      
      .user-badges {
        justify-content: center;
      }
    }
    
    .fade-in {
      animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Perbaikan: Tambahan untuk chart container */
    .chart-container {
      position: relative;
      height: 250px;
      width: 100%;
    }
  </style>
</head>
<body class="min-h-screen py-6 px-4 bg-gray-50">
  <div class="max-w-6xl mx-auto">
    <!-- Header -->
    <header class="text-center mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">
        <span class="text-indigo-600">PEMILIHAN KETUA & WAKIL</span>
        <br class="md:hidden">HIMPUNAN INFORMATIKA
      </h1>
      <div class="w-24 h-1 bg-indigo-500 rounded-full mx-auto mb-4"></div>
      
      <div class="flex justify-center space-x-4 mb-6">
        <img src="../img/logo.png" alt="Logo Kampus" class="h-10" />
        <img src="../img/hmif.png" alt="Logo HMIF" class="h-10" />
      </div>
    </header>

    <!-- Hero Section -->
    <section class="hero card mb-8">
      <div class="relative z-10 p-6 md:p-8">
        <div class="flex flex-col md:flex-row items-center justify-between">
          <div class="md:w-2/3 mb-6 md:mb-0">
            <span class="badge mb-4">
              <i class="fas fa-vote-yea mr-2"></i>PEMILIHAN KETUA & WAKIL HMIF
            </span>
            <h2 class="text-2xl md:text-3xl font-bold mb-4">
              Ayo Tentukan <span class="text-yellow-300">Pemimpin</span> HMIF!
            </h2>
            <p class="text-indigo-100 mb-6 max-w-xl">
              Pilih pasangan calon terbaik untuk masa depan yang lebih gemilang.
            </p>
            
            <div class="user-badges flex flex-wrap gap-3">
              <div class="user-badge">
                <i class="fas fa-user-graduate mr-2"></i>
                <span>NIM: <?php echo htmlspecialchars($nim); ?></span>
              </div>
              <div class="user-badge <?php echo $sudahVote ? 'bg-emerald-500/20' : 'bg-amber-500/20'; ?>">
                <i class="fas <?php echo $sudahVote ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <span><?php echo $sudahVote ? 'Sudah Memilih' : 'Belum Memilih'; ?></span>
              </div>
            </div>
          </div>
          
          <div class="hero-image">
            <img src="../img/hmif.png" alt="Vote Illustration" 
                 class="w-full max-w-xs" />
          </div>
        </div>
      </div>
    </section>

    <!-- Info Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="card p-5 bg-indigo-50 border border-indigo-100">
        <div class="flex items-center">
          <div class="bg-indigo-100 p-3 rounded-full mr-4">
            <i class="fas fa-clock text-indigo-600 text-xl"></i>
          </div>
          <div>
            <h3 class="text-gray-700 font-medium">Tanggal Pemilihan</h3>
            <p class="text-gray-900 font-semibold"><?php echo date('d F Y'); ?></p>
          </div>
        </div>
      </div>
      
      <div class="card p-5 bg-indigo-50 border border-indigo-100">
        <div class="flex items-center">
          <div class="bg-indigo-100 p-3 rounded-full mr-4">
            <i class="fas fa-users text-indigo-600 text-xl"></i>
          </div>
          <div>
            <h3 class="text-gray-700 font-medium">Total Pemilih</h3>
            <p class="text-gray-900 font-semibold"><?php echo $totalPemilih; ?> dari <?php echo $totalMahasiswa; ?> Mahasiswa</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Konten Utama -->
    <?php if ($sudahVote): ?>
      <!-- Sudah Vote -->
      <div class="card p-6 mb-8 bg-emerald-50 border border-emerald-100 fade-in">
        <div class="flex flex-col items-center text-center">
          <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-4">
            <i class="fas fa-check-circle text-emerald-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold mb-2 text-emerald-800">Terima kasih atas partisipasinya!</h3>
          <p class="text-gray-700">Anda sudah memberikan suara pada pemilihan kali ini.</p>
        </div>
      </div>

<!-- Hasil Voting -->
<section class="card p-6 mb-8 fade-in">
  <div class="flex flex-col md:flex-row justify-between items-center mb-6">
    <div>
      <h2 class="text-xl font-bold text-gray-800">Hasil Voting Sementara</h2>
      <p class="text-gray-600 text-sm">Update terakhir: <span id="last-update"><?php echo date('d F Y, H:i'); ?></span></p>
    </div>
    <div class="mt-2 md:mt-0">
      <div class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg flex items-center">
        <i class="fas fa-chart-pie mr-2"></i>
        <span>Total Suara: <span class="font-bold"><?php echo $totalPemilih; ?></span></span>
      </div>
    </div>
  </div>
  
  <!-- Chart Container -->
  <div class="chart-container mb-8">
    <canvas id="resultsChart"></canvas>
  </div>
  
  <!-- Progress Bars -->
  <div class="space-y-6">
    <?php
    $candidates = mysqli_query($conn, "SELECT * FROM candidates");
    $colors = ['#4f46e5', '#7c3aed', '#2563eb', '#10b981', '#f59e0b'];
    $i = 0;
    
    while ($candidate = mysqli_fetch_assoc($candidates)):
      $candidateId = $candidate['id'];
      $voteCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM votes WHERE candidate_id = $candidateId"))['count'];
      $percentage = $totalPemilih > 0 ? round(($voteCount / $totalPemilih) * 100, 1) : 0;
    ?>
    <div>
      <div class="flex justify-between mb-2">
        <span class="font-bold text-gray-800"><?php echo $candidate['nama_ketua']; ?> & <?php echo $candidate['nama_wakil']; ?></span>
        <span class="font-bold"><?php echo $voteCount; ?> suara (<?php echo $percentage; ?>%)</span>
      </div>
      <div class="progress-bar">
        <div class="progress-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo $colors[$i % count($colors)]; ?>"></div>
      </div>
    </div>
    <?php $i++; endwhile; ?>
  </div>
  
  <!-- Statistik Pemilih -->
  <div class="mt-8 pt-6 border-t border-gray-100">
    <h3 class="font-bold text-gray-800 mb-4">Statistik Pemilih</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="card p-4">
        <div class="text-2xl font-bold text-indigo-600 mb-1"><?php echo $persentasePemilih; ?>%</div>
        <div class="text-gray-600">Tingkat Partisipasi</div>
      </div>
      <div class="card p-4">
        <div class="text-2xl font-bold text-purple-600 mb-1"><?php echo $totalMahasiswa - $totalPemilih; ?></div>
        <div class="text-gray-600">Belum Memilih</div>
      </div>
    </div>
  </div>
</section>
    <?php else: ?>
      <!-- Belum Vote - Daftar Kandidat -->
      <section class="mb-8">
        <h2 class="text-xl font-bold text-center text-gray-800 mb-6">PILIHAN KANDIDAT</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php 
          $colors = ['#4f46e5', '#7c3aed', '#2563eb'];
          $i = 0;
          
          while ($row = mysqli_fetch_assoc($candidates)): 
            $color = $colors[$i % count($colors)];
            $i++;
          ?>
          <form action="vote.php" method="POST" class="card">
            <input type="hidden" name="candidate_id" value="<?php echo $row['id']; ?>" />
            
            <!-- Header Kandidat -->
            <div class="p-5" style="background: <?php echo $color; ?>; color: white;">
              <div class="text-center mb-4">
                <div class="text-xs font-semibold bg-white/20 px-3 py-1 rounded-full inline-block">
                  PASLON <?php echo $i; ?>
                </div>
              </div>
              
              <div class="flex justify-center">
                <?php if(!empty($row['foto'])): ?>
                  <img src="../<?php echo $row['foto']; ?>" alt="Foto Kandidat" class="candidate-photo" />
                <?php else: ?>
                  <div class="candidate-photo bg-white flex items-center justify-center">
                    <i class="fas fa-user-group text-2xl" style="color: <?php echo $color; ?>"></i>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- Detail Kandidat -->
            <div class="p-5">
              <div class="text-center mb-4">
                <h3 class="text-lg font-bold text-gray-800 mb-1">
                  <?php echo $row['nama_ketua']; ?>
                </h3>
                <p class="text-gray-600 text-sm">Ketua</p>
                
                <div class="my-2">
                  <i class="fas fa-heart text-xs text-gray-400"></i>
                </div>
                
                <h3 class="text-lg font-bold text-gray-800 mb-1">
                  <?php echo $row['nama_wakil']; ?>
                </h3>
                <p class="text-gray-600 text-sm">Wakil</p>
              </div>
              
              <div class="mb-5">
                <div class="text-center text-sm text-gray-500 mb-2">
                  <i class="fas fa-quote-left mr-1"></i> Visi & Misi
                </div>
                <p class="text-gray-700 text-sm line-clamp-3 italic">
                  "<?php echo htmlspecialchars($row['visi']); ?>"
                </p>
              </div>
              
              <button type="submit" class="btn w-full" style="background: <?php echo $color; ?>">
                <i class="fas fa-check-circle mr-2"></i> Pilih Kandidat Ini
              </button>
            </div>
          </form>
          <?php endwhile; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="text-center py-8 text-gray-600 mt-8">
      <div class="mb-4">
        <p>© 2025 Himpunan Mahasiswa Informatika</p>
        <p class="text-sm mt-1">Pemilihan Ketua & Wakil HMIF Periode 2025-2026</p>
      </div>
      <div class="flex justify-center space-x-4">
        <a href="#" class="text-gray-500 hover:text-indigo-600">
          <i class="fab fa-instagram text-lg"></i>
        </a>
        <a href="#" class="text-gray-500 hover:text-indigo-600">
          <i class="fab fa-twitter text-lg"></i>
        </a>
        <a href="#" class="text-gray-500 hover:text-indigo-600">
          <i class="fab fa-facebook text-lg"></i>
        </a>
      </div>
    </footer>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
  <?php if ($sudahVote): ?>
  // Perbarui waktu dengan zona waktu klien
  function updateLocalTime() {
    const now = new Date();
    const options = { 
      day: 'numeric', 
      month: 'long', 
      year: 'numeric',
      hour: '2-digit', 
      minute: '2-digit',
      timeZoneName: 'short'
    };
    const formattedTime = now.toLocaleString('id-ID', options);
    document.getElementById('last-update').textContent = formattedTime;
  }
  
  // Perbarui waktu pertama kali
  updateLocalTime();
  
  // Perbarui waktu setiap 1 menit
  setInterval(updateLocalTime, 60000);

  // Elemen chart
  const ctx = document.getElementById('resultsChart').getContext('2d');
  
  // Inisialisasi chart
  let voteChart;
  
  // Fungsi untuk memperbarui chart
  function updateChart() {
    fetch('vote_results.php')
      .then(response => response.json())
      .then(data => {
        // Warna untuk chart
        const colors = ['#4f46e5', '#7c3aed', '#2563eb', '#10b981', '#f59e0b'];
        
        // Perbarui atau buat chart
        if (voteChart) {
          voteChart.data.labels = data.labels;
          voteChart.data.datasets[0].data = data.data;
          voteChart.update();
        } else {
          voteChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: data.labels,
              datasets: [{
                data: data.data,
                backgroundColor: colors,
                borderColor: 'white',
                borderWidth: 2
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  position: 'bottom',
                  labels: {
                    font: {
                      family: "'Poppins', sans-serif"
                    }
                  }
                },
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      const total = context.chart._metasets[0].total;
                      const value = context.raw;
                      const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                      return `${context.label}: ${value} suara (${percentage}%)`;
                    }
                  }
                }
              },
              animation: {
                animateRotate: true
              }
            }
          });
        }
      })
      .catch(error => {
        console.error('Error fetching vote results:', error);
      });
  }
  
  // Panggil pertama kali
  updateChart();
  
  // Perbarui setiap 10 detik
  setInterval(updateChart, 10000);
  
  <?php else: ?>
  // Konfirmasi pemilihan
  const voteForms = document.querySelectorAll('form');
  voteForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const candidateNames = this.querySelector('h3').textContent;
      
      Swal.fire({
        title: 'Konfirmasi Pilihan',
        html: `Apakah Anda yakin memilih <b>${candidateNames}</b>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Saya Yakin!',
        cancelButtonText: 'Batal',
        customClass: {
          popup: 'font-poppins'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          // Tampilkan animasi loading
          Swal.fire({
            title: 'Memproses suara...',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
          
          // Kirim form setelah konfirmasi
          setTimeout(() => {
            this.submit();
          }, 1000);
        }
      });
    });
  });
  <?php endif; ?>
});
  </script>
</body>
</html>