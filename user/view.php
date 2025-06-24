<?php
session_start();
include '../db.php';

if (!isset($_SESSION['nim'])) {
  header('Location: login.php');
  exit();
}
$nim = $_SESSION['nim'];

// Cek status voting user
$voteRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT candidate_id_ketua, candidate_id_wakil FROM votes WHERE nim='$nim' LIMIT 1"));
$sudahVote = $voteRow !== null;

// Ambil kandidat Ketua & Wakil
$ketua_rs = mysqli_query($conn, "SELECT * FROM candidates_ketua ORDER BY id");
$wakil_rs = mysqli_query($conn, "SELECT * FROM candidates_wakil ORDER BY id");
$ketua = [];
while ($row = mysqli_fetch_assoc($ketua_rs)) { $ketua[] = $row; }
$wakil = [];
while ($row = mysqli_fetch_assoc($wakil_rs)) { $wakil[] = $row; }

// Statistik partisipasi
$totalPemilih = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT nim) AS total FROM votes"))['total'];
$totalMahasiswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];
$persentasePemilih = $totalMahasiswa > 0 ? round($totalPemilih / $totalMahasiswa * 100, 1) : 0;

// Ambil jumlah suara untuk setiap kandidat
$suaraKetua = [];
$suaraWakil = [];

$resultKetua = mysqli_query($conn, "SELECT candidate_id_ketua, COUNT(*) as jumlah FROM votes GROUP BY candidate_id_ketua");
while ($row = mysqli_fetch_assoc($resultKetua)) {
    $suaraKetua[$row['candidate_id_ketua']] = $row['jumlah'];
}

$resultWakil = mysqli_query($conn, "SELECT candidate_id_wakil, COUNT(*) as jumlah FROM votes GROUP BY candidate_id_wakil");
while ($row = mysqli_fetch_assoc($resultWakil)) {
    $suaraWakil[$row['candidate_id_wakil']] = $row['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pemilihan Ketua & Wakil HMIF</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="shortcut icon" href="../img/hmif.png" type="image/x-icon">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f0f9ff, #e6f7ff);
      min-height: 100vh;
      position: relative;
    }
    
    .hero-section {
      background: linear-gradient(135deg, #1e3a8a, #3b82f6);
      color: #fff;
      border-radius: 0 0 40px 40px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      position: relative;
      overflow: hidden;
      padding: 2rem 0;
    }
    
    .hero-pattern {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%),
        radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);
    }
    
    .status-badge {
      padding: 8px 18px;
      border-radius: 25px;
      font-size: 15px;
      display: inline-block;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .candidate-card {
      transition: all 0.3s ease;
      border-radius: 18px;
      overflow: hidden;
      position: relative;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
      background: white;
      border: 2px solid transparent;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    
    .candidate-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    }
    
    .candidate-card input:checked + .card-overlay {
      border: 3px solid #3b82f6;
      background: rgba(59,130,246,0.05);
    }
    
    .card-overlay {
      position: absolute;
      inset: 0;
      border: 3px solid transparent;
      border-radius: 18px;
      pointer-events: none;
      transition: all 0.3s ease;
    }
    
    .vote-button {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      color: #fff;
      padding: 14px 36px;
      border-radius: 14px;
      font-weight: 600;
      font-size: 17px;
      transition: all 0.3s;
      box-shadow: 0 6px 20px rgba(59,130,246,0.4);
      border: none;
      position: relative;
      overflow: hidden;
    }
    
    .vote-button:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 25px rgba(59,130,246,0.5);
      background: linear-gradient(135deg, #2563eb, #1e40af);
    }
    
    .vote-button:active {
      transform: translateY(1px);
    }
    
    .result-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.06);
      padding: 28px;
    }
    
    .stat-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.06);
      padding: 22px;
      text-align: center;
      transition: all 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .chart-container {
      position: relative;
      height: 220px;
      width: 100%;
    }
    
    .progress-bar {
      height: 12px;
      border-radius: 10px;
      background: #e2e8f0;
      overflow: hidden;
      margin-top: 12px;
    }
    
    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #3b82f6, #60a5fa);
      border-radius: 10px;
    }
    
    .footer {
      background: linear-gradient(135deg, #1e3a8a, #1e40af);
      color: #fff;
      border-radius: 40px 40px 0 0;
      padding: 40px 0 20px;
      margin-top: 70px;
      position: relative;
    }
    
    .footer::before {
      content: '';
      position: absolute;
      top: -20px;
      left: 0;
      right: 0;
      height: 40px;
      background: url("data:image/svg+xml,%3Csvg width='100' height='20' viewBox='0 0 100 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0 Q 25 20, 50 0 Q 75 20, 100 0 L 100 20 L 0 20 Z' fill='%231e3a8a'/%3E%3C/svg%3E");
      background-size: 100px 40px;
    }
    
    .candidate-info {
      padding: 18px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }
    
    .candidate-tag {
      display: inline-flex;
      align-items: center;
      background: #e0f2fe;
      color: #0c4a6e;
      padding: 6px 14px;
      border-radius: 25px;
      font-size: 14px;
      margin-top: 10px;
      font-weight: 500;
    }
    
    .visi-section {
      background: #f0f9ff;
      border-radius: 14px;
      padding: 14px;
      margin-top: 12px;
      flex-grow: 1;
      border-left: 4px solid #3b82f6;
    }
    
    .search-container {
      max-width: 500px;
      margin: 0 auto 25px;
    }
    
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.7);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }
    
    .modal-content {
      background: white;
      border-radius: 24px;
      width: 90%;
      max-width: 600px;
      max-height: 90vh;
      overflow-y: auto;
      padding: 30px;
      position: relative;
      box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }
    
    @media (max-width: 768px) {
      .hero-section { 
        border-radius: 0 0 30px 30px; 
        padding: 1.5rem 0;
      }
      .candidate-grid { grid-template-columns: 1fr; }
      .chart-container { height: 200px; }
      .vote-button { padding: 12px 28px; font-size: 16px; }
      .stat-card { padding: 18px; }
      .result-card { padding: 22px; }
    }
  </style>
