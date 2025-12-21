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
$jam_masuk = date('H:i:s');

/* ==== CEK SUDAH CHECK-IN ATAU BELUM ==== */
$cek = mysqli_query($conn, "
    SELECT id_absensi 
    FROM absensi 
    WHERE id_karyawan = $id_karyawan 
      AND tanggal = '$tanggal'
    LIMIT 1
");

if (mysqli_num_rows($cek) > 0) {
    // Sudah check-in hari ini
    header("Location: ../dashboard/karyawan.php?msg=sudah_checkin");
    exit;
}

/* ==== INSERT CHECK-IN ==== */
$query = "
    INSERT INTO absensi (id_karyawan, tanggal, jam_masuk, status)
    VALUES ($id_karyawan, '$tanggal', '$jam_masuk', 'Hadir')
";

if (!mysqli_query($conn, $query)) {
    die("Gagal check-in: " . mysqli_error($conn));
}

/* ==== REDIRECT ==== */
header("Location: ../dashboard/karyawan.php?msg=checkin_ok");
exit;
