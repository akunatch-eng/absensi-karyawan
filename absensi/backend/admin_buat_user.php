<?php
require "db.php";
require "../config/mail.php";

$id_request = $_POST['id_request'] ?? null;
if(!$id_request) die("Request tidak valid");

/* Ambil data request + karyawan */
$q = mysqli_query($conn, "
    SELECT r.nama_karyawan, r.email, k.id_karyawan
    FROM request_akun r
    JOIN karyawan k ON r.nip = k.nip
    WHERE r.id_request = $id_request
");

if(mysqli_num_rows($q) === 0){
    die("Karyawan tidak ditemukan di database");
}

$data = mysqli_fetch_assoc($q);

/* Cek user sudah ada */
$cek = mysqli_query($conn, "
    SELECT id_user FROM users 
    WHERE email='{$data['email']}'
");
if(mysqli_num_rows($cek) > 0){
    die("Akun untuk email ini sudah dibuat");
}

/* Insert user TANPA password */
mysqli_query($conn, "
    INSERT INTO users (id_karyawan, email, status)
    VALUES ({$data['id_karyawan']}, '{$data['email']}', 'aktif')
");

$id_user = mysqli_insert_id($conn);

/* Generate token set password */
$token   = bin2hex(random_bytes(32));
$expired = date('Y-m-d H:i:s', strtotime('+1 day'));

mysqli_query($conn, "
    UPDATE users 
    SET reset_token='$token', reset_expired='$expired'
    WHERE id_user=$id_user
");

/* Update relasi */
mysqli_query($conn, "
    UPDATE karyawan 
    SET id_user=$id_user 
    WHERE id_karyawan={$data['id_karyawan']}
");

/* Update status request */
mysqli_query($conn, "
    UPDATE request_akun 
    SET status='selesai' 
    WHERE id_request=$id_request
");

/* Kirim email */
$link = "http://localhost/absensi/set_password.php?token=$token";

$body = "
<h3>Aktivasi Akun Absensi</h3>
<p>Halo <b>{$data['nama_karyawan']}</b>,</p>
<p>Akun absensi Anda telah disetujui.</p>
<p>Silakan buat password melalui link berikut:</p>
<p><a href='$link'>$link</a></p>
<p><small>Link berlaku 24 jam</small></p>
";

sendMail($data['email'], 'Aktivasi Akun Absensi', $body);

header("Location: ../dashboard/admin.php?page=request_akun&msg=Akun berhasil dibuat & email terkirim");
