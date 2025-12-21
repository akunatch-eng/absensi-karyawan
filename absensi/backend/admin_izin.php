<?php
session_start();
require "db.php";

$id_izin = $_POST['id_izin'] ?? 0;
$action = $_POST['action'] ?? '';

if (!$id_izin || !in_array($action, ['approve', 'reject'])) {
    die("Invalid request");
}

// Mapping ke enum
$status = $action === 'approve' ? 'Disetujui' : 'Ditolak';

$query = "UPDATE izin SET status='$status' WHERE id_izin=$id_izin";

if (mysqli_query($conn, $query)) {
    header("Location: ../dashboard/admin.php?page=izin&msg=Status berhasil diubah");
    exit;
} else {
    die("Gagal update status: " . mysqli_error($conn));
}
?>
