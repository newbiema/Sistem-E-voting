<?php
include '../db.php';

// Cek koneksi database
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$query = "
  SELECT c.id, c.nama_ketua, c.nama_wakil, COUNT(v.id) AS cnt
  FROM candidates c
  LEFT JOIN votes v ON v.candidate_id = c.id
  GROUP BY c.id 
  ORDER BY cnt DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
    exit;
}

$response = [
    'labels' => [],
    'data' => [],
    'colors' => ['#4f46e5', '#7c3aed', '#2563eb', '#10b981', '#f59e0b'] // Warna untuk chart
];

while ($row = mysqli_fetch_assoc($result)) {
    $response['labels'][] = $row['nama_ketua'] . ' & ' . $row['nama_wakil'];
    $response['data'][] = (int)$row['cnt'];
}

// Tutup koneksi
mysqli_close($conn);

header('Content-Type: application/json');
echo json_encode($response);