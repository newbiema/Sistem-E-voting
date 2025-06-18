<?php
// vote.php
session_start();
include '../db.php';

if (!isset($_SESSION['nim'])) {
  header("Location: login.php");
  exit();
}

$nim = $_SESSION['nim'];

// Cek apakah user sudah voting
$cek = mysqli_query($conn, "SELECT * FROM votes WHERE nim = '$nim'");
if (mysqli_num_rows($cek) > 0) {
  // Sudah voting, kembalikan ke halaman utama
  header("Location: view.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $candidate_id = mysqli_real_escape_string($conn, $_POST['candidate_id']);

  // Simpan suara ke tabel votes
  $insert = mysqli_query($conn, "INSERT INTO votes (nim, candidate_id) VALUES ('$nim', '$candidate_id')");

  if ($insert) {
    // Redirect kembali ke halaman utama dengan status sukses
    header("Location: view.php");
    exit();
  } else {
    echo "<script>alert('Gagal menyimpan suara. Silakan coba lagi.'); window.location='index.php';</script>";
  }
} else {
  header("Location: index.php");
  exit();
}
?>
