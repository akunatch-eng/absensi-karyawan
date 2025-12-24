<?php
session_start();
require "../backend/db.php";
require "../backend/auth_guard.php";

$page = $_GET['page'] ?? 'dashboard';

if($page==='dashboard'){
    $total_karyawan = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM karyawan"))['total'];
    $total_request  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM request_akun WHERE status='pending'"))['total'];
    $total_izin     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM izin WHERE tanggal=CURDATE()"))['total'];
    $total_absensi  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM absensi WHERE tanggal=CURDATE()"))['total'];
}

if($page==='karyawan'){
    $qAllKaryawan = mysqli_query($conn, "
    SELECT 
        k.id_karyawan,
        k.nama_karyawan,
        k.nip,
        k.tgl_lahir,
        k.jenis_kelamin,
        k.alamat,
        u.email,
        u.status
    FROM karyawan k
    LEFT JOIN users u ON k.id_user = u.id_user
    ORDER BY k.nip ASC
");

}

if($page==='request_akun'){
    $qRequestList = mysqli_query($conn,"SELECT * FROM request_akun ORDER BY id_request DESC");
}

if($page==='izin'){
    $start=$_GET['start']??null;
    $end=$_GET['end']??null;

    $sql="SELECT izin.*, karyawan.nama_karyawan
          FROM izin JOIN karyawan USING(id_karyawan) WHERE 1";
    if($start) $sql.=" AND tanggal>='$start'";
    if($end)   $sql.=" AND tanggal<='$end'";
    $sql.=" ORDER BY tanggal DESC";

    $qIzinList=mysqli_query($conn,$sql);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#f4f7f5;color:#41524a}
.dashboard{max-width:1200px;margin:30px auto;padding:0 20px}

/* TOPBAR */
.topbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:30px; flex-wrap:wrap; gap:18px; }
.topbar h2 { font-size:28px; font-weight:700; margin-right:auto; }
.nav { display:flex; gap:18px; flex-wrap:wrap; }
.nav a { text-decoration:none; font-weight:500; color:#41524a; transition:0.3s; }
.nav a.active { color:#1aa34a; font-weight:600; }
.nav a:hover { color:#14903d; }
.logout { color:#b33; font-weight:600; text-decoration:none; }
.logout:hover { opacity:0.8; }


/* CARD */
.card{background:#fff;padding:22px;border-radius:14px;box-shadow:0 8px 20px rgba(0,0,0,.08);margin-bottom:28px}

/* STAT CARD */
.card-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:30px; }
.stat-card { background:#fff; padding:20px; border-radius:14px; box-shadow:0 10px 25px rgba(0,0,0,.06); transition:0.3s; }
.stat-card:hover { transform:translateY(-3px); }
.stat-card h4 { font-size:14px; color:#6e7b73; margin-bottom:8px; }
.stat-card .value { font-size:22px; font-weight:700; color:#1aa34a; }


/* ===== FINAL TABLE MODEL ===== */
.table-wrapper{width:100%;overflow-x:auto;margin-top:16px}
.admin-table{width:100%;border-collapse:collapse;min-width:900px;font-size:14px}
.admin-table th{background:#f5f8f7;padding:12px 14px;text-align:left;border-bottom:2px solid #e2e8e4;white-space:nowrap}
.admin-table td{padding:12px 14px;border-bottom:1px solid #edf1ef;vertical-align:middle}
.admin-table tr:hover{background:#f9fbfa}

/* BADGE */
.badge{padding:5px 12px;border-radius:14px;font-size:12px;font-weight:600;color:#fff}
.badge.aktif,.badge.approved{background:#1aa34a}
.badge.nonaktif,.badge.rejected{background:#e5533d}
.badge.waiting{background:#f1c40f}

/* BUTTON */
.btn-small{padding:5px 10px;font-size:12px;border-radius:6px;border:none;cursor:pointer}
.green{background:#1aa34a;color:#fff}
.red{background:#e5533d;color:#fff}

/* FILTER */
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.filter-bar input,.filter-bar button{padding:6px 10px;border-radius:6px;border:1px solid #ccc}
.filter-bar button{background:#1aa34a;color:#fff;border:none}

/*modal */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.4);
    z-index: 1000;
}

.modal-content {
    background: #fff;
    width: 400px;
    margin: 80px auto;
    padding: 20px;
    border-radius: 6px;
}

.modal-content input,
.modal-content textarea,
.modal-content select {
    width: 100%;
    margin-bottom: 10px;
}


@media(max-width:768px){
    .admin-table{min-width:700px}
}
</style>
</head>

<body>
<div class="dashboard">

<div class="topbar">
    <h2>Dashboard Admin</h2>
    <div class="nav">
        <a href="?page=dashboard" class="<?= $page=='dashboard'?'active':'' ?>">Dashboard</a>
        <a href="?page=karyawan" class="<?= $page=='karyawan'?'active':'' ?>">Karyawan</a>
        <a href="?page=request_akun" class="<?= $page=='request_akun'?'active':'' ?>">Request Akun</a>
        <a href="?page=izin" class="<?= $page=='izin'?'active':'' ?>">Pengajuan Izin</a>
    </div>
    <a href="../backend/logout.php" class="logout">Logout</a>
</div>

<!-- DASHBOARD SUMMARY -->
<?php if($page==='dashboard'): ?>
<div class="card-grid">
    <div class="stat-card"><h4>Total Karyawan</h4><div class="value"><?= $total_karyawan ?></div></div>
    <div class="stat-card"><h4>Request Pending</h4><div class="value"><?= $total_request ?></div></div>
    <div class="stat-card"><h4>Izin Hari Ini</h4><div class="value"><?= $total_izin ?></div></div>
    <div class="stat-card"><h4>Absensi Hari Ini</h4><div class="value"><?= $total_absensi ?></div></div>
</div>
<?php endif; ?>

<?php if ($page === 'karyawan'): ?>
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center">
        <h3>Daftar Karyawan</h3>
        <button onclick="document.getElementById('modalTambah').style.display='block'">
            + Tambah Karyawan
        </button>
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Email</th>
                    <th>JK</th>
                    <th>tgl lahir</th>
                    <th>Alamat</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = mysqli_fetch_assoc($qAllKaryawan)): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nama_karyawan']) ?></td>
                    <td><?= htmlspecialchars($r['nip']) ?></td>
                    <td><?= htmlspecialchars($r['email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['jenis_kelamin'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['tgl_lahir'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['alamat'] ?? '-') ?></td>
                    <td>
                        <?php if ($r['status']): ?>
                            <span class="badge <?= $r['status'] === 'aktif' ? 'aktif' : 'nonaktif' ?>">
                                <?= $r['status'] ?>
                            </span>
                        <?php else: ?>
                            <span class="badge nonaktif">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div id="modalTambah" class="modal">
    <div class="modal-content">
        <h3>Tambah Karyawan</h3>

        <form method="post" action="../backend/karyawan_data.php">
            <label>Nama Karyawan</label>
            <input type="text" name="nama_karyawan" required>

            <label>NIP</label>
            <input type="text" name="nip" required>

            <label>Jenis Kelamin</label>
            <select name="jenis_kelamin">
                <option value="">- Pilih -</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>

            <label>Tanggal Lahir</label>
            <input type="date" name="tgl_lahir">

            <label>Alamat</label>
            <textarea name="alamat"></textarea>

            <label>No HP</label>
            <input type="text" name="no_hp">

            <div style="margin-top:15px">
                <button type="submit">Simpan</button>
                <button type="button" onclick="document.getElementById('modalTambah').style.display='none'">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>


<?php if ($page === 'request_akun'): ?>
<div class="card">
    <h3>Request Akun</h3>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($r = mysqli_fetch_assoc($qRequestList)): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nama_karyawan']) ?></td>
                    <td><?= htmlspecialchars($r['nip']) ?></td>
                    <td><?= htmlspecialchars($r['email']) ?></td>

                    <!-- STATUS -->
                    <td>
                        <span class="badge <?= $r['status'] === 'pending' ? 'waiting' : 'aktif' ?>">
                            <?= ucfirst($r['status']) ?>
                        </span>
                    </td>

                    <!-- AKSI -->
                    <td>
                        <?php if ($r['status'] === 'pending'): ?>
                            <form action="../backend/admin_buat_user.php"
                                  method="post"
                                  style="display:flex; gap:6px;">

                                <input type="hidden" name="id_request"
                                       value="<?= $r['id_request'] ?>">

                                <button type="submit"
                                        class="btn-small green">
                                    Setujui
                                </button>
                            </form>
                        <?php else: ?>
                            <span style="color:#6e7b73; font-weight:600;">
                                Selesai
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>


<?php if($page==='izin'): ?>
<div class="card">
<h3>Pengajuan Izin</h3>

<form class="filter-bar">
<input type="hidden" name="page" value="izin">
<input type="date" name="start" value="<?= $_GET['start']??'' ?>">
<input type="date" name="end" value="<?= $_GET['end']??'' ?>">
<button type="submit">Filter</button>
</form>

<div class="table-wrapper">
<table class="admin-table">
<thead><tr>
<th>Nama</th><th>Tanggal</th><th>Jenis</th><th>Keterangan</th><th>Status</th><th>Aksi</th>
</tr></thead>
<tbody>
<?php while ($r = mysqli_fetch_assoc($qIzinList)): ?>
<tr>
    <td><?= htmlspecialchars($r['nama_karyawan']) ?></td>
    <td><?= htmlspecialchars($r['tanggal']) ?></td>
    <td><?= htmlspecialchars($r['jenis']) ?></td>
    <td><?= htmlspecialchars($r['keterangan']) ?></td>

    <!-- STATUS -->
    <td>
        <?php
            $statusClass = match ($r['status']) {
                'Menunggu'  => 'badge waiting',
                'Disetujui' => 'badge approved',
                'Ditolak'   => 'badge rejected',
                default     => 'badge'
            };
        ?>
        <span class="<?= $statusClass ?>">
            <?= htmlspecialchars($r['status']) ?>
        </span>
    </td>

    <!-- AKSI -->
    <td>
        <?php if ($r['status'] === 'Menunggu'): ?>
            <form action="../backend/admin_izin.php" method="post" style="display:flex; gap:6px;">
                <input type="hidden" name="id_izin" value="<?= $r['id_izin'] ?>">
                <button type="submit" name="action" value="approve" class="btn-small green">✓</button>
                <button type="submit" name="action" value="reject" class="btn-small red">✗</button>
            </form>
        <?php else: ?>
            <span style="color:#6e7b73; font-weight:600;">Selesai</span>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
<?php endif; ?>

</div>
</body>
</html>
