<?php
// dashboard.php - Final Version (Dengan Fitur Lihat Bukti Surat Dokter)
require 'koneksi.php';
require_login();
$user = $_SESSION['user'];

// 1. DATA STATISTIK UMUM
$c_kelas = $mysqli->query("SELECT COUNT(*) as c FROM kelas")->fetch_assoc()['c'];
$c_siswa = $mysqli->query("SELECT COUNT(*) as c FROM siswa")->fetch_assoc()['c'];

// 2. DATA ABSENSI HARI INI
$tgl = date('Y-m-d');

// Ambil semua data absensi hari ini (termasuk kolom bukti_foto)
$sql = "SELECT a.*, s.nama_siswa, s.nis, k.nama_kelas 
        FROM absensi a 
        JOIN siswa s ON a.id_siswa = s.id 
        LEFT JOIN kelas k ON s.id_kelas = k.id 
        WHERE a.tanggal = '$tgl' 
        ORDER BY k.nama_kelas ASC, s.nama_siswa ASC";

$result = $mysqli->query($sql);
$all_data = $result->fetch_all(MYSQLI_ASSOC);

// Pisahkan data
$list_hadir = [];
$list_sakit = [];
$list_izin  = [];
$list_alfa  = [];

foreach($all_data as $row){
    if($row['status'] == 'Hadir') $list_hadir[] = $row;
    if($row['status'] == 'Sakit') $list_sakit[] = $row;
    if($row['status'] == 'Izin')  $list_izin[]  = $row;
    if($row['status'] == 'Alfa')  $list_alfa[]  = $row;
}

// Hitung jumlah
$jml_hadir = count($list_hadir);
$jml_sakit = count($list_sakit);
$jml_izin  = count($list_izin);
$jml_alfa  = count($list_alfa);