</head>
<body>
  <!-- Hero Section -->
  <div class="hero-section">
    <div class="hero-pattern"></div>
    <div class="max-w-6xl mx-auto px-4 md:px-8 relative z-10">
      <div class="flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-5">
          <div class="bg-white p-3 rounded-full shadow-xl">
            <img src="../img/hmif.png" alt="Logo HMIF" class="w-16 aspect-auto">
          </div>
          <div>
            <h1 class="text-2xl md:text-4xl font-bold text-white drop-shadow-md">PEMILIHAN KETUA & WAKIL HMIF</h1>
            <p class="text-blue-100 text-lg mt-1">Suara Anda Menentukan Masa Depan Organisasi</p>
          </div>
        </div>
        <div class="flex flex-col items-end mt-4 md:mt-0">
          <div class="status-badge <?php echo $sudahVote ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> transform transition duration-500 hover:scale-105">
            <i class="fas <?php echo $sudahVote ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
            <?php echo $sudahVote ? 'Sudah Memilih' : 'Belum Memilih'; ?>
          </div>
          <p class="text-sm text-blue-100 mt-3 font-medium bg-blue-900/30 px-3 py-1 rounded-full">NIM: <?php echo htmlspecialchars($nim); ?></p>
        </div>
      </div>
      
      <div class="mt-8 text-center">
        <div class="inline-block bg-blue-500/20 backdrop-blur-sm px-6 py-3 rounded-full border border-blue-300/30">
          <div class="flex items-center justify-center gap-3">
          <p class="text-sm flex items-center">
            <i class="fas fa-calendar-alt mr-2"></i>
            <span id="live-time" class="text-blue-100 font-medium"></span>
          </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="max-w-6xl mx-auto py-8 px-4 md:px-8">
    <!-- Statistik Partisipasi -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
      <div class="stat-card">
        <div class="text-4xl font-bold text-blue-600 mb-2"><?php echo $totalPemilih; ?></div>
        <p class="text-gray-600 text-lg">Pemilih</p>
        <div class="mt-4">
          <i class="fas fa-users text-blue-400 text-2xl"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="text-4xl font-bold text-blue-600 mb-2"><?php echo $totalMahasiswa; ?></div>
        <p class="text-gray-600 text-lg">Mahasiswa Terdaftar</p>
        <div class="mt-4">
          <i class="fas fa-user-graduate text-blue-400 text-2xl"></i>
        </div>
      </div>
      <div class="stat-card">
        <div class="text-4xl font-bold text-blue-600 mb-2"><?php echo $persentasePemilih; ?>%</div>
        <p class="text-gray-600 text-lg">Tingkat Partisipasi</p>
        <div class="progress-bar mt-4">
          <div class="progress-fill" style="width:<?php echo $persentasePemilih; ?>%"></div>
        </div>
      </div>
    </div>

    <?php if ($sudahVote): ?>
    <!-- SUDAH VOTE -->
    <section class="result-card">
      <div class="text-center mb-8">
        <div class="inline-block bg-green-100 text-green-800 px-6 py-3 rounded-full mb-4 transform transition hover:scale-105">
          <i class="fas fa-check-circle mr-2"></i>
          <span class="font-semibold">Terima kasih sudah berpartisipasi!</span>
        </div>
        <p class="text-gray-600 max-w-2xl mx-auto">Berikut rekap hasil pemilihan Ketua dan Wakil HMIF:</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
        <div class="space-y-6">
          <h3 class="text-2xl font-bold text-center flex items-center justify-center gap-3">
            <i class="fas fa-crown text-yellow-500"></i> Hasil Pemilihan Ketua
          </h3>
          <div class="chart-container">
            <canvas id="chartKetua"></canvas>
          </div>
        </div>
        <div class="space-y-6">
          <h3 class="text-2xl font-bold text-center flex items-center justify-center gap-3">
            <i class="fas fa-user-friends text-blue-500"></i> Hasil Pemilihan Wakil
          </h3>
          <div class="chart-container">
            <canvas id="chartWakil"></canvas>
          </div>
        </div>
      </div>
      
      <!-- Detail Hasil -->
      <div>
        <h3 class="text-2xl font-bold text-center mb-6 flex items-center justify-center gap-3">
          <i class="fas fa-chart-bar text-blue-500"></i> Detail Perolehan Suara
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-blue-50 rounded-2xl p-5">
            <h4 class="font-bold text-xl mb-4 text-center text-blue-800">Calon Ketua</h4>
            <div class="space-y-4">
              <?php foreach ($ketua as $row): 
                $suara = isset($suaraKetua[$row['id']]) ? $suaraKetua[$row['id']] : 0;
                $persentase = $totalPemilih > 0 ? round(($suara / $totalPemilih) * 100, 1) : 0;
              ?>
              <div class="flex items-center justify-between bg-white p-4 rounded-xl shadow-sm">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-blue-200">
                    <img src="../<?php echo htmlspecialchars($row['foto']); ?>" class="w-full h-full object-cover">
                  </div>
                  <div>
                    <div class="font-bold"><?php echo htmlspecialchars($row['nama']); ?></div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-bold text-blue-800"><?php echo $suara; ?> suara</div>
                  <div class="text-sm text-gray-600"><?php echo $persentase; ?>%</div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          
          <div class="bg-blue-50 rounded-2xl p-5">
            <h4 class="font-bold text-xl mb-4 text-center text-blue-800">Calon Wakil Ketua</h4>
            <div class="space-y-4">
              <?php foreach ($wakil as $row): 
                $suara = isset($suaraWakil[$row['id']]) ? $suaraWakil[$row['id']] : 0;
                $persentase = $totalPemilih > 0 ? round(($suara / $totalPemilih) * 100, 1) : 0;
              ?>
              <div class="flex items-center justify-between bg-white p-4 rounded-xl shadow-sm">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-blue-200">
                    <img src="../<?php echo htmlspecialchars($row['foto']); ?>" class="w-full h-full object-cover">
                  </div>
                  <div>
                    <div class="font-bold"><?php echo htmlspecialchars($row['nama']); ?></div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-bold text-blue-800"><?php echo $suara; ?> suara</div>
                  <div class="text-sm text-gray-600"><?php echo $persentase; ?>%</div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php else: ?>
    <!-- Pencarian Kandidat -->
    <div class="search-container mb-8">
      <div class="relative">
        <input type="text" id="searchInput" placeholder="Cari kandidat berdasarkan nama..." class="w-full py-4 px-5 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm">
        <i class="fas fa-search absolute right-5 top-4 text-gray-400 text-lg"></i>
      </div>
    </div>
    
    <!-- BELUM VOTE -->
    <form id="voteForm" action="vote.php" method="POST" class="space-y-10">
      <input type="hidden" name="action" value="vote" />
      
      <div class="space-y-6">
        <div class="flex items-center justify-center gap-4">
          <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-white px-7 py-2 rounded-full text-lg font-bold flex items-center gap-3 shadow-md">
            <i class="fas fa-crown text-white"></i>
            <span>Kandidat Ketua</span>
          </div>
          <span class="text-gray-600 text-sm bg-blue-50 px-3 py-1 rounded-full">
            <i class="fas fa-info-circle mr-1"></i> Semester 4 (Pilih satu)
          </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 candidate-grid">
          <?php foreach ($ketua as $row): ?>
          <label class="candidate-card">
            <input type="radio" name="ketua_id" value="<?php echo $row['id']; ?>" class="sr-only" required>
            <div class="card-overlay"></div>
            <div class="h-52 overflow-hidden">
              <img src="../<?php echo htmlspecialchars($row['foto']); ?>" class="w-full h-full object-cover">
            </div>
            <div class="candidate-info">
              <h3 class="font-bold text-xl flex items-center gap-3 text-blue-800">
                <i class="fas fa-user-circle text-blue-500"></i>
                <?php echo htmlspecialchars($row['nama']); ?>
              </h3>
              
              <div class="flex items-center gap-2 mt-3">
                <span class="candidate-tag">
                  <i class="fas fa-graduation-cap mr-2"></i> Teknik Informatika
                </span>
                <span class="candidate-tag">
                  <i class="fas fa-layer-group mr-2"></i> Semester 4
                </span>
              </div>
              
              <div class="visi-section mt-4">
                <div class="text-md font-bold text-blue-700 flex items-center gap-3">
                  <i class="fas fa-bullhorn text-blue-500"></i> Visi
                </div>
                <p class="text-gray-700 mt-3"><?php echo htmlspecialchars(mb_strimwidth($row['visi'], 0, 140, '...')); ?></p>
              </div>
              
              <button type="button" class="mt-4 text-blue-600 font-medium flex items-center gap-3 detail-btn" data-name="<?php echo htmlspecialchars($row['nama']); ?>" data-visi="<?php echo htmlspecialchars($row['visi']); ?>" data-foto="../<?php echo htmlspecialchars($row['foto']); ?>">
                <i class="fas fa-info-circle text-blue-500"></i> Lihat Detail Lengkap
              </button>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      
      <div class="space-y-6">
        <div class="flex items-center justify-center gap-4">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-7 py-2 rounded-full text-lg font-bold flex items-center gap-3 shadow-md">
            <i class="fas fa-user-friends text-white"></i>
            <span>Kandidat Wakil</span>
          </div>
          <span class="text-gray-600 text-sm bg-blue-50 px-3 py-1 rounded-full">
            <i class="fas fa-info-circle mr-1"></i> Semester 2 (Pilih satu)
          </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 candidate-grid">
          <?php foreach ($wakil as $row): ?>
          <label class="candidate-card">
            <input type="radio" name="wakil_id" value="<?php echo $row['id']; ?>" class="sr-only" required>
            <div class="card-overlay"></div>
            <div class="h-52 overflow-hidden">
              <img src="../<?php echo htmlspecialchars($row['foto']); ?>" class="w-full h-full object-cover">
            </div>
            <div class="candidate-info">
              <h3 class="font-bold text-xl flex items-center gap-3 text-blue-800">
                <i class="fas fa-user-circle text-blue-500"></i>
                <?php echo htmlspecialchars($row['nama']); ?>
              </h3>
              
              <div class="flex items-center gap-2 mt-3">
                <span class="candidate-tag">
                  <i class="fas fa-graduation-cap mr-2"></i> Teknik Informatika
                </span>
                <span class="candidate-tag">
                  <i class="fas fa-layer-group mr-2"></i> Semester 2
                </span>
              </div>
              
              <div class="visi-section mt-4">
                <div class="text-md font-bold text-blue-700 flex items-center gap-3">
                  <i class="fas fa-bullhorn text-blue-500"></i> Visi
                </div>
                <p class="text-gray-700 mt-3"><?php echo htmlspecialchars(mb_strimwidth($row['visi'], 0, 140, '...')); ?></p>
              </div>
              
              <button type="button" class="mt-4 text-blue-600 font-medium flex items-center gap-3 detail-btn" data-name="<?php echo htmlspecialchars($row['nama']); ?>" data-visi="<?php echo htmlspecialchars($row['visi']); ?>" data-foto="../<?php echo htmlspecialchars($row['foto']); ?>">
                <i class="fas fa-info-circle text-blue-500"></i> Lihat Detail Lengkap
              </button>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      
      <div class="text-center pt-6">
        <button type="submit" class="vote-button">
          <i class="fas fa-paper-plane mr-2"></i>Kirim Suara Saya
        </button>
        <p class="text-gray-600 text-sm mt-4 max-w-2xl mx-auto">
          <i class="fas fa-shield-alt text-blue-500 mr-2"></i> 
          Sistem ini menjamin kerahasiaan pilihan Anda. Pastikan Anda telah memilih dengan bijak.
        </p>
      </div>
    </form>
    <?php endif; ?>
  </div>

  <!-- Modal Detail Kandidat -->
  <div class="modal" id="candidateModal">
    <div class="modal-content">
      <button class="absolute top-5 right-5 text-gray-500 hover:text-gray-700 transition">
        <i class="fas fa-times text-2xl"></i>
      </button>
      
      <div class="text-center">
        <div class="w-36 h-36 rounded-full overflow-hidden mx-auto border-4 border-blue-200 shadow-md">
          <img id="modalFoto" class="w-full h-full object-cover">
        </div>
        <h2 id="modalNama" class="text-2xl font-bold mt-5 text-blue-900"></h2>
        <div class="flex justify-center gap-3 mt-3">
          <span class="candidate-tag">
            <i class="fas fa-graduation-cap mr-2"></i> <span id="modalJurusan"></span>
          </span>
          <span class="candidate-tag">
            <i class="fas fa-layer-group mr-2"></i> Semester <span id="modalSemester"></span>
          </span>
        </div>
      </div>
      
      <div class="mt-8">
        <h3 class="text-xl font-bold text-blue-800 flex items-center gap-3 mb-4">
          <i class="fas fa-bullhorn text-blue-500"></i> Visi & Misi
        </h3>
        <div id="modalVisi" class="bg-blue-50 rounded-xl p-5 text-gray-700 leading-relaxed border-l-4 border-blue-500"></div>
      </div>
    </div>
  </div>
