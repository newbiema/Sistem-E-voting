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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vote Kandidat – HMIF</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"></script>
</head>
<body class="bg-gradient-to-br from-indigo-100 via-purple-100 to-blue-100 min-h-screen py-10 px-4 font-sans">
  <div class="max-w-6xl mx-auto">
    <!-- Logo -->
    <div class="flex justify-center items-center gap-6 mb-4">
      <img src="../img/logo_kampus.png" alt="Logo Kampus" class="w-16 h-16 object-contain" />
      <img src="../img/logo_hmif.png"   alt="Logo HMIF"   class="w-16 h-16 object-contain" />
    </div>

    <!-- Judul -->
    <header class="text-center mb-10">
      <h1 class="text-4xl font-extrabold bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 bg-clip-text text-transparent drop-shadow-sm">
        Pemilihan Ketua & Wakil Himpunan Informatika
      </h1>
      <p class="text-gray-700 mt-2 text-sm">
        Selamat datang, <span class="font-semibold"><?php echo htmlspecialchars($nim); ?></span>. Pilih pasangan terbaik menurutmu!
      </p>
    </header>

    <?php if ($sudahVote): ?>
      <!-- Pesan terima kasih -->
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-center shadow-md mb-8">
        <strong class="font-bold">Terima kasih!</strong> <span class="block sm:inline">Kamu sudah memberikan suara.</span>
      </div>

      <!-- Chart hasil voting -->
      <section class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold text-center text-indigo-700 mb-4">Hasil Voting Sementara</h2>
        <canvas id="resultsChart" height="200"></canvas>
      </section>
    <?php else: ?>
      <!-- Form pilih kandidat -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php while ($row = mysqli_fetch_assoc($candidates)): ?>
          <form action="vote.php" method="POST" onsubmit="return confirm('Yakin pilih pasangan ini?')" class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-1 p-6 flex flex-col items-center text-center border-2 border-indigo-100">
            <input type="hidden" name="candidate_id" value="<?php echo $row['id']; ?>" />

            <img src="../<?php echo $row['foto']; ?>" alt="Foto Kandidat" class="w-32 h-32 rounded-full object-cover border-4 border-indigo-500 shadow-md mb-4 hover:scale-105 transition duration-300" />

            <h3 class="text-lg font-bold text-gray-800">
              <?php echo $row['nama_ketua']; ?> &amp; <?php echo $row['nama_wakil']; ?>
            </h3>
            <p class="text-sm text-gray-600 italic mt-2 mb-4 line-clamp-4">"<?php echo nl2br($row['visi']); ?>"</p>

            <button type="submit" class="mt-auto bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow-md">
              <i class="fas fa-vote-yea"></i> Pilih Kandidat Ini
            </button>
          </form>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </div>

<!-- Chart.js & AJAX (hanya jika sudah vote) -->
<?php if ($sudahVote): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const ctx = document.getElementById('resultsChart').getContext('2d');

      const chart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: [],
          datasets: [{
            label: 'Suara Masuk',
            data: [],
            backgroundColor: [
              'rgba(99, 102, 241, 0.7)',   // indigo
              'rgba(139, 92, 246, 0.7)',   // purple
              'rgba(79, 70, 229, 0.7)',    // blue
              'rgba(16, 185, 129, 0.7)',   // emerald
              'rgba(239, 68, 68, 0.7)'     // red
            ],
            borderColor: '#fff',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                font: {
                  size: 14
                }
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const total = context.chart._metasets[0].total;
                  const value = context.raw;
                  const percentage = ((value / total) * 100).toFixed(1);
                  return `${context.label}: ${value} suara (${percentage}%)`;
                }
              }
            }
          }
        }
      });

      function loadResults() {
        fetch('vote_results.php')
          .then(res => res.json())
          .then(json => {
            chart.data.labels = json.labels;
            chart.data.datasets[0].data = json.data;
            chart.update();
          });
      }

      loadResults();
      setInterval(loadResults, 5000);
    });
  </script>
<?php endif; ?>

</body>
</html>
