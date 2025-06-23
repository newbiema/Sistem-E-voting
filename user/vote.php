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
  header("Location: view.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $ketua_id = isset($_POST['ketua_id']) ? intval($_POST['ketua_id']) : 0;
  $wakil_id = isset($_POST['wakil_id']) ? intval($_POST['wakil_id']) : 0;

  // Validasi ID harus dipilih
  if ($ketua_id === 0 || $wakil_id === 0) {
    echo "<script>alert('Harap pilih kandidat ketua dan wakil.'); window.location='view.php';</script>";
    exit();
  }

  // Simpan suara
  $query = "INSERT INTO votes (nim, candidate_id_ketua, candidate_id_wakil) 
            VALUES ('$nim', '$ketua_id', '$wakil_id')";
  $insert = mysqli_query($conn, $query);

  if ($insert) {
    header("Location: view.php");
    exit();
  } else {
    echo "<script>alert('Gagal menyimpan suara.'); window.location='view.php';</script>";
  }
} else {
  header("Location: view.php");
  exit();
}
?>
