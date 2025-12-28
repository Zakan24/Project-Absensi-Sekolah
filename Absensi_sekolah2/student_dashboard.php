<?php
// student_dashboard.php - Versi UI Modern + Upload Surat Dokter
require 'koneksi.php';
require_login();

// Keamanan: Cek Role
if($_SESSION['user']['role'] !== 'siswa'){ header('Location: dashboard.php'); exit; }

$user = $_SESSION['user'];
$id_siswa = $user['id'];
$tanggal_hari_ini = date('Y-m-d');
$jam_sekarang = date('H:i');

// ==========================================
// LOGIKA PENYIMPANAN ABSENSI (HADIR/SAKIT/IZIN)
// ==========================================
$msg = '';
$err = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    // Cek dulu apakah sudah absen hari ini
    $cek = $mysqli->query("SELECT id FROM absensi WHERE id_siswa=$id_siswa AND tanggal='$tanggal_hari_ini'");
    if($cek->num_rows > 0){
        $err = "Anda sudah melakukan absensi hari ini! Tidak bisa absen dua kali.";
    } else {
        $tipe = $_POST['tipe_absen']; // Hadir, Sakit, atau Izin
        
        if($tipe == 'Hadir'){
            // --- LOGIKA HADIR ---
            $stmt = $mysqli->prepare("INSERT INTO absensi (id_siswa, tanggal, status, keterangan) VALUES (?, ?, 'Hadir', ?)");
            $ket = "Hadir via Web jam $jam_sekarang";
            $stmt->bind_param('iss', $id_siswa, $tanggal_hari_ini, $ket);
            if($stmt->execute()) $msg = "Berhasil Absen Hadir! Selamat Belajar.";
            
        } elseif($tipe == 'Izin'){
            // --- LOGIKA IZIN ---
            $alasan = htmlspecialchars($_POST['alasan_izin']);
            $stmt = $mysqli->prepare("INSERT INTO absensi (id_siswa, tanggal, status, keterangan) VALUES (?, ?, 'Izin', ?)");
            $stmt->bind_param('iss', $id_siswa, $tanggal_hari_ini, $alasan);
            if($stmt->execute()) $msg = "Absen Izin tercatat. Menunggu konfirmasi guru.";

        } elseif($tipe == 'Sakit'){
            // --- LOGIKA SAKIT + UPLOAD FOTO ---
            $keterangan = htmlspecialchars($_POST['keterangan_sakit']);
            
            // Proses Upload Foto
            $nama_foto = null;
            if(isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] == 0){
                $target_dir = "uploads/";
                // Ganti nama file jadi unik: timestamp_namafileasli
                $nama_file_baru = time() . "_" . basename($_FILES["bukti_foto"]["name"]);
                $target_file = $target_dir . $nama_file_baru;
                
                // Pindahkan file ke folder uploads
                if(move_uploaded_file($_FILES["bukti_foto"]["tmp_name"], $target_file)){
                    $nama_foto = $nama_file_baru;
                }
            }

            $stmt = $mysqli->prepare("INSERT INTO absensi (id_siswa, tanggal, status, keterangan, bukti_foto) VALUES (?, ?, 'Sakit', ?, ?)");
            $stmt->bind_param('isss', $id_siswa, $tanggal_hari_ini, $keterangan, $nama_foto);
            if($stmt->execute()) $msg = "Status Sakit tercatat. Bukti berhasil diupload.";
        }
    }
}

// Data Status Hari Ini
$status_hari_ini = 'Belum Absen';
$q_today = $mysqli->query("SELECT status, keterangan FROM absensi WHERE id_siswa=$id_siswa AND tanggal='$tanggal_hari_ini'");
if($r = $q_today->fetch_assoc()){ $status_hari_ini = $r['status']; }

// Data Riwayat
$riwayat = $mysqli->query("SELECT * FROM absensi WHERE id_siswa=$id_siswa ORDER BY tanggal DESC LIMIT 7")->fetch_all(MYSQLI_ASSOC);