<!-- Footer -->
<footer class="bg-blue-900 text-white py-10">
  <div class="max-w-6xl mx-auto px-4 md:px-8 grid grid-cols-1 md:grid-cols-3 gap-10">
    
    <!-- Tentang -->
    <div>
      <h3 class="text-xl font-bold mb-4">Tentang Pemilihan</h3>
      <p class="text-blue-100 text-sm leading-relaxed">
        Pemilihan ini bertujuan memilih pemimpin HMIF yang visioner dan berdedikasi untuk kemajuan organisasi dan seluruh anggotanya.
      </p>
    </div>

    <!-- Informasi -->
    <div>
      <h3 class="text-xl font-bold mb-4">Informasi</h3>
      <ul class="text-blue-100 space-y-3 text-sm">
      <li class="flex items-center gap-3">
        <i class="fas fa-calendar-alt text-blue-400"></i>
        <span id="tanggal-sekarang">Periode: Hari ini</span>
      </li>
      <li class="flex items-center gap-3">
        <i class="fas fa-clock text-blue-400"></i>
        <span id="jam-sekarang">Jam: --:--:-- WIB</span>
      </li>

        <li class="flex items-center gap-3">
          <i class="fas fa-user-shield text-blue-400"></i>
          <span>Sistem Terverifikasi dan Aman</span>
        </li>
      </ul>
    </div>

    <!-- Kontak -->
    <div>
      <h3 class="text-xl font-bold mb-4">Kontak</h3>
      <ul class="text-blue-100 space-y-3 text-sm">

        <li class="flex items-center gap-3">
          <i class="fab fa-instagram text-pink-400"></i>
          <a href="https://www.instagram.com/hmif_uwg/l" target="_blank" class="hover:underline">@hmif_uwg</a>
        </li>
        <li class="flex items-center gap-3">
          <i class="fab fa-whatsapp text-green-400"></i>
          <a href="https://wa.me/6281234567890" target="_blank" class="hover:underline">+62 878-9221-9615</a>
        </li>
      </ul>
    </div>
  </div>

  <!-- Copyright -->
  <div class="mt-10 border-t border-blue-800 pt-6 text-center text-sm text-blue-300">
    © 2025 Himpunan Mahasiswa Informatika. Hak Cipta Dilindungi.
  </div>
