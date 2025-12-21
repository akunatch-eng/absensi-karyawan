<?php
require "db.php";

$id_karyawan = $_POST['id_karyawan'] ?? null;

// =====================
// FIELD WAJIB
// =====================
$nama = trim($_POST['nama_karyawan'] ?? '');
$nip  = trim($_POST['nip'] ?? '');

if ($nama === '' || $nip === '') {
    die("Nama dan NIP wajib diisi");
}

// =====================
// FIELD OPSIONAL
// =====================
$email         = $_POST['email'] ?? null;
$jenis_kelamin = $_POST['jenis_kelamin'] ?? null;
$tgl_lahir     = $_POST['tgl_lahir'] ?? null;
$alamat        = $_POST['alamat'] ?? null;
$no_hp         = $_POST['no_hp'] ?? null;

// =====================
// MODE EDIT
// =====================
if ($id_karyawan) {

    $sql = "UPDATE karyawan SET
        nama_karyawan = ?,
        nip = ?,
        jenis_kelamin = ?,
        tgl_lahir = ?,
        alamat = ?,
        no_hp = ?
        WHERE id_karyawan = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssi",
        $nama,
        $nip,
        $jenis_kelamin,
        $tgl_lahir,
        $alamat,
        $no_hp,
        $id_karyawan
    );

    $stmt->execute();
    header("Location: ../dashboard/admin.php?page=karyawan&msg=updated");
    exit;
}

// =====================
// MODE TAMBAH
// =====================
$sql = "INSERT INTO karyawan
(nama_karyawan, nip, jenis_kelamin, tgl_lahir, alamat, no_hp)
VALUES (?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssss",
    $nama,
    $nip,
    $jenis_kelamin,
    $tgl_lahir,
    $alamat,
    $no_hp
);

$stmt->execute();
header("Location: ../dashboard/admin.php?page=karyawan&msg=created");