// Sapaan
$jam = date('H');
$sapa = ($jam < 11) ? "Selamat Pagi" : (($jam < 15) ? "Selamat Siang" : "Selamat Sore");
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Portal Siswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background: #f4f6f9; font-family: 'Plus Jakarta Sans', sans-serif; padding-bottom: 80px; }
        .header-bg {
            background: linear-gradient(135deg, #4361EE 0%, #304FFE 100%);
            padding: 30px 20px 80px; color: white; border-radius: 0 0 30px 30px; margin-bottom: -50px;
        }
        .card-menu {
            border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            background: white; overflow: hidden; transition: transform 0.2s;
        }
        .card-menu:active { transform: scale(0.98); }
        .btn-absen {
            border: none; padding: 20px; width: 100%; border-radius: 16px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            font-weight: 700; color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn-hadir { background: linear-gradient(135deg, #10B981, #059669); }
        .btn-sakit { background: linear-gradient(135deg, #F59E0B, #D97706); }
        .btn-izin  { background: linear-gradient(135deg, #3B82F6, #2563EB); }
        
        .riwayat-item {
            background: white; border-radius: 12px; margin-bottom: 10px; padding: 15px;
            display: flex; align-items: center; justify-content: between;
            border-left: 5px solid transparent; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .status-Hadir { border-left-color: #10B981; }
        .status-Sakit { border-left-color: #F59E0B; }
        .status-Izin  { border-left-color: #3B82F6; }
        .status-Alfa  { border-left-color: #EF4444; }
    </style>
</head>
<body>

<div class="header-bg text-center">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold">
            <i class="ph ph-student"></i> Portal Siswa
        </span>
        <a href="logout.php" class="text-white text-decoration-none small fw-bold" onclick="return confirm('Logout?')">
            Logout <i class="ph ph-sign-out"></i>
        </a>
    </div>
    <h2 class="fw-bold mb-1"><?php echo $sapa; ?>,</h2>
    <h4 class="fw-normal opacity-75"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h4>
    <p class="mt-2 small opacity-50">NIS: <?php echo htmlspecialchars($user['username']); ?></p>
</div>

<div class="container" style="max-width: 500px;">
    
    <div class="card-menu p-4 text-center mb-4">
        <small class="text-muted fw-bold text-uppercase ls-1">Status Hari Ini</small>
        <h5 class="mb-3 text-primary fw-bold"><?php echo date('d F Y'); ?></h5>
        
        <?php if($status_hari_ini == 'Belum Absen'): ?>
            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning fw-bold rounded-3 mb-4">
                <i class="ph ph-warning me-1"></i> Anda Belum Absen
            </div>

            <div class="row g-2">
                <div class="col-12">
                    <form method="post">
                        <input type="hidden" name="tipe_absen" value="Hadir">
                        <button class="btn-absen btn-hadir">
                            <i class="ph ph-hand-waving fs-1 mb-2"></i>
                            ABSEN HADIR
                        </button>
                    </form>
                </div>
                <div class="col-6">
                    <button class="btn-absen btn-sakit" data-bs-toggle="modal" data-bs-target="#modalSakit">
                        <i class="ph ph-thermometer fs-2 mb-2"></i>
                        SAKIT
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn-absen btn-izin" data-bs-toggle="modal" data-bs-target="#modalIzin">
                        <i class="ph ph-envelope-open fs-2 mb-2"></i>
                        IZIN
                    </button>
                </div>
            </div>

        <?php else: ?>
            <div class="py-4">
                <div class="mb-3">
                    <?php if($status_hari_ini=='Hadir') echo '<i class="ph ph-check-circle fs-1 text-success"></i>'; 
                          else if($status_hari_ini=='Sakit') echo '<i class="ph ph-first-aid fs-1 text-warning"></i>';
                          else echo '<i class="ph ph-info fs-1 text-primary"></i>'; ?>
                </div>
                <h4 class="fw-bold text-dark">Anda Sudah Absen</h4>
                <span class="badge bg-secondary px-3 py-2 rounded-pill mt-2 fs-6">
                    Status: <?php echo $status_hari_ini; ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="ph ph-check-circle me-2"></i> <?php echo $msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($err): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="ph ph-warning-circle me-2"></i> <?php echo $err; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h6 class="fw-bold text-secondary mb-3 ms-1">Riwayat 7 Hari Terakhir</h6>
    <div class="riwayat-list">
        <?php foreach($riwayat as $r): ?>
        <div class="riwayat-item status-<?php echo $r['status']; ?>">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="fw-bold text-dark"><?php echo date('d', strtotime($r['tanggal'])); ?></div>
                    <small class="text-muted"><?php echo date('M', strtotime($r['tanggal'])); ?></small>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><?php echo $r['status']; ?></h6>
                    <small class="text-muted" style="font-size: 11px;">
                        <?php echo $r['keterangan'] ? substr($r['keterangan'],0,30).'...' : '-'; ?>
                    </small>
                </div>
            </div>
            <?php if($r['status'] == 'Sakit' && $r['bukti_foto']): ?>
                <a href="uploads/<?php echo $r['bukti_foto']; ?>" target="_blank" class="btn btn-sm btn-light text-primary">
                    <i class="ph ph-image"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<div class="modal fade" id="modalSakit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-warning">🤒 Form Izin Sakit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="tipe_absen" value="Sakit">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keterangan Sakit</label>
                        <textarea name="keterangan_sakit" class="form-control bg-light border-0" rows="3" placeholder="Contoh: Demam tinggi, pusing..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Foto Surat Dokter / Obat</label>
                        <input type="file" name="bukti_foto" class="form-control" accept="image/*" required>
                        <small class="text-muted d-block mt-1" style="font-size:10px">*Wajib upload bukti foto</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-warning w-100 fw-bold text-white rounded-pill">Kirim Laporan Sakit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalIzin" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-primary">📩 Form Izin Tidak Masuk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <input type="hidden" name="tipe_absen" value="Izin">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Alasan Izin</label>
                        <textarea name="alasan_izin" class="form-control bg-light border-0" rows="3" placeholder="Contoh: Acara keluarga, urusan mendadak..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill">Kirim Izin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>