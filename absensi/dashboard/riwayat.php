<?php
// dashboard/riwayat.php
session_start();

$allowed_roles = ['karyawan'];
require "../backend/auth_guard.php";
require "../backend/db.php";

$id_karyawan = $_SESSION['id_karyawan'];

/* ==== FILTER TANGGAL ==== */
$start = $_GET['start'] ?? date('Y-m-01'); // default awal bulan
$end   = $_GET['end'] ?? date('Y-m-d');    // default hari ini

$q = mysqli_query($conn, "
   SELECT 
        tanggal,
        jam_masuk,
        jam_keluar,
        status
    FROM absensi
    WHERE id_karyawan = $id_karyawan
      AND tanggal BETWEEN '$start' AND '$end'
   ORDER BY tanggal DESC
");
?>
<style>
    /* ==========================
   Dashboard / Riwayat Absensi
========================== */
.dashboard {
    max-width: 1100px;   /* sama seperti dashboard utama */
    margin: 30px auto;
    padding: 0 20px;
    font-family: 'Poppins', sans-serif;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.topbar h2 {
    font-size: 30px;
    font-weight: 800;
}

.topbar a.btn.small {
    background: #1aa34a;
    color: #fff;
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: 0.3s;
}

.topbar a.btn.small:hover {
    opacity: 0.85;
}
/* CSS */
.divider {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 40px 0;
    gap: 15px; /* jarak teks dengan garis */
}

.divider-line {
    flex: 1;
    height: 2px;
    background: #e0e0e0; /* garis netral */
    border-radius: 2px;
}

.divider-text {
    font-size: 20px;
    font-weight: 700;
    color: #1aa34a; /* aksen hijau konsisten dengan dashboard */
    white-space: nowrap;
    padding: 0 10px;
}


/* Card umum */
.card {
    background: #fff;
    border-radius: 14px;
    padding: 10px 20px 20px 20px;
    margin-bottom: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.06);
}

/* Table Card */
.table-card {
    overflow-x: auto;
}

table.table {
    width: 100%;
    border-collapse: collapse;
}

table.table th, table.table td {
    padding: 12px 15px;
    text-align: left;
}

table.table th {
    background: #f4f7f5;
    font-weight: 600;
}

/* Badge Status */
.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.badge.hadir { background: #1aa34a; color: #fff; }
.badge.alpha { background: #e5533d; color: #fff; }
.badge.belum { background: #6e7b73; color: #fff; }

/* Highlight hari libur */
tr.holiday {
    background: #ffe5e5;
}

/* Filter Bar */
.filter-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-bar input[type=date] {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.filter-bar button {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    background: #1aa34a;
    color: #fff;
    cursor: pointer;
    transition: 0.3s;
}

.filter-bar button:hover {
    opacity: 0.85;
}

/* Responsive */
@media(max-width: 768px){
    .topbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .filter-bar {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Absensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
</head>
<body>

<div class="dashboard">

    <!-- TOP BAR -->
    <div class="topbar">
        <h2>Riwayat Absensi</h2>
        <a href="karyawan.php" class="btn small">← Kembali</a>
    </div>
<div class="divider">
    <span class="divider-line"></span>
    <h3 class="divider-text">Rekap Absensi Kamu</h3>
    <span class="divider-line"></span>
</div>

    <!-- Filter Card -->
    <div class="card">
        <form class="filter-bar" method="get">
            <label>Dari:</label>
            <input type="date" name="start" value="<?= htmlspecialchars($start) ?>">
            <label>Sampai:</label>
            <input type="date" name="end" value="<?= htmlspecialchars($end) ?>">
            <button type="submit" class="btn">Filter</button>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card table-card">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
<?php if (mysqli_num_rows($q) === 0): ?>
    <tr>
        <td colspan="4" style="text-align:center;color:#6e7b73">
            Belum ada data absensi
        </td>
    </tr>
<?php else: ?>
    <?php while ($row = mysqli_fetch_assoc($q)): ?>
        <?php 
            $isHoliday = isset($holidays) && in_array($row['tanggal'], $holidays);
            $status_class = strtolower($row['status'] ?? '');
            if (!$row['jam_masuk']) $status_class = 'belum';
        ?>
        <tr class="<?= $isHoliday ? 'holiday' : '' ?>">
            <td><?= htmlspecialchars($row['tanggal']) ?></td>
            <td><?= $row['jam_masuk'] ?: '-' ?></td>
            <td><?= $row['jam_keluar'] ?: '-' ?></td>
            <td>
                <span class="badge <?= $status_class ?>">
                    <?= $row['status'] ?: 'Belum Absen' ?>
                </span>
            </td>
        </tr>
    <?php endwhile; ?>
<?php endif; ?>
</tbody>
        </table>
    </div>

</div>


</body>
</html>
