<?php
$allowed_roles = ['supervisor'];
require "../backend/auth_guard.php";

$q = mysqli_query($conn,"
SELECT u.nama,
COUNT(a.id_absen) total_hadir
FROM karyawan u
LEFT JOIN absensi a ON u.id_karyawan=a.id_karyawan
GROUP BY u.id_karyawan
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Laporan Supervisor</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="navbar">
<h2>Laporan Kehadiran</h2>
<a href="supervisor.php">Kembali</a>
</div>

<div class="container">
<table width="50%" cellpadding="10">
<tr><th>Nama</th><th>Total Hadir</th></tr>
<?php while($d=mysqli_fetch_assoc($q)): ?>
<tr>
<td><?= $d['nama'] ?></td>
<td><?= $d['total_hadir'] ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

</body>
</html>