// Sapaan Waktu
$jam = date('H');
$sapa = ($jam < 12) ? "Selamat Pagi" : (($jam < 15) ? "Selamat Siang" : "Selamat Sore");
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Sekolah</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background-color: #F8F9FC; font-family: 'Poppins', sans-serif; }
        
        .hero-card {
            background: linear-gradient(135deg, #4361EE 0%, #3F37C9 100%);
            color: white; border-radius: 20px; padding: 30px 20px; margin-bottom: 25px;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2);
        }
        .stat-card {
            border: none; border-radius: 16px; background: white;
            padding: 20px; height: 100%; transition: all 0.2s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            cursor: pointer; position: relative; overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        .stat-card::after { 
            content: 'Lihat Detail \2192'; position: absolute; bottom: 15px; right: 20px;
            font-size: 10px; color: #999; opacity: 0; transition: opacity 0.2s;
        }
        .stat-card:hover::after { opacity: 1; }

        .icon-box {
            width: 45px; height: 45px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; margin-bottom: 15px;
        }

        .bg-soft-green { background: #D1FAE5; color: #10B981; }
        .bg-soft-yellow { background: #FEF3C7; color: #F59E0B; }
        .bg-soft-blue { background: #DBEAFE; color: #2563EB; }
        .bg-soft-red { background: #FEE2E2; color: #EF4444; }

        /* Modal Styles */
        .modal-header { border-bottom: none; }
        .table-modal th { font-size: 12px; text-transform: uppercase; color: #666; }
        .badge-kelas { background: #eee; color: #333; font-size: 0.8rem; }
        .btn-lihat-bukti { font-size: 0.8rem; font-weight: 600; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-4">
    
    <div class="hero-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-1"><?php echo $sapa; ?>, <?php echo htmlspecialchars($user['nama_lengkap']); ?>!</h2>
                <p class="mb-0 opacity-75">Monitoring Absensi Hari Ini.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge bg-white text-primary px-4 py-3 rounded-pill shadow-sm fs-6">
                    <i class="ph ph-calendar-blank me-2"></i> <?php echo date('d F Y'); ?>
                </span>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3 text-secondary">Status Hari Ini</h5>
    <div class="row g-3 mb-5">
        
        <div class="col-6 col-md-3" data-bs-toggle="modal" data-bs-target="#modalHadir">
            <div class="stat-card border-bottom border-4 border-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="icon-box bg-soft-green"><i class="ph ph-check-circle"></i></div>
                    <h2 class="fw-bold mb-0 text-success"><?php echo $jml_hadir; ?></h2>
                </div>
                <div class="fw-bold text-dark">Hadir</div>
            </div>
        </div>

        <div class="col-6 col-md-3" data-bs-toggle="modal" data-bs-target="#modalSakit">
            <div class="stat-card border-bottom border-4 border-warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="icon-box bg-soft-yellow"><i class="ph ph-thermometer"></i></div>
                    <h2 class="fw-bold mb-0 text-warning"><?php echo $jml_sakit; ?></h2>
                </div>
                <div class="fw-bold text-dark">Sakit</div>
            </div>
        </div>

        <div class="col-6 col-md-3" data-bs-toggle="modal" data-bs-target="#modalIzin">
            <div class="stat-card border-bottom border-4 border-primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="icon-box bg-soft-blue"><i class="ph ph-envelope-open"></i></div>
                    <h2 class="fw-bold mb-0 text-primary"><?php echo $jml_izin; ?></h2>
                </div>
                <div class="fw-bold text-dark">Izin</div>
            </div>
        </div>

        <div class="col-6 col-md-3" data-bs-toggle="modal" data-bs-target="#modalAlfa">
            <div class="stat-card border-bottom border-4 border-danger">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="icon-box bg-soft-red"><i class="ph ph-warning-circle"></i></div>
                    <h2 class="fw-bold mb-0 text-danger"><?php echo $jml_alfa; ?></h2>
                </div>
                <div class="fw-bold text-dark">Alfa</div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3 text-secondary">Akses Cepat</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <a href="absensi.php" class="card p-3 text-decoration-none text-dark border-0 shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary text-white mb-0 me-3"><i class="ph ph-pencil-simple"></i></div>
                    <div><h6 class="fw-bold mb-1">Input Absensi</h6><small class="text-muted">Manual Guru</small></div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="siswa.php" class="card p-3 text-decoration-none text-dark border-0 shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-info text-white mb-0 me-3"><i class="ph ph-users"></i></div>
                    <div><h6 class="fw-bold mb-1">Data Siswa</h6><small class="text-muted">Database</small></div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="rekap.php" class="card p-3 text-decoration-none text-dark border-0 shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-success text-white mb-0 me-3"><i class="ph ph-file-csv"></i></div>
                    <div><h6 class="fw-bold mb-1">Laporan</h6><small class="text-muted">Export Excel/PDF</small></div>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHadir" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold text-success">✅ Siswa Hadir</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0">
                <table class="table table-striped mb-0 table-modal">
                    <thead class="table-light"><tr><th>Nama</th><th>Kelas</th><th>Waktu</th></tr></thead>
                    <tbody>
                        <?php foreach($list_hadir as $d): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($d['nama_siswa']); ?></td>
                            <td><span class="badge badge-kelas"><?php echo htmlspecialchars($d['nama_kelas']); ?></span></td>
                            <td><small class="text-muted"><?php echo date('H:i', strtotime($d['id'] ?? 'now')); ?></small></td>
                        </tr>
                        <?php endforeach; if(!$list_hadir) echo '<tr><td colspan="3" class="text-center py-3">Tidak ada data.</td></tr>'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSakit" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold text-warning">🤒 Siswa Sakit (Cek Surat Dokter)</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0">
                <table class="table table-striped mb-0 table-modal align-middle">
                    <thead class="table-light"><tr><th>Nama</th><th>Kelas</th><th>Keterangan</th><th class="text-end">Bukti Surat</th></tr></thead>
                    <tbody>
                        <?php foreach($list_sakit as $d): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($d['nama_siswa']); ?></td>
                            <td><span class="badge badge-kelas"><?php echo htmlspecialchars($d['nama_kelas']); ?></span></td>
                            <td class="small"><?php echo htmlspecialchars($d['keterangan'] ?: '-'); ?></td>
                            <td class="text-end">
                                <?php if(!empty($d['bukti_foto'])): ?>
                                    <a href="uploads/<?php echo $d['bukti_foto']; ?>" target="_blank" class="btn btn-sm btn-outline-primary btn-lihat-bukti rounded-pill px-3">
                                        <i class="ph ph-image me-1"></i> Lihat Foto
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Tidak ada foto</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; if(!$list_sakit) echo '<tr><td colspan="4" class="text-center py-3">Tidak ada data.</td></tr>'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalIzin" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold text-primary">📩 Siswa Izin</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0">
                <table class="table table-striped mb-0 table-modal">
                    <thead class="table-light"><tr><th>Nama</th><th>Kelas</th><th>Alasan</th></tr></thead>
                    <tbody>
                        <?php foreach($list_izin as $d): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($d['nama_siswa']); ?></td>
                            <td><span class="badge badge-kelas"><?php echo htmlspecialchars($d['nama_kelas']); ?></span></td>
                            <td class="text-primary small fw-bold"><?php echo htmlspecialchars($d['keterangan'] ?: '-'); ?></td>
                        </tr>
                        <?php endforeach; if(!$list_izin) echo '<tr><td colspan="3" class="text-center py-3">Tidak ada data.</td></tr>'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAlfa" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold text-danger">⚠️ Siswa Alfa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0">
                <table class="table table-striped mb-0 table-modal">
                    <thead class="table-light"><tr><th>Nama</th><th>Kelas</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach($list_alfa as $d): ?>
                        <tr>
                            <td class="fw-bold text-danger"><?php echo htmlspecialchars($d['nama_siswa']); ?></td>
                            <td><span class="badge badge-kelas"><?php echo htmlspecialchars($d['nama_kelas']); ?></span></td>
                            <td class="small text-danger">Tanpa Keterangan</td>
                        </tr>
                        <?php endforeach; if(!$list_alfa) echo '<tr><td colspan="3" class="text-center py-3">Tidak ada data.</td></tr>'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>