<?php
// vote_results.php – JSON rekap suara (Ketua & Wakil terpisah)
include '../db.php';
header('Content-Type: application/json');

if (!$conn) {
  http_response_code(500);
  echo json_encode(['error' => 'Database connection failed']);
  exit;
}

// ------------------------------------------------------------
// Rekap Ketua
// ------------------------------------------------------------
$qKetua = "SELECT ck.id, ck.nama, COUNT(v.id) AS cnt
           FROM candidates_ketua ck
           LEFT JOIN votes v ON v.candidate_id_ketua = ck.id
           GROUP BY ck.id ORDER BY ck.id";
$rsKetua = mysqli_query($conn, $qKetua);
if(!$rsKetua){ http_response_code(500); echo json_encode(['error'=>mysqli_error($conn)]); exit; }
$labels_ketua = $data_ketua = [];
while($row = mysqli_fetch_assoc($rsKetua)){
  $labels_ketua[] = $row['nama'];
  $data_ketua[]   = (int)$row['cnt'];
}

// ------------------------------------------------------------
// Rekap Wakil
// ------------------------------------------------------------
$qWakil = "SELECT cw.id, cw.nama, COUNT(v.id) AS cnt
           FROM candidates_wakil cw
           LEFT JOIN votes v ON v.candidate_id_wakil = cw.id
           GROUP BY cw.id ORDER BY cw.id";
$rsWakil = mysqli_query($conn, $qWakil);
if(!$rsWakil){ http_response_code(500); echo json_encode(['error'=>mysqli_error($conn)]); exit; }
$labels_wakil = $data_wakil = [];
while($row = mysqli_fetch_assoc($rsWakil)){
  $labels_wakil[] = $row['nama'];
  $data_wakil[]   = (int)$row['cnt'];
}

// ------------------------------------------------------------
// Response
// ------------------------------------------------------------
$response = [
  'labels_ketua' => $labels_ketua,
  'data_ketua'   => $data_ketua,
  'labels_wakil' => $labels_wakil,
  'data_wakil'   => $data_wakil
];

echo json_encode($response);
?>
