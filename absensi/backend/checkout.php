<?php
session_start();
require "db.php";

/* ==== VALIDASI LOGIN ==== */
if (!isset($_SESSION['id_karyawan'])) {
    header("Location: ../index.php");
    exit;
}

/* ==== SET TIMEZONE ==== */
date_default_timezone_set('Asia/Jakarta');

/* ==== AMBIL DATA ==== */
$id_karyawan = $_SESSION['id_karyawan'];
$tanggal = date('Y-m-d');
$jam_keluar = date('H:i:s');

/* ==== CEK SUDAH CHECK-IN DAN BELUM CHECK-OUT ==== */
$cek = mysqli_query($conn, "
    SELECT id_absensi, jam_keluar 
    FROM absensi 
    WHERE id_karyawan = $id_karyawan 
      AND tanggal = '$tanggal'
    LIMIT 1
");

if (mysqli_num_rows($cek) == 0) {
    // Belum check-in hari ini
    header("Location: ../dashboard/karyawan.php?msg=belum_checkin");
    exit;
}

$absensi = mysqli_fetch_assoc($cek);

if (!empty($absensi['jam_pulang'])) {
    // Sudah checkout
    header("Location: ../dashboard/karyawan.php?msg=sudah_checkout");
    exit;
}

/* ==== UPDATE CHECK-OUT ==== */
$query = "
    UPDATE absensi
    SET jam_keluar = '$jam_keluar', status = 'Hadir'
    WHERE id_absensi = " . $absensi['id_absensi'] . "
";

if (!mysqli_query($conn, $query)) {
    die("Gagal check-out: " . mysqli_error($conn));
}

/* ==== REDIRECT ==== */
header("Location: ../dashboard/karyawan.php?msg=checkout_ok");
exit;
