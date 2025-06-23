<?php
// delete_user.php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
  header('Location: login.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
  $id = $_POST['id'];
  
  // Dapatkan NIM user yang akan dihapus
  $query_nim = "SELECT nim FROM users WHERE id = $id";
  $result = mysqli_query($conn, $query_nim);
  $user = mysqli_fetch_assoc($result);
  
  if ($user) {
    $nim = $user['nim'];
    
    // Hapus dari tabel votes
    $query_votes = "DELETE FROM votes WHERE nim = '$nim'";
    mysqli_query($conn, $query_votes);
    
    // Hapus dari tabel users
    $query_users = "DELETE FROM users WHERE id = $id";
    
    if (mysqli_query($conn, $query_users)) {
      $_SESSION['success'] = "User berhasil dihapus!";
    } else {
      $_SESSION['error'] = "Error: " . mysqli_error($conn);
    }
  } else {
    $_SESSION['error'] = "User tidak ditemukan!";
  }
}

header('Location: daftar_user.php');
exit();
?>