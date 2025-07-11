<?php
// login.php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nim = mysqli_real_escape_string($conn, $_POST['nim']);

  // Cek apakah NIM terdaftar
  $query = mysqli_query($conn, "SELECT * FROM users WHERE nim='$nim'");
  if (mysqli_num_rows($query) > 0) {
    $_SESSION['nim'] = $nim;
    header("Location: user/view.php");
    exit();
  } else {
    $error = "NIM tidak terdaftar.";
  }
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Pemilih | E-Voting HMIF</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="shortcut icon" href="img/hmif.png" type="image/x-icon">
  <link rel="stylesheet" href="css/login.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#4f46e5',
            primaryDark: '#4338ca',
            accent: '#7c3aed',
            success: '#10b981',
            warning: '#f59e0b',
          },
          animation: {
            float: 'float 8s ease-in-out infinite',
            fadeIn: 'fadeIn 0.8s ease-out',
          },
          keyframes: {
            float: {
              '0%, 100%': { transform: 'translate(0, 0)' },
              '25%': { transform: 'translate(10px, 15px)' },
              '50%': { transform: 'translate(-15px, 10px)' },
              '75%': { transform: 'translate(5px, -10px)' },
            },
            fadeIn: {
              'from': { opacity: 0, transform: 'translateY(20px)' },
              'to': { opacity: 1, transform: 'translateY(0)' },
            }
          }
        }
      }
    }
  </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-200 via-indigo-200 to-purple-200 flex items-center justify-center p-4 wave">
  <!-- Floating particles background -->
  <div id="particles"></div>
  
  <!-- Login card -->
  <div class="login-card w-full max-w-md bg-white/90 backdrop-blur-md rounded-2xl overflow-hidden border border-white/40 animate-fadeIn">
    <!-- Card header -->
    <div class="bg-gradient-to-r from-primary to-accent p-8 text-center relative">
      <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
        <i class="fas fa-lock text-4xl bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent"></i>
      </div>
      <h1 class="text-2xl font-bold text-white">Masuk ke Sistem</h1>
      <p class="mt-2 text-blue-100">E-Voting Himpunan Informatika</p>
      
      <div class="absolute bottom-0 left-0 right-0 h-4 bg-gradient-to-t from-primary/20 to-transparent"></div>
    </div>
    
    <!-- Card body -->
    <div class="p-8">
      <form method="POST" class="space-y-6">
        <div class="relative">
          <i class="fas fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-primary text-lg"></i>
          <input
            type="text"
            name="nim"
            placeholder="Masukkan NIM Anda"
            required
            autocomplete="off"
            id="nimInput"
            class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-300 focus:border-primary focus:ring-0 input-focus transition-all duration-300"
          >
        </div>

        <button
          type="submit"
          class="w-full flex items-center justify-center gap-3 bg-gradient-to-r from-primary to-accent hover:from-primaryDark hover:to-accent text-white font-semibold py-3.5 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl"
        >
          <i class="fas fa-sign-in-alt"></i> Masuk Sebagai Pemilih
        </button>
      </form>
      
      <!-- Separator -->
      <div class="flex items-center my-6">
        <div class="flex-grow border-t border-gray-300"></div>
        <span class="mx-4 text-gray-500 text-sm">atau</span>
        <div class="flex-grow border-t border-gray-300"></div>
      </div>
      
      <!-- Admin login button -->
      <a 
        href="admin/login.php" 
        class="admin-btn w-full flex items-center justify-center gap-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3.5 rounded-xl transition-all duration-300"
      >
        <i class="fas fa-user-cog text-primary transition-all duration-300"></i> 
        Masuk Sebagai Admin
      </a>
      
      <!-- Footer text -->
      <p class="text-center text-sm text-gray-600 mt-8">
        Gunakan NIM aktif untuk login. <br class="hidden sm:inline">
        Masalah login? <a href="whatsapp://send?text=Hallo Min, NIM saya belom kedaftar&phone=+6287892219615"class="text-primary font-medium hover:underline">Hubungi Admin</a>
      </p>
    </div>
  </div>
  
  <?php if (isset($error)): ?>
  <script>
    setTimeout(() => {
      Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: '<?php echo addslashes($error); ?>',
        confirmButtonColor: '#4f46e5',
        confirmButtonText: 'Coba Lagi',
        customClass: {
          popup: 'font-poppins'
        }
      }).then(() => {
        document.getElementById('nimInput').focus();
      });
    }, 500);
  </script>
  <?php endif; ?>

  <script>
    // Create floating particles
    function createParticles() {
      const particlesContainer = document.getElementById('particles');
      const particleCount = 20;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Random properties
        const size = Math.random() * 10 + 5;
        const posX = Math.random() * 100;
        const posY = Math.random() * 100;
        const opacity = Math.random() * 0.4 + 0.1;
        const delay = Math.random() * 5;
        const duration = Math.random() * 10 + 10;
        
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${posX}%`;
        particle.style.top = `${posY}%`;
        particle.style.opacity = opacity;
        particle.style.animationDuration = `${duration}s`;
        particle.style.animationDelay = `${delay}s`;
        particle.style.backgroundColor = `rgba(79, 70, 229, ${Math.random() * 0.3 + 0.1})`;
        
        particlesContainer.appendChild(particle);
      }
    }
    
    // Initialize particles and focus input
    document.addEventListener('DOMContentLoaded', () => {
      createParticles();
      
      // Focus on input after page load
      setTimeout(() => {
        document.getElementById('nimInput').focus();
      }, 300);
    });
  </script>
</body>
</html>