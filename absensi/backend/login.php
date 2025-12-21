<?php
session_start();
require "db.php";

/* ==== VALIDASI ==== */
if (!isset($_POST['email'], $_POST['password'])) {
    header("Location: ../index.php?error=Data tidak lengkap");
    exit;
}

$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

/* ==== QUERY LOGIN SESUAI DB BARU ==== */
$sql = "
SELECT 
        u.id_user,
        u.email,
        u.password,
        u.role,
        u.status,
        k.id_karyawan,
        k.nama_karyawan
    FROM users u
    LEFT JOIN karyawan k ON u.id_user = k.id_user
    WHERE u.email = '$email'
    LIMIT 1
";

$q = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($q);

/* ==== CEK LOGIN ==== */
if (!$user || !password_verify($password, $user['password'])) {
    header("Location: ../index.php?error=Email atau password salah");
    exit;
}

/* ==== SESSION ==== */
$_SESSION['login'] = true;
$_SESSION['id_user'] = $user['id_user'];
$_SESSION['role'] = $user['role'];
$_SESSION['email'] = $user['email'];

if ($user['id_karyawan']) {
    $_SESSION['id_karyawan'] = $user['id_karyawan'];
    $_SESSION['nama'] = $user['nama_karyawan'];
}

/* ==== REDIRECT DASHBOARD ==== */
header("Location: ../dashboard/{$user['role']}.php");
exit;
