<?php
session_start();
require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_karyawan = trim($_POST['nama_karyawan'] ?? '');
    $nip           = trim($_POST['nip'] ?? '');
    $email         = trim($_POST['email'] ?? '');

    if (!$nama_karyawan || !$nip || !$email) {
        header("Location: request_akun.php?msg=Semua field wajib diisi");
        exit;
    }

    // Escape data
    $nama_karyawan = mysqli_real_escape_string($conn, $nama_karyawan);
    $nip           = mysqli_real_escape_string($conn, $nip);
    $email         = mysqli_real_escape_string($conn, $email);

    // Cek apakah email/NIP sudah request sebelumnya dan masih pending
    $check = mysqli_query($conn,"SELECT * FROM request_akun WHERE (email='$email' OR nip='$nip') AND status='pending'");
    if(mysqli_num_rows($check) > 0){
        header("Location: request_akun.php?msg=Anda sudah mengajukan request sebelumnya. Tunggu konfirmasi admin.");
        exit;
    }

    $query = "INSERT INTO request_akun (nama_karyawan, nip, email) 
              VALUES ('$nama_karyawan','$nip','$email')";

    if (mysqli_query($conn, $query)) {
        header("Location: request_akun.php?msg=Request berhasil dikirim. Tunggu konfirmasi admin.");
    } else {
        header("Location: request_akun.php?msg=Gagal mengirim request");
    }
    exit;
}
?>
