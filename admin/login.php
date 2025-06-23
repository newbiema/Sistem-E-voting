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
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin | E-Voting HMIF</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="shortcut icon" href="../img/hmif.png" type="image/x-icon">
  <link rel="stylesheet" href="../css/login_admin.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#7e22ce',
            primaryDark: '#6b21a8',
            accent: '#a855f7',
            dark: '#1e1b4b'
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
<body class="min-h-screen bg-gradient-to-br from-purple-50 to-blue-100 flex items-center justify-center p-4 wave">
  <!-- Floating particles background -->
  <div id="particles"></div>
  
  <!-- Login card -->
  <div class="login-card w-full max-w-md bg-white/90 backdrop-blur-md rounded-2xl overflow-hidden border border-white/40 animate-fadeIn relative">
    <!-- Security badge -->
    <div class="security-badge">
      <i class="fas fa-lock mr-1"></i> Secure Admin Portal
    </div>
    
    <!-- Card header -->
    <div class="bg-gradient-to-r from-primary to-accent p-8 text-center relative">
      <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
        <i class="fas fa-user-shield text-4xl admin-icon"></i>
      </div>
      <h1 class="text-2xl font-bold text-white">Admin Dashboard</h1>
      <p class="mt-2 text-purple-100">E-Voting Himpunan Informatika</p>
      
      <div class="absolute bottom-0 left-0 right-0 h-4 bg-gradient-to-t from-primary/20 to-transparent"></div>
    </div>
    
    <!-- Card body -->
    <div class="p-8">
      <form method="POST" class="space-y-6">
        <div class="relative">
          <i class="fas fa-user-cog absolute left-4 top-1/2 -translate-y-1/2 text-primary text-lg"></i>
          <input
            type="text"
            name="username"
            placeholder="Username admin"
            required
            autocomplete="off"
            id="usernameInput"
            class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-300 focus:border-primary focus:ring-0 input-focus transition-all duration-300"
          >
        </div>
        
        <div class="relative">
          <i class="fas fa-key absolute left-4 top-1/2 -translate-y-1/2 text-primary text-lg"></i>
          <input
            type="password"
            name="password"
            placeholder="Password"
            required
            id="passwordInput"
            class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-300 focus:border-primary focus:ring-0 input-focus transition-all duration-300"
          >
          <button type="button" id="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">
            <i class="fas fa-eye"></i>
          </button>
        </div>

        <button
          type="submit"
          class="w-full flex items-center justify-center gap-3 bg-gradient-to-r from-primary to-accent hover:from-primaryDark hover:to-accent text-white font-semibold py-3.5 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl"
        >
          <i class="fas fa-sign-in-alt"></i> Masuk Ke Dashboard
        </button>
      </form>
      
      <!-- Footer text -->
      <p class="text-center text-sm text-gray-600 mt-8">
        <a href="../login.php" class="text-primary font-medium hover:underline">
          <i class="fas fa-arrow-left mr-1"></i> Kembali ke halaman pemilih
        </a>
      </p>
    </div>
  </div>
  
  <?php if (isset($error)): ?>
  <script>
    setTimeout(() => {
      Swal.fire({
        icon: 'error',
        title: 'Akses Ditolak',
        text: '<?php echo addslashes($error); ?>',
        confirmButtonColor: '#7e22ce',
        confirmButtonText: 'Coba Lagi',
        customClass: {
          popup: 'font-poppins'
        }
      }).then(() => {
        document.getElementById('usernameInput').focus();
      });
    }, 500);
  </script>
  <?php endif; ?>

  <script>
    // Create floating particles
    function createParticles() {
      const particlesContainer = document.getElementById('particles');
      const particleCount = 15;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Random properties
        const size = Math.random() * 10 + 5;
        const posX = Math.random() * 100;
        const posY = Math.random() * 100;
        const opacity = Math.random() * 0.3 + 0.1;
        const delay = Math.random() * 5;
        const duration = Math.random() * 10 + 10;
        
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${posX}%`;
        particle.style.top = `${posY}%`;
        particle.style.opacity = opacity;
        particle.style.animationDuration = `${duration}s`;
        particle.style.animationDelay = `${delay}s`;
        particle.style.backgroundColor = `rgba(126, 34, 206, ${Math.random() * 0.3 + 0.1})`;
        
        particlesContainer.appendChild(particle);
      }
    }
    
    // Toggle password visibility
    function setupPasswordToggle() {
      const toggleBtn = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('passwordInput');
      
      toggleBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle eye icon
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
      });
    }
    
    // Initialize effects
    document.addEventListener('DOMContentLoaded', () => {
      createParticles();
      setupPasswordToggle();
      
      // Focus on username input after page load
      setTimeout(() => {
        document.getElementById('usernameInput').focus();
      }, 300);
    });
  </script>
</body>
</html>