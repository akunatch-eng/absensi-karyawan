<?php
$allowed_roles = ['admin'];
require "../backend/auth_guard.php";

$data = mysqli_query($conn,"
SELECT a.id_absen,u.nama,a.tanggal,a.check_in,a.check_out,a.status
FROM absensi a
JOIN karyawan u ON a.id_karyawan=u.id_karyawan
ORDER BY a.tanggal DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Rekap Absensi</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="navbar">
<h2>Rekap Absensi</h2>
<a href="admin.php">Kembali</a>
</div>

<div class="container">
<table border="0" width="100%" cellpadding="10">
<tr>
<th>Nama</th><th>Tanggal</th><th>Masuk</th><th>Keluar</th><th>Status</th><th>Aksi</th>
</tr>

<?php while($r=mysqli_fetch_assoc($data)): ?>
<tr>
<td><?= $r['nama'] ?></td>
<td><?= $r['tanggal'] ?></td>
<td><?= $r['check_in'] ?></td>
<td><?= $r['check_out'] ?></td>
<td><?= $r['status'] ?></td>
<td>
<form action="../backend/update_status.php" method="post">
<input type="hidden" name="id_absen" value="<?= $r['id_absen'] ?>">
<select name="status">
<option>hadir</option>
<option>izin</option>
<option>sakit</option>
<option>alpa</option>
</select>
<button class="btn">Update</button>
</form>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

</body>
</html>