</footer>



  <script>
    document.addEventListener('DOMContentLoaded', function() {
      <?php if ($sudahVote): ?>
      // Load voting results for users who have voted
      fetch('vote_results.php').then(r => r.json()).then(d => {
        const ctxK = document.getElementById('chartKetua').getContext('2d');
        new Chart(ctxK, {
          type: 'doughnut',
          data: {
            labels: d.labels_ketua,
            datasets: [{
              data: d.data_ketua,
              backgroundColor: ['#3b82f6', '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
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
                    size: 12
                  },
                  padding: 15
                }
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const label = context.label || '';
                    const value = context.raw || 0;
                    const total = context.chart.getDatasetMeta(0).total;
                    const percentage = Math.round((value / total) * 100);
                    return `${label}: ${value} suara (${percentage}%)`;
                  }
                }
              }
            }
          }
        });
        
        const ctxW = document.getElementById('chartWakil').getContext('2d');
        new Chart(ctxW, {
          type: 'doughnut',
          data: {
            labels: d.labels_wakil,
            datasets: [{
              data: d.data_wakil,
              backgroundColor: ['#3b82f6', '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
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
                    size: 12
                  },
                  padding: 15
                }
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const label = context.label || '';
                    const value = context.raw || 0;
                    const total = context.chart.getDatasetMeta(0).total;
                    const percentage = Math.round((value / total) * 100);
                    return `${label}: ${value} suara (${percentage}%)`;
                  }
                }
              }
            }
          }
        });
      });
      <?php else: ?>
      // Fitur Pencarian
      const searchInput = document.getElementById('searchInput');
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('.candidate-card');
        
        cards.forEach(card => {
          const name = card.querySelector('h3').textContent.toLowerCase();
          if (name.includes(searchTerm)) {
            card.style.display = 'flex';
          } else {
            card.style.display = 'none';
          }
        });
      });
      
      // Modal Detail Kandidat
      const modal = document.getElementById('candidateModal');
      const modalFoto = document.getElementById('modalFoto');
      const modalNama = document.getElementById('modalNama');
      const modalVisi = document.getElementById('modalVisi');
      const modalJurusan = document.getElementById('modalJurusan');
      const modalSemester = document.getElementById('modalSemester');
      
      document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          modalFoto.src = this.dataset.foto;
          modalNama.textContent = this.dataset.name;
          modalVisi.innerHTML = this.dataset.visi.replace(/\n/g, '<br>');
          
          // Tentukan jurusan dan semester berdasarkan posisi
          if (this.closest('.space-y-6:first-child')) {
            modalJurusan.textContent = "Teknik Informatika";
            modalSemester.textContent = "4";
          } else {
            modalJurusan.textContent = "Teknik Informatika";
            modalSemester.textContent = "2";
          }
          
          modal.style.display = 'flex';
        });
      });
      
      document.querySelector('.modal-content button').addEventListener('click', function() {
        modal.style.display = 'none';
      });
      
      // Close modal when clicking outside
      window.addEventListener('click', function(event) {
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      });
      
      // Vote form interaction
      const voteForm = document.getElementById('voteForm');
      voteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const k = document.querySelector('input[name="ketua_id"]:checked');
        const w = document.querySelector('input[name="wakil_id"]:checked');
        
        if (!k || !w) {
          Swal.fire({
            icon: 'warning',
            title: 'Pilihan Belum Lengkap',
            text: 'Silakan pilih kandidat untuk Ketua dan Wakil terlebih dahulu',
            confirmButtonColor: '#3b82f6',
            customClass: {
              popup: 'text-sm'
            }
          });
          return;
        }
        
        const ketuaName = k.parentElement.querySelector('h3').textContent;
        const wakilName = w.parentElement.querySelector('h3').textContent;
        
        Swal.fire({
          title: 'Konfirmasi Pilihan Anda',
          html: `<div class="text-center p-4">
                  <div class="text-xl font-bold mb-4">Anda memilih:</div>
                  <div class="flex flex-col md:flex-row justify-center gap-8 mb-6">
                    <div class="text-center">
                      <div class="bg-gray-200 border-2 border-dashed rounded-full w-20 h-20 mx-auto mb-3"></div>
                      <p class="font-bold text-lg">${ketuaName}</p>
                      <p class="text-blue-600 font-medium">Ketua HMIF</p>
                      <div class="mt-2">
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                          <i class="fas fa-layer-group mr-1"></i> Semester 4
                        </span>
                      </div>
                    </div>
                    <div class="flex items-center justify-center text-3xl text-blue-500 my-4 md:my-0">+</div>
                    <div class="text-center">
                      <div class="bg-gray-200 border-2 border-dashed rounded-full w-20 h-20 mx-auto mb-3"></div>
                      <p class="font-bold text-lg">${wakilName}</p>
                      <p class="text-blue-600 font-medium">Wakil Ketua HMIF</p>
                      <div class="mt-2">
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                          <i class="fas fa-layer-group mr-1"></i> Semester 2
                        </span>
                      </div>
                    </div>
                  </div>
                  <p class="text-lg font-medium">Yakin ingin mengirimkan pilihan Anda?</p>
                </div>`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, Kirim Suara',
          confirmButtonColor: '#3b82f6',
          cancelButtonText: 'Periksa Kembali',
          reverseButtons: true,
          customClass: {
            popup: 'text-sm'
          }
        }).then((result) => {
          if (result.isConfirmed) {
            // Submit form
            voteForm.submit();
          }
        });
      });
      <?php endif; ?>
    });


    
    function updateTime() {
    const now = new Date();
    const options = {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
      hour: '2-digit', minute: '2-digit', second: '2-digit',
      hour12: false,
      timeZone: 'Asia/Jakarta'
    };
    const formatted = now.toLocaleString('id-ID', options);
    document.getElementById('live-time').textContent = formatted + ' WIB';
  }

  updateTime(); // update pertama kali
  setInterval(updateTime, 1000); // update setiap detik


    function updateTanggalDanJam() {
    const now = new Date();

    // Format tanggal: Senin, 23 Juni 2025
    const tanggalOptions = {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      timeZone: 'Asia/Jakarta'
    };

    // Format jam: 14:30:15
    const jamOptions = {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false,
      timeZone: 'Asia/Jakarta'
    };

    const tanggal = now.toLocaleDateString('id-ID', tanggalOptions);
    const jam = now.toLocaleTimeString('id-ID', jamOptions);

    document.getElementById('tanggal-sekarang').textContent = `Tanggal: ${tanggal}`;
    document.getElementById('jam-sekarang').textContent = `Jam: ${jam} WIB`;
  }

  updateTanggalDanJam();            // Panggil pertama kali
  setInterval(updateTanggalDanJam, 1000);  // Perbarui tiap detik
  </script>
</body>
</html>