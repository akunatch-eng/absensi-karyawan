<?php
$allowed_roles = ['supervisor'];
require "../backend/auth_guard.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Supervisor</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="navbar">
<h2>Dashboard Supervisor</h2>
<a href="../backend/logout.php">Logout</a>
</div>

<div class="container">
<a class="card" href="supervisor_laporan.php">Laporan Kehadiran</a>
</div>

</body>
</html>
