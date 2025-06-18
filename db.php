<?php

$host = "localhost";
$user = "root";
$pass = ""; // sesuaikan dengan password MySQL kamu
$db   = "evoting";

$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
  die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
