<?php
include '../db.php';

// Ambil hasil voting terbaru
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

// Statistik partisipasi
$totalPemilih = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT nim) AS total FROM votes"))['total'];
$totalMahasiswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];

header('Content-Type: application/json');
echo json_encode([
    'suaraKetua' => $suaraKetua,
    'suaraWakil' => $suaraWakil,
    'totalPemilih' => $totalPemilih,
    'totalMahasiswa' => $totalMahasiswa
]);
?>