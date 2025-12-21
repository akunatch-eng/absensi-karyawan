<?php
require "db.php";

if (!isset($_SESSION['login'])) {
    header("Location: ../index.php");
    exit;
}

if (isset($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
    die("Akses ditolak");
}
