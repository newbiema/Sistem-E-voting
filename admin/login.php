<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);

  // Cek admin
  $query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
  if (mysqli_num_rows($query) > 0) {
    $_SESSION['admin_login'] = true;
    header("Location: admin.php");
    exit();
  } else {
    $error = "Username atau password salah!";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient-to-br from-purple-100 to-blue-200 h-screen flex items-center justify-center">
  <div class="bg-white p-8 rounded-xl shadow-xl w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center text-purple-700"><i class="fas fa-user-shield"></i> Login Admin</h2>

    <?php if (isset($error)): ?>
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Login Gagal',
          text: '<?php echo $error; ?>',
        });
      </script>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Username</label>
        <input type="text" name="username" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-purple-200">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-purple-200">
      </div>
      <button type="submit" class="w-full bg-purple-600 text-white py-2 rounded-md hover:bg-purple-700 transition">
        <i class="fas fa-sign-in-alt"></i> Login
      </button>
    </form>
  </div>
</body>
</html>
