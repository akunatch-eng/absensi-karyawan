<?php
require "db.php";

$id_user = $_POST['id_user'] ?? null;
if (!$id_user) die("User tidak valid");

$q = $conn->prepare("SELECT status FROM users WHERE id_user=?");
$q->bind_param("i", $id_user);
$q->execute();
$status = $q->get_result()->fetch_assoc()['status'];

$newStatus = $status === 'aktif' ? 'nonaktif' : 'aktif';

$u = $conn->prepare("UPDATE users SET status=? WHERE id_user=?");
$u->bind_param("si", $newStatus, $id_user);
$u->execute();

header("Location: ../dashboard/admin.php?page=karyawan");
