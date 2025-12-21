<?php
session_start();
require "db.php"; // pastikan koneksi database

$id_karyawan = $_SESSION['id_karyawan'] ?? null;
if (!$id_karyawan) die("ID karyawan tidak ditemukan!");

$nama = mysqli_real_escape_string($conn, $_POST['nama_karyawan'] ?? '');
$jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin'] ?? '');
$tgl_lahir = $_POST['tgl_lahir'] ?? null;
$alamat = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');
$no_hp = mysqli_real_escape_string($conn, $_POST['no_hp'] ?? '');

$foto_db = ''; // default kosong

// Upload foto jika ada
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $uploads_dir = "../uploads/foto/";
    if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

    $tmp_name = $_FILES['foto']['tmp_name'];
    $filename = time() . '_' . basename($_FILES['foto']['name']);
    if (move_uploaded_file($tmp_name, $uploads_dir . $filename)) {
        $foto_db = $filename;
    }
}

// Update database
$sql = "UPDATE karyawan SET 
        nama_karyawan='$nama',
        jenis_kelamin='$jenis_kelamin',
        tgl_lahir='$tgl_lahir',
        alamat='$alamat',
        no_hp='$no_hp'";

if ($foto_db) {
    $sql .= ", foto='$foto_db'";
}

$sql .= " WHERE id_karyawan=$id_karyawan";

if (mysqli_query($conn, $sql)) {
    header("Location: ../dashboard/profil.php?msg=Data berhasil diperbarui");
} else {
    echo "Error: " . mysqli_error($conn);
}

header("Location: ../dashboard/karyawan.php?page=profil&success=updated");
exit;
