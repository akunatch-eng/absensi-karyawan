<?php
session_start();
require "../backend/db.php";
require "../backend/auth_guard.php"; // Pastikan hanya admin bisa akses

// Tentukan page aktif
$page = $_GET['page'] ?? 'dashboard';

// =======================
// DATA DASHBOARD
// =======================
if($page==='dashboard'){
    $total_karyawan = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM karyawan"))['total'];
    $total_request = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM request_akun WHERE status='pending'"))['total'];
    $total_izin = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM izin WHERE tanggal=CURDATE()"))['total'];
    $total_absensi = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM absensi WHERE tanggal=CURDATE()"))['total'];
}

// =======================
// DATA KARYAWAN
// =======================
if($page==='karyawan'){
    $qAllKaryawan = mysqli_query($conn, "
        SELECT 
            k.id_karyawan,
            k.nama_karyawan,
            k.nip,
            k.jenis_kelamin,
            k.tgl_lahir,
            k.alamat,
            k.no_hp,
            u.id_user,
            u.email,
            u.status
        FROM karyawan k
        LEFT JOIN users u ON k.id_user = u.id_user
        ORDER BY k.nama_karyawan ASC
    ");
}

// =======================
// REQUEST AKUN
// =======================
if($page==='request_akun'){
    $qRequestList = mysqli_query($conn,"SELECT * FROM request_akun ORDER BY id_request DESC");
}

// =======================
// PENGAJUAN IZIN
// =======================
if($page==='izin'){
    // Filter tanggal
    $start = $_GET['start'] ?? null;
    $end = $_GET['end'] ?? null;

    $sql = "SELECT izin.*, karyawan.nama_karyawan 
            FROM izin 
            JOIN karyawan ON izin.id_karyawan=karyawan.id_karyawan
            WHERE 1=1";
    if($start) $sql .= " AND tanggal >= '$start'";
    if($end) $sql .= " AND tanggal <= '$end'";
    $sql .= " ORDER BY tanggal DESC";

    $qIzinList = mysqli_query($conn, $sql);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<style>
/* =======================
   GLOBAL
======================= */
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Poppins',sans-serif; background:#f4f7f5; color:#41524a; line-height:1.5; }
.dashboard {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px; /* padding di kiri-kanan */
}


/* TOPBAR */
.topbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:30px; flex-wrap:wrap; gap:18px; }
.topbar h2 { font-size:28px; font-weight:700; margin-right:auto; }
.nav { display:flex; gap:18px; flex-wrap:wrap; }
.nav a { text-decoration:none; font-weight:500; color:#41524a; transition:0.3s; }
.nav a.active { color:#1aa34a; font-weight:600; }
.nav a:hover { color:#14903d; }
.logout { color:#b33; font-weight:600; text-decoration:none; }
.logout:hover { opacity:0.8; }

/* STAT CARD */
.card-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:30px; }
.stat-card { background:#fff; padding:20px; border-radius:14px; box-shadow:0 10px 25px rgba(0,0,0,.06); transition:0.3s; }
.stat-card:hover { transform:translateY(-3px); }
.stat-card h4 { font-size:14px; color:#6e7b73; margin-bottom:8px; }
.stat-card .value { font-size:22px; font-weight:700; color:#1aa34a; }

/* CARD */
.card {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,.08);
    margin-bottom: 28px;

    /* Tambahan supaya lebar pas */
    max-width: 100%;       /* tidak melebihi container */
    overflow-x: auto;      /* scroll horizontal kalau tabel terlalu lebar */
    box-sizing: border-box; /* padding tidak nambahin lebar total */
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    transition: 0.3s;
}

.badge.aktif { background-color: #1aa34a; color: #fff; }
.badge.nonaktif { background-color: #e5533d; color: #fff; }

/* TABLE CARD */
.table-card { overflow-x:auto; margin-top:12px; }
.table { width:100%; border-collapse:collapse; }
.table th, table td {
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
    font-size :14px;
    word-break: break-word; /* supaya teks panjang wrap */
}

/* Contoh set minimal width agar kolom tidak terlalu sempit */
table th:nth-child(1), table td:nth-child(1) { min-width: 150px; } /* Nama */
table th:nth-child(2), table td:nth-child(2) { min-width: 100px; } /* NIP */
table th:nth-child(3), table td:nth-child(3) { min-width: 180px; } /* Email */
table th:nth-child(4), table td:nth-child(4) { min-width: 120px; } /* Jenis Kelamin */
table th:nth-child(5), table td:nth-child(5) { min-width: 120px; } /* Tanggal Lahir */
table th:nth-child(6), table td:nth-child(6) { min-width: 180px; } /* Alamat */
table th:nth-child(7), table td:nth-child(7) { min-width: 120px; } /* No HP */
table th:nth-child(8), table td:nth-child(8) { min-width: 80px; }  /* Status */
table th:nth-child(9), table td:nth-child(9) { min-width: 160px; } /* Aksi */
.badge.waiting { background:#f1c40f; color:#fff; }  /* Menunggu */
.badge.approved { background:#1aa34a; color:#fff; } /* Disetujui */
.badge.rejected { background:#e74c3c; color:#fff; }  /* Ditolak */

/* Modal */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:1000; }
.modal-content { background:#fff; margin:8% auto; padding:20px; border-radius:12px; max-width:500px; position:relative; }
.close { position:absolute; top:12px; right:16px; font-size:24px; cursor:pointer; }
.modal input, .modal select { width:100%; padding:10px 12px; margin-bottom:12px; border-radius:8px; border:1px solid #ccc; }

.btn-small { padding:4px 8px; font-size:12px; border-radius:6px; }
.btn-tambah { background:#1aa34a; color:#fff; }
.req {
  color: #e74c3c;
  font-weight: 600;
}

.opt {
  font-size: 12px;
  color: #8b8b8b;
  font-weight: 400;
}

/* BUTTON */
.action-group {
    display: flex;
    gap: 8px; /* jarak antar tombol */
    justify-content: flex-start;
}

.action-group button {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: 0.3s;
}

.action-group button.edit {
    background-color: #1aa34a;
    color: #fff;
}

.action-group button.toggle {
    background-color: #e0e0e0;
    color: #41524a;
}

.action-group button.toggle.red {
    background-color: #e5533d;
    color: #fff;
}

.action-group button:hover {
    opacity: 0.85;
}


/* FILTER */
.filter-bar { display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; align-items:center; }
.filter-bar input[type=date], .filter-bar select { padding:6px 10px; border-radius:6px; border:1px solid #ccc; }
.filter-bar button { padding:6px 12px; border:none; border-radius:6px; background:#1aa34a; color:#fff; cursor:pointer; }
.filter-bar button:hover { opacity:0.85; }

/* RESPONSIVE */
@media(max-width:768px){
    .topbar { flex-direction:column; align-items:flex-start; }
    .filter-bar { flex-direction:column; align-items:flex-start; }
}
</style>
</head>
<body>
<div class="dashboard">

<!-- TOPBAR -->
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

<!-- DAFTAR KARYAWAN -->
<?php if($page==='karyawan'): ?>
<div class="card">
    <h3>Daftar Karyawan</h3>
    <button class="btn-tambah" onclick="openModal('tambah')">+ Tambah Karyawan</button>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>NIP</th>
                <th>Email</th>
                <th>Jns Kelamin</th>
                <th>Tgl Lahir</th>
                <th>Alamat</th>
                <th>No HP</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row=mysqli_fetch_assoc($qAllKaryawan)): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                <td><?= htmlspecialchars($row['nip']) ?></td>
                <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['jenis_kelamin']) ?></td>
                <td><?= htmlspecialchars($row['tgl_lahir']) ?></td>
                <td><?= htmlspecialchars($row['alamat']) ?></td>
                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                <td>
    <span class="badge <?= ($row['status'] ?? '-') === 'aktif' ? 'aktif' : 'nonaktif' ?>">
        <?= $row['status'] ?? '-' ?>
    </span>
</td>
<td>
    <div class="action-group">
        <button class="edit" onclick="openModal('edit', <?= $row['id_karyawan'] ?>)">
            <ion-icon name="create-outline" style="vertical-align: middle;"></ion-icon> Edit
        </button>
        <form action="../backend/toggle_status.php" method="post" style="margin:0;">
            <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
            <button class="toggle <?= $row['status']==='aktif'?'red':'' ?>" type="submit">
                <?= $row['status']==='aktif'?'Nonaktifkan':'Aktifkan' ?>
            </button>
        </form>
    </div>
</td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>


<!-- CRUD KARYAWAN MODAL -->
<div id="modalKaryawan" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3 id="modalTitle">Tambah Karyawan</h3>
    <form id="formKaryawan" action="../backend/karyawan_data.php" method="post">
      <input type="hidden" name="id_karyawan" id="id_karyawan">

      <div class="form-group">
  <label>Nama Karyawan <span class="req">*</span></label>
  <input type="text" name="nama_karyawan" id="nama_karyawan" required>
</div>

<div class="form-group">
  <label>NIP <span class="req">*</span></label>
  <input type="text" name="nip" id="nip" required>
</div>

      <div class="form-group">
  <label>Email <span class="opt">(opsional)</span></label>
  <input type="email" name="email" id="email">
</div>

<div class="form-group">
  <label>Tanggal Lahir <span class="opt">(opsional)</span></label>
  <input type="date" name="tgl_lahir" id="tgl_lahir">
</div>

    <div class="form-group">
      <label>Jenis Kelamin<span class="opt">(opsional)</span></label>
      <select name="jenis_kelamin" id="jenis_kelamin">
        <option value="Laki-laki">Laki-laki</option>
        <option value="Perempuan">Perempuan</option>
      </select>
    </div>
      <div class="form-group">
  <label>Alamat<span class="opt">(opsional)</span></label>
  <input type="text" id="alamat" name="alamat">
</div>

<div class="form-group">
  <label>No HP<span class="opt">(opsional)</span></label>
  <input type="text" id="no_hp" name="no_hp">
</div>

      <button type="submit" class="btn green">Simpan</button>
    </form>
  </div>
</div>

<script>
function openModal(type, id = null) {
    document.getElementById('modalKaryawan').style.display = 'flex';
    const form = document.getElementById('formKaryawan');

    // semua field opsional
    const optionalFields = document.querySelectorAll('.optional-field');

    if (type === 'tambah') {
        document.getElementById('modalTitle').innerText = 'Tambah Karyawan';
        form.reset();
        document.getElementById('id_karyawan').value = '';

        // sembunyikan field opsional
        optionalFields.forEach(f => f.style.display = 'none');

    } else {
        document.getElementById('modalTitle').innerText = 'Edit Karyawan';

        // tampilkan field opsional
        optionalFields.forEach(f => f.style.display = 'block');

        fetch('../backend/get_karyawan.php?id=' + id)
            .then(res => res.json())
            .then(data => {
                document.getElementById('id_karyawan').value = data.id_karyawan;
                document.getElementById('nama_karyawan').value = data.nama_karyawan;
                document.getElementById('nip').value = data.nip;
                document.getElementById('email').value = data.email ?? '';
                document.getElementById('jenis_kelamin').value = data.jenis_kelamin ?? '';
                document.getElementById('tgl_lahir').value = data.tgl_lahir ?? '';
                document.getElementById('alamat').value = data.alamat ?? '';
                document.getElementById('no_hp').value = data.no_hp ?? '';
            });
    }
}

function closeModal() {
    document.getElementById('modalKaryawan').style.display = 'none';
}

</script>




<!-- REQUEST AKUN -->
<?php if($page==='request_akun'): ?>
<div class="card action-card">
    <h3><ion-icon name="person-add-outline"></ion-icon> Request Pembuatan Akun</h3>
    <table>
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
            <?php while($row=mysqli_fetch_assoc($qRequestList)): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                <td><?= htmlspecialchars($row['nip']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <span class="badge <?= $row['status']==='pending'?'nonaktif':'aktif' ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </td>
                <td>
                    <?php if($row['status']==='pending'): ?>
                        <form action="../backend/admin_buat_user.php" method="post" style="display:inline-flex; gap:6px;">
                            <input type="hidden" name="id_request" value="<?= $row['id_request'] ?>">
                            <button class="btn green" type="submit" style="padding:6px 12px; font-size:13px;">
                                <ion-icon name="checkmark-done-outline" style="font-size:14px;"></ion-icon> Buat Akun
                            </button>
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
<?php endif; ?>


<!-- PENGAJUAN IZIN -->
<?php if($page==='izin'): ?>
<div class="card">
    <h3>Pengajuan Izin</h3>

    <!-- Filter Tanggal -->
    <form class="filter-bar" method="get">
        <input type="hidden" name="page" value="izin">
        <label>Dari:</label>
        <input type="date" name="start" value="<?= $_GET['start'] ?? '' ?>">
        <label>Sampai:</label>
        <input type="date" name="end" value="<?= $_GET['end'] ?? '' ?>">
        <button type="submit" class="btn green">Filter</button>
    </form>

    <div class="table-card">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Keterangan</th>
                    <th>Bukti</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row=mysqli_fetch_assoc($qIzinList)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                    <td><?= $row['tanggal'] ?></td>
                    <td><?= htmlspecialchars($row['jenis']) ?></td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                    <td>
                        <?php if($row['bukti']): ?>
                        <a href="../uploads/bukti/<?= $row['bukti'] ?>" target="_blank" class="btn btn-small green">Lihat</a>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                            $statusClass = match($row['status']) {
                                'Menunggu' => 'badge waiting',
                                'Disetujui' => 'badge approved',
                                'Ditolak' => 'badge rejected',
                                default => 'badge',
                            };
                        ?>
                        <span class="<?= $statusClass ?>"><?= $row['status'] ?></span>
                    </td>
                    <td>
                        <?php if($row['status']=='Menunggu'): ?>
                        <form action="../backend/admin_izin.php" method="post" style="display:flex; gap:6px;">
                            <input type="hidden" name="id_izin" value="<?= $row['id_izin'] ?>">
                            <button class="btn green btn-small" name="action" value="approve">✓</button>
                            <button class="btn red btn-small" name="action" value="reject">✗</button>
                        </form>
                        <?php else: ?>
                        <span style="color:#6e7b73;">Selesai</span>
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
