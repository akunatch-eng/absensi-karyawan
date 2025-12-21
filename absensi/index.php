<?php
session_start();
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    // kalau sudah login, langsung lempar sesuai role
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: dashboard/admin.php");
            exit;
        case 'karyawan':
            header("Location: dashboard/karyawan.php");
            exit;
        case 'supervisor':
            header("Location: dashboard/supervisor.php");
            exit;
    }
}
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistem Informasi Manajemen Kerja</title>

  <!-- FONT -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- CSS UTAMA (INI YANG TADI KAMU KIRIM) -->
  <link rel="stylesheet" href="assets/css/style.css" />

  <!-- ICON -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</head>
<body>

<div class="container">

  <!-- KIRI -->
  <div class="left">
    <div class="brand-icon">
      <ion-icon name="shield-checkmark-outline"></ion-icon>
    </div>

    <h1>Sistem Manajemen Kerja</h1>
    <p class="lead">
      Partner produktivitas Anda untuk mengelola absensi, tugas, dan laporan secara mudah dan terpusat.
    </p>

    <ul class="features">
      <li>
        <span class="feature-icon"><ion-icon name="lock-closed-outline"></ion-icon></span>
        <div>
          <strong>Secure Access</strong>
          <div class="muted">Protected login</div>
        </div>
      </li>
      <li>
        <span class="feature-icon"><ion-icon name="cloud-download-outline"></ion-icon></span>
        <div>
          <strong>Rekap Cepat</strong>
          <div class="muted">Laporan kehadiran otomatis</div>
        </div>
      </li>
    </ul>
  </div>

  <!-- KANAN -->
  <div class="right">
    <div class="card">
      <h2>Masuk</h2>
      <p class="subtitle">Gunakan akun yang sudah terdaftar di sistem</p>

      <?php if ($error): ?>
        <div style="color:#b33;text-align:center;margin-bottom:12px;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- FORM LOGIN ASLI -->
      <form action="backend/login.php" method="POST">

        <div class="input-box">
          <span class="icon"><ion-icon name="mail-outline"></ion-icon></span>
          <input type="email" name="email" required>
          <label>Email</label>
        </div>

        <div class="input-box">
          <span class="icon"><ion-icon name="lock-closed-outline"></ion-icon></span>
          <input type="password" name="password" required>
          <label>Password</label>
        </div>

        <div class="remember-forgot">
          <label><input type="checkbox" name="remember"> Ingat saya</label>
        </div>

        <button type="submit" class="btn">Masuk</button>

        <div class="divider no-lines">
          <span>Sistem Absensi Terintegrasi</span>
        </div>

        <p class="bottom">
          Belum punya akun? <a href="backend/register.php">Hubungi admin</a>
        </p>
      </form>
    </div>
  </div>

</div>

</body>
</html>
