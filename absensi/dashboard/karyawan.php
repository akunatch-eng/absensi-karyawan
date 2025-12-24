<?php
session_start();
$allowed_roles = ['karyawan'];
require "../backend/auth_guard.php";
require "../backend/db.php";

// Cek page & set default
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

if(!isset($_SESSION['id_karyawan'])){
    header("Location: ../index.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');


$id_karyawan = (int)$_SESSION['id_karyawan'];
$nama = $_SESSION['nama'];
$hari_ini = date('Y-m-d');

/* ======================
   DATA ABSENSI HARI INI
   ====================== */
$qToday = mysqli_query($conn, "
    SELECT jam_masuk, jam_keluar
    FROM absensi
    WHERE id_karyawan = $id_karyawan
      AND tanggal = '$hari_ini'
    LIMIT 1
");
$today = mysqli_fetch_assoc($qToday);

$status_hari_ini = "Belum Absen";
$jam_masuk_terakhir = "—";

if ($today) {
    $jam_masuk_terakhir = $today['jam_masuk'];
    if ($today['jam_keluar']) {
        $status_hari_ini = "Selesai";
    } else {
        $status_hari_ini = "Sedang Bekerja";
    }
}

/* ======================
   TOTAL HADIR BULAN INI
   ====================== */
$bulan = date('m');
$tahun = date('Y');

$qTotal = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM absensi
    WHERE id_karyawan = $id_karyawan
      AND MONTH(tanggal) = '$bulan'
      AND YEAR(tanggal) = '$tahun'
");
$total_hadir = mysqli_fetch_assoc($qTotal)['total'];

/* ======================
   DATA PROFIL
   ====================== */
$qProfil = mysqli_query($conn, "
    SELECT nip, nama_karyawan, jenis_kelamin, alamat, no_hp, tgl_lahir,foto
    FROM karyawan
    WHERE id_karyawan = $id_karyawan
");
$profil = mysqli_fetch_assoc($qProfil);
/*==========================
   pengajuan izin
    ==========================*/
$cekIzinHariIni = mysqli_query($conn, "
    SELECT * FROM izin
    WHERE id_karyawan = $id_karyawan 
      AND DATE(dibuat_pada) = '$hari_ini'
    LIMIT 1
");


$izinHariIni = mysqli_fetch_assoc($cekIzinHariIni);
/* ======================
   RIWAYAT IZIN
   ====================== */
// Ambil riwayat izin terakhir 10 data
$riwayat = mysqli_query($conn, "
    SELECT tanggal, jenis, keterangan, status, bukti, dibuat_pada 
    FROM izin 
    WHERE id_karyawan = $id_karyawan
      AND tanggal = '$hari_ini'
    LIMIT 1
");

// Ambil riwayat izin terakhir 10 data
$riwayat = mysqli_query($conn, "
    SELECT tanggal, jenis, keterangan, status, bukti, dibuat_pada 
    FROM izin 
    WHERE id_karyawan = $id_karyawan
    ORDER BY dibuat_pada DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Karyawan</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- FONT & ICON -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>


<!-- CSS UTAMA -->
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Poppins',sans-serif; background:#f4f7f5; color:#41524a; line-height:1.5; }
.dashboard { max-width:1200px; margin:30px auto; padding:0 24px; }
.topbar {
    display: flex;
    align-items: center;
    justify-content: flex-end; /* dorong semua ke kanan */
    gap: 18px;                  /* jarak antar elemen */
    margin-bottom: 30px;
}

.topbar h2 {
    font-size: 28px;
    font-weight: 700;
    margin-right: auto; /* dorong heading ke kiri */
}
.nav {
    display: flex;
    gap: 18px;
}
.nav a { margin-left:10px; text-decoration:none; font-weight:500; color:#41524a; transition:0.3s; }
.nav a.active { color:#1aa34a; font-weight:600; }
.nav a:hover { color:#14903d; }
.logout { color:#41524a; font-weight:600; text-decoration:none; }
.logout:hover { color:#b33; }

.card-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:30px; }
.stat-card { background:#fff; padding:20px; border-radius:14px; box-shadow:0 10px 25px rgba(0,0,0,.06); transition:0.3s; }
.stat-card:hover { transform:translateY(-3px); }
.stat-card h4 { font-size:14px; color:#6e7b73; margin-bottom:8px; }
.stat-card .value { font-size:22px; font-weight:700; color:#1aa34a; }

.action-card { background:#fff; border-radius:18px; padding:24px; box-shadow:0 15px 35px rgba(0,0,0,.08); margin-bottom:24px; transition:0.3s; }
.action-card:hover { transform:translateY(-2px); }
.action-card h3 { margin-bottom:16px; font-size:20px; display:flex; align-items:center; gap:8px; }
.action-buttons { display:flex; gap:14px; flex-wrap:wrap; margin-top:16px; }
.btn { padding:10px 18px; border:none; border-radius:8px; background:#1aa34a; color:#fff; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; transition:0.3s; }
.btn:hover { opacity:0.9; }
.btn.danger { background:linear-gradient(90deg,#e5533d,#c0392b); box-shadow:0 8px 18px rgba(192,57,43,.25); }

.profile-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px; }
.profile-item { background:#f9f9f9; padding:14px 16px; border-radius:12px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 6px 12px rgba(0,0,0,.05); transition:0.3s; }
.profile-item ion-icon { margin-right:8px; font-size:20px; color:#1aa34a; }
.profile-item:hover { background:#eaf7f1; }
.tooltip-container {
  position: relative;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  border-radius: 12px;
  background: #f9f9f9;
  transition: 0.3s;
}
.tooltip-container:hover { background: #eaf7f1; }

.tooltip-content {
  visibility: hidden;
  opacity: 1;
  position: absolute;
  top: 50%;        /* muncul di bawah elemen */
  left: 100%;        /* tengah horizontal */
  transform: translateX(-50%); /
  background: #fff;
  color: #333;
  padding: 10px 14px;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0,0,0,.12);
  white-space: normal; /* biar wrap ke baris baru */
  min-width: 150px;
  max-width: 500px;    /* batas lebar tooltip */
  font-size: 14px;
  transition: 0.3s;
  z-index: 10;

}

.arrow-icon {
  font-size: 18px;
  color: #1aa34a;
  transition: transform 0.3s;
}

.tooltip-container:hover .arrow-icon {
  transform: translateX(4px); /* animasi geser saat hover */
}

.tooltip-container:hover .tooltip-content {
  visibility: visible;
  opacity: 1;
}

.profile-item strong { margin-right: 8px; color:#41524a; }
.profile-item span { color:#1aa34a; font-weight:600; }

.izin-form .form-group { margin-bottom:16px; display:flex; flex-direction:column; }
.izin-form label { font-weight:600; margin-bottom:6px; color:#41524a; }
.izin-form input[type="text"], .izin-form select, .izin-form textarea { padding:10px 12px; border-radius:8px; border:1px solid #ccc; font-size:14px; transition:0.3s; }
.izin-form input[type="text"]:focus, .izin-form select:focus, .izin-form textarea:focus { border-color:#1aa34a; outline:none; }
.izin-form textarea {
  resize: none; /* mencegah drag */
  max-height: 60px; /* tinggi maksimal */
  overflow-y: auto;
}
.submit-btn { display:flex; align-items:center; gap:6px; padding:10px 18px; border-radius:8px; border:none; background:#1aa34a; color:#fff; font-weight:600; cursor:pointer; transition:0.3s; }
.submit-btn:hover { background:#14903d; }

.table { width:100%; border-collapse:collapse; margin-top:12px; }
.table td { padding:10px 12px; border-bottom:1px solid #eee; }
.note { font-size:12px; color:#6e7b73; margin-top:6px; }

.footer-links { margin-top:20px; }
.footer-links a { justify-content: center; font-weight:600; color:#eee; text-decoration:none; }
.footer-links a.btn.secondary {
    background: #000000ff;
}
.footer-links a:hover { text-decoration:underline; }

/* ===== Card Container ===== */
.izin-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 22px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    margin-top: 20px;
}

/* ===== Title ===== */
.izin-title {
    margin-bottom: 18px;
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
}

/* ===== Table ===== */
.izin-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

/* Header */
.izin-table thead th {
    background: #f4f6f8;
    padding: 12px 10px;
    text-align: left;
    color: #34495e;
    font-weight: 600;
    border-bottom: 2px solid #e0e0e0;
}

/* Body */
.izin-table tbody td {
    padding: 10px;
    border-bottom: 1px solid #ececec;
    color: #2f3640;
    vertical-align: middle;
}

/* Hover */
.izin-table tbody tr:hover {
    background-color: #f9fbfc;
}

/* Empty State */
.izin-empty {
    text-align: center;
    padding: 20px;
    color: #7f8c8d;
    font-style: italic;
}

/* ===== Badge ===== */
.izin-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
    display: inline-block;
}

/* Status Colors */
.izin-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.izin-badge.disetujui {
    background: #d4edda;
    color: #155724;
}

.izin-badge.ditolak {
    background: #f8d7da;
    color: #721c24;
}

/* ===== Link ===== */
.izin-link {
    color: #3498db;
    font-weight: 500;
    text-decoration: none;
}

.izin-link:hover {
    text-decoration: underline;
}
</style>
</head>
<body>

<div class="dashboard">

<!-- TOPBAR -->
<div class="topbar">
    <h2>Dashboard Karyawan</h2>
    <div class="nav">
        <a href="?page=home" class="<?= ($page=='home')?'active':'' ?>">Home</a>
        <a href="?page=profil" class="<?= ($page=='profil')?'active':'' ?>">Data Diri</a>
        <a href="?page=izin" class="<?= ($page=='izin')?'active':'' ?>">Pengajuan Izin</a>
    </div>
    <a href="../backend/logout.php" class="logout"><ion-icon name="log-out-outline"></ion-icon> Logout</a>
</div>

<p style="margin-bottom:18px;">Selamat datang, <strong><?= htmlspecialchars($nama) ?></strong></p>

<?php if($page=='home'): ?>
<div class="card-grid">
    <div class="stat-card">
        <h4>Status Hari Ini</h4>
        <div class="value"><?= $status_hari_ini ?></div>
    </div>
    <div class="stat-card">
        <h4>Total Hadir Bulan Ini</h4>
        <div class="value"><?= $total_hadir ?></div>
    </div>
    <div class="stat-card">
        <h4>Jam Masuk Terakhir</h4>
        <div class="value"><?= $jam_masuk_terakhir ?></div>
    </div>
</div>

<div class="action-card">
    <h3><ion-icon name="log-in-outline"></ion-icon> Absensi Hari Ini</h3>
    <div class="action-buttons">
        <?php if(!$today): ?>
        <form action="../backend/checkin.php" method="post">
            <button class="btn" type="submit">
                <ion-icon name="log-in-outline"></ion-icon> Check In
            </button>
        </form>
        <?php endif; ?>

        <?php if($today && !$today['jam_keluar']): ?>
        <form action="../backend/checkout.php" method="post">
            <button class="btn danger" type="submit">
                <ion-icon name="log-out-outline"></ion-icon> Check Out
            </button>
        </form>
        <?php endif; ?>
    </div>

    <!-- Tombol Lihat Rekap -->
    <div class="footer-links" style="margin-top:16px;">
        <a href="riwayat.php" class="btn secondary">
            <ion-icon name="document-text-outline"></ion-icon> Lihat Rekap Absensi
        </a>
    </div>
</div>
<?php endif; ?>

<!-- HALAMAN PROFIL -->
<?php if($page=='profil'): ?>
<div class="card action-card">
  <div class="profile-header" style="display:flex; align-items:center; gap:12px;">
    <div class="profile-foto" style="width:60px; height:60px; border-radius:50%; overflow:hidden; background:#f0f0f0; display:flex; align-items:center; justify-content:center;">
      <?php if(!empty($profil['foto'])): ?>
        <img src="../uploads/foto/<?= htmlspecialchars($profil['foto']) ?>" alt="Foto Profil" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
      <?php else: ?>
        <ion-icon name="person-circle-outline" style="font-size:40px; color:#1aa34a;"></ion-icon>
      <?php endif; ?>
    </div>
    <div class="profile-name" style="font-weight:600; font-size:18px; color:#41524a;">
      <?= htmlspecialchars($profil['nama_karyawan']) ?>
    </div>
  </div>
</div>

  <div class="profile-grid">
    <!-- Contoh item dengan tooltip -->
    <div class="profile-item tooltip-container">
      <ion-icon name="id-card-outline"></ion-icon>
      <strong>NIP</strong>
      <ion-icon name="chevron-forward-outline" class="arrow-icon"></ion-icon> <!-- disembunyikan, bisa diganti default ringkas -->
      <div class="tooltip-content">
        <?= $profil['nip'] ?>
      </div>
    </div>

    <div class="profile-item tooltip-container">
      <ion-icon name="male-female-outline"></ion-icon>
      <strong>Jenis Kelamin</strong>
      <ion-icon name="chevron-forward-outline" class="arrow-icon"></ion-icon>
      <div class="tooltip-content">
        <?= $profil['jenis_kelamin'] ?: "-" ?>
      </div>
    </div>

    <div class="profile-item tooltip-container">
      <ion-icon name="calendar-outline"></ion-icon>
      <strong>Tanggal Lahir</strong>
      <ion-icon name="chevron-forward-outline" class="arrow-icon"></ion-icon>
      <div class="tooltip-content">
        <?= $profil['tgl_lahir'] ?: "-" ?>
      </div>
    </div>

    <div class="profile-item tooltip-container">
      <ion-icon name="home-outline"></ion-icon>
      <strong>Alamat</strong>
      <ion-icon name="chevron-forward-outline" class="arrow-icon"></ion-icon>
      <div class="tooltip-content">
        <?= $profil['alamat'] ?: "-" ?>
      </div>
    </div>

    <div class="profile-item tooltip-container">
      <ion-icon name="call-outline"></ion-icon>
      <strong>No HP</strong>
      <ion-icon name="chevron-forward-outline" class="arrow-icon"></ion-icon>
      <div class="tooltip-content">
        <?= $profil['no_hp'] ?: "-" ?>
      </div>
    </div>
  </div>

  <!-- Tombol Edit -->
  <div style="margin-top:20px;">
    <button class="btn" id="btn-edit"><ion-icon name="create-outline"></ion-icon> Edit Data Diri</button>
  </div>

    <!-- Form Edit (hidden by default) -->
  <form action="../backend/update_data.php" method="post" class="izin-form" id="form-edit" 
      style="display:none; margin-top:20px;" enctype="multipart/form-data">

    <input type="hidden" name="id_karyawan" value="<?= $id_karyawan ?>">

    <div class="form-group">
        <label>Foto Profile</label>
        <input type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png">
    </div>

    <div class="form-group">
        <label>Nama</label>
        <input type="text" name="nama_karyawan" value="<?= htmlspecialchars($profil['nama_karyawan']) ?>">
    </div>

    <div class="form-group">
        <label>Jenis Kelamin</label>
        <select name="jenis_kelamin">
            <option value="Laki-laki" <?= $profil['jenis_kelamin']=='Laki-laki'?'selected':'' ?>>Laki-laki</option>
            <option value="Perempuan" <?= $profil['jenis_kelamin']=='Perempuan'?'selected':'' ?>>Perempuan</option>
        </select>
    </div>

    <div class="form-group">
        <label for="tanggal">Tanggal Lahir</label>
        <input type="date" name="tgl_lahir" id="tgl_lahir" value="<?= htmlspecialchars($profil['tgl_lahir']) ?>">
    </div>

    <div class="form-group">
        <label>Alamat</label>
        <input type="text" name="alamat" value="<?= htmlspecialchars($profil['alamat']) ?>">
    </div>

    <div class="form-group">
        <label>No HP</label>
        <input type="text" name="no_hp" value="<?= htmlspecialchars($profil['no_hp']) ?>">
    </div>

    <button class="submit-btn" type="submit">
        <ion-icon name="save-outline"></ion-icon> Simpan Perubahan
    </button>
</form>
</div>

<?php endif; ?>

<!-- HALAMAN IZIN -->
<?php if($page=='izin'): ?>
<div class="card action-card">
    <h3><ion-icon name="document-text-outline"></ion-icon> Pengajuan Izin</h3>
    <?php if($izinHariIni): ?>
        <p style="color:#e5533d; font-weight:600;">
            Kamu sudah mengajukan izin hari ini: 
            <strong><?= htmlspecialchars($izinHariIni['jenis']) ?></strong> - 
            <span><?= htmlspecialchars($izinHariIni['status']) ?></span>
        </p>
    <?php else: ?>
        <!-- Form pengajuan izin -->
        <form class="izin-form" action="../backend/izin_store.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="tanggal">Tanggal Izin</label>
                <input type="date" name="tanggal" id="tanggal" value="<?= $today ?>" required>
            </div>
            <div class="form-group">
                <label for="jenis">Jenis</label>
                <select name="jenis" id="jenis" required>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Cuti">Cuti</option>
                </select>
            </div>
            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="2" placeholder="Misal: Sakit atau Urusan Pribadi" required></textarea>
            </div>
            <div class="form-group">
                <label for="bukti">Bukti (opsional)</label>
                <input type="file" name="bukti" id="bukti" accept=".jpg,.jpeg,.png,.pdf">
            </div>
            <button type="submit" class="submit-btn"><ion-icon name="send-outline"></ion-icon> Kirim</button>
        </form>
    <?php endif; ?>
</div>

<!-- Riwayat Izin -->
<div class="izin-card">
    <h3 class="izin-title">Riwayat Izin</h3>

    <table class="izin-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th>Bukti</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($riwayat) === 0): ?>
                <tr>
                    <td colspan="5" class="izin-empty">
                        Belum ada riwayat izin
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($riwayat)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['tanggal']) ?></td>
                        <td><?= htmlspecialchars($row['jenis']) ?></td>
                        <td><?= htmlspecialchars($row['keterangan']) ?></td>
                        <td>
                            <span class="izin-badge <?= strtolower($row['status']) ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['bukti']): ?>
                                <a class="izin-link"
                                   href="../uploads/bukti/<?= htmlspecialchars($row['bukti']) ?>"
                                   target="_blank">
                                   Lihat
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>


<script>
// Tombol edit → tampilkan form
document.getElementById('btn-edit').addEventListener('click', function(){
    document.getElementById('form-edit').style.display = 'block';
    this.style.display = 'none'; // sembunyikan tombol edit setelah diklik
    window.scrollTo({ top: this.offsetTop, behavior: 'smooth' }); // scroll ke form
});
</script>

</div>
</body>
</html>
