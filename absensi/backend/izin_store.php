<?php
session_start();
require "db.php";

// Pastikan user login
if (!isset($_SESSION['id_karyawan'])) {
    header("Location: ../index.php");
    exit;
}

$id_karyawan = $_SESSION['id_karyawan'];
$tanggal     = $_POST['tanggal'] ?? '';
$jenis       = $_POST['jenis'] ?? '';
$keterangan  = $_POST['keterangan'] ?? '';
$bukti      = null;

// Validasi input
if (!$tanggal || !$jenis || !$keterangan) {
    header("Location: ../dashboard/karyawan.php?page=izin&msg=Semua field wajib diisi");
    exit;
}

// Cek apakah sudah ada izin di tanggal yang sama
$cek = mysqli_query($conn, "SELECT id_izin FROM izin WHERE id_karyawan = $id_karyawan AND tanggal = '$tanggal'");
if (mysqli_num_rows($cek) > 0) {
    header("Location: ../dashboard/karyawan.php?page=izin&msg=Sudah mengajukan izin di tanggal ini");
    exit;
}

// Handle upload bukti (opsional)
if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
    $uploadDir  = '../uploads/bukti/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $filename   = time() . '_' . basename($_FILES['bukti']['name']);
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['bukti']['tmp_name'], $targetPath)) {
        $bukti = mysqli_real_escape_string($conn, $filename);
    }
}

// Escape input untuk query
$jenis      = mysqli_real_escape_string($conn, $jenis);
$keterangan = mysqli_real_escape_string($conn, $keterangan);

// Insert ke database
$query = "INSERT INTO izin (id_karyawan, tanggal, jenis, keterangan, bukti, status)
          VALUES ($id_karyawan, '$tanggal', '$jenis', '$keterangan', ".($bukti ? "'$bukti'" : "NULL").", 'Menunggu')";

if (mysqli_query($conn, $query)) {
    header("Location: ../dashboard/karyawan.php?page=izin&msg=Izin berhasil diajukan");
    exit;
} else {
    header("Location: ../dashboard/karyawan.php?page=izin&msg=Gagal mengajukan izin");
    exit;
}
