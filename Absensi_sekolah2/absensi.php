<?php
// absensi.php - Modern UI Version
require 'koneksi.php';
require_login();

// Logika PHP Tetap Sama (Backend tidak berubah)
$kelas = $mysqli->query("SELECT * FROM kelas")->fetch_all(MYSQLI_ASSOC);
$selected_kelas = intval($_GET['kelas'] ?? ($kelas[0]['id'] ?? 0));
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

// Ambil data siswa
$stmt = $mysqli->prepare("SELECT * FROM siswa WHERE id_kelas = ? ORDER BY nama_siswa ASC");
$stmt->bind_param('i', $selected_kelas); 
$stmt->execute(); 
$siswa = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Proses Simpan
$msg = '';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['simpan'])){
    $tanggal_post = $_POST['tanggal']; 
    $id_guru = $_SESSION['user']['id'] ?? null;
    $count_changes = 0;
    
    // Gunakan transaction agar aman
    $mysqli->begin_transaction();
    try {
        foreach($_POST['status'] as $id_siswa => $status){
            $id_siswa = intval($id_siswa);
            // Cek data lama
            $ps = $mysqli->prepare("SELECT id, status FROM absensi WHERE id_siswa=? AND tanggal=?");
            $ps->bind_param('is', $id_siswa, $tanggal_post); 
            $ps->execute(); 
            $r = $ps->get_result();
            
            if($r->num_rows){
                $row = $r->fetch_assoc();
                if($row['status'] !== $status){ // Hanya update jika beda
                    $upd = $mysqli->prepare("UPDATE absensi SET status=?, id_guru=? WHERE id=?");
                    $upd->bind_param('sii', $status, $id_guru, $row['id']); 
                    $upd->execute();
                    $count_changes++;
                }
            } else {
                $ins = $mysqli->prepare("INSERT INTO absensi (id_siswa,tanggal,status,id_guru) VALUES (?,?,?,?)");
                $ins->bind_param('issi', $id_siswa, $tanggal_post, $status, $id_guru); 
                $ins->execute();
                $count_changes++;
            }
        }
        $mysqli->commit();
        $msg = "Berhasil menyimpan data absensi ($count_changes data diupdate).";
    } catch (Exception $e) {
        $mysqli->rollback();
        $msg = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Ambil data absensi existing untuk ditampilkan
$abs_map = [];
$ps = $mysqli->prepare("SELECT * FROM absensi WHERE tanggal=?"); 
$ps->bind_param('s', $tanggal); 
$ps->execute(); 
$r = $ps->get_result();
while($a = $r->fetch_assoc()) $abs_map[$a['id_siswa']] = $a;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Input Absensi Modern</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361EE; /* Royal Blue */
            --bg-light: #F0F4F8;
            --hadir: #4CC9F0; --izin: #F72585; --sakit: #FNC600; --alfa: #E63946;
        }
        body { background-color: var(--bg-light); font-family: 'Inter', sans-serif; padding-bottom: 80px; }
        h1, h2, h3, h4, h5 { font-family: 'Poppins', sans-serif; color: #2B2D42; }
        
        /* Card Styles */
        .student-card {
            border: none;
            border-radius: 16px;
            background: white;
            transition: all 0.2s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .student-card:active { transform: scale(0.98); }
        .avatar-placeholder {
            width: 45px; height: 45px;
            background: #e9ecef; color: #6c757d;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 18px;
        }

        /* Modern Radio Buttons (Chips) */
        .btn-check:checked + .btn-outline-success { background-color: #198754; color: white; border-color: #198754; }
        .btn-check:checked + .btn-outline-warning { background-color: #ffc107; color: black; border-color: #ffc107; }
        .btn-check:checked + .btn-outline-info { background-color: #0dcaf0; color: white; border-color: #0dcaf0; }
        .btn-check:checked + .btn-outline-danger { background-color: #dc3545; color: white; border-color: #dc3545; }
        
        .selector-label { font-size: 0.8rem; font-weight: 600; padding: 0.4rem 0.8rem; border-radius: 20px; }

        /* Floating Action Bar */
        .fab-container {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: white; padding: 15px;
            box-shadow: 0 -4px 10px rgba(0,0,0,0.05);
            z-index: 1000;
        }
        .btn-simpan {
            background: var(--primary); border: none;
            border-radius: 12px; font-weight: 600;
            padding: 12px; width: 100%; box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Input Absensi</h4>
        <span class="badge bg-primary rounded-pill"><?php echo count($siswa); ?> Siswa</span>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo esc($msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3 mb-4 border-0 shadow-sm rounded-4">
        <form method="get" class="row g-2">
            <div class="col-7">
                <label class="small text-muted mb-1">Pilih Kelas</label>
                <select name="kelas" class="form-select border-0 bg-light fw-bold text-primary" onchange="this.form.submit()">
                    <?php foreach($kelas as $k): ?>
                        <option value="<?php echo $k['id']; ?>" <?php echo ($k['id']==$selected_kelas?'selected':''); ?>>
                            Kelas <?php echo esc($k['nama_kelas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-5">
                <label class="small text-muted mb-1">Tanggal</label>
                <input type="date" name="tanggal" class="form-control border-0 bg-light" value="<?php echo esc($tanggal); ?>" onchange="this.form.submit()">
            </div>
        </form>
    </div>

    <form method="post" id="formAbsensi">
        <input type="hidden" name="simpan" value="1">
        <input type="hidden" name="tanggal" value="<?php echo esc($tanggal); ?>">
        
        <div class="row g-3">
            <?php 
            foreach($siswa as $s): 
                $st = $abs_map[$s['id']]['status'] ?? 'Hadir'; // Default Hadir
                $inisial = strtoupper(substr($s['nama_siswa'], 0, 1));
                $sid = intval($s['id']);
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="student-card p-3 h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-placeholder me-3"><?php echo $inisial; ?></div>
                        <div>
                            <h6 class="mb-0 fw-bold"><?php echo esc($s['nama_siswa']); ?></h6>
                            <small class="text-muted">NIS: <?php echo esc($s['nis']); ?></small>
                        </div>
                    </div>
                    
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="status[<?php echo $sid; ?>]" id="h_<?php echo $sid; ?>" value="Hadir" <?php echo ($st=='Hadir'?'checked':''); ?>>
                        <label class="btn btn-outline-success selector-label" for="h_<?php echo $sid; ?>">Hadir</label>

                        <input type="radio" class="btn-check" name="status[<?php echo $sid; ?>]" id="s_<?php echo $sid; ?>" value="Sakit" <?php echo ($st=='Sakit'?'checked':''); ?>>
                        <label class="btn btn-outline-warning selector-label" for="s_<?php echo $sid; ?>">Sakit</label>

                        <input type="radio" class="btn-check" name="status[<?php echo $sid; ?>]" id="i_<?php echo $sid; ?>" value="Izin" <?php echo ($st=='Izin'?'checked':''); ?>>
                        <label class="btn btn-outline-info selector-label" for="i_<?php echo $sid; ?>">Izin</label>
                        
                        <input type="radio" class="btn-check" name="status[<?php echo $sid; ?>]" id="a_<?php echo $sid; ?>" value="Alfa" <?php echo ($st=='Alfa'?'checked':''); ?>>
                        <label class="btn btn-outline-danger selector-label" for="a_<?php echo $sid; ?>">Alfa</label>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(empty($siswa)): ?>
            <div class="text-center py-5 text-muted">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" width="100" class="mb-3 opacity-50">
                <p>Tidak ada siswa di kelas ini.</p>
            </div>
        <?php endif; ?>

        <div class="fab-container">
            <button class="btn btn-primary btn-simpan text-white">
                <span class="me-2">💾</span> Simpan Absensi (<?php echo date('d/m', strtotime($tanggal)); ?>)
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>