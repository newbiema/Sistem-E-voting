<?php
include '../db.php';
$data = mysqli_query($conn, "
  SELECT c.nama_ketua, c.nama_wakil, COUNT(v.id) AS cnt
  FROM candidates c
  LEFT JOIN votes v ON v.candidate_id = c.id
  GROUP BY c.id ORDER BY cnt DESC
");
$response = ['labels'=>[], 'data'=>[]];
while ($r = mysqli_fetch_assoc($data)) {
  $response['labels'][] = $r['nama_ketua'].' & '.$r['nama_wakil'];
  $response['data'][] = (int)$r['cnt'];
}
header('Content-Type: application/json');
echo json_encode($response);
