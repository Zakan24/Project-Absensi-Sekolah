<?php
require 'koneksi.php';
require_login();

// ==============================
// 1. LOGIKA PHP (SISWA & KELAS)
// ==============================

// A. TAMBAH SISWA
if(isset($_POST['tambah_siswa'])){
    $nis = trim($_POST['nis']); 
    $nama = trim($_POST['nama']); 
    $id_kelas = intval($_POST['id_kelas']);
    
    // Auto Password = MD5(NIS)
    $password = md5($nis); 
    
    $stmt = $mysqli->prepare("INSERT INTO siswa (nama_siswa, nis, id_kelas, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssis', $nama, $nis, $id_kelas, $password);
    if($stmt->execute()){ header('Location: siswa.php'); exit; }
}

// B. TAMBAH KELAS (BARU)
if(isset($_POST['tambah_kelas'])){
    $nama_kelas = trim($_POST['nama_kelas']);
    if($nama_kelas){
        $stmt = $mysqli->prepare("INSERT INTO kelas (nama_kelas) VALUES (?)");
        $stmt->bind_param('s', $nama_kelas);
        if($stmt->execute()){ header('Location: siswa.php'); exit; }
    }
}

// C. HAPUS SISWA
if(isset($_GET['hapus_siswa'])){
    $id = intval($_GET['hapus_siswa']);
    $mysqli->query("DELETE FROM siswa WHERE id=$id");
    header('Location: siswa.php'); exit;
}

// D. HAPUS KELAS (BARU)
if(isset($_GET['hapus_kelas'])){
    $id = intval($_GET['hapus_kelas']);
    // Hati-hati: Menghapus kelas akan menghapus semua siswa di dalamnya (ON DELETE CASCADE di Database)
    $mysqli->query("DELETE FROM kelas WHERE id=$id");
    header('Location: siswa.php'); exit;
}

// ==============================
// 2. AMBIL DATA DARI DATABASE
// ==============================

// Data Kelas (sekalian hitung jumlah siswa di kelas itu)
$q_kelas = "SELECT k.*, COUNT(s.id) as total_siswa 
            FROM kelas k LEFT JOIN siswa s ON k.id=s.id_kelas 
            GROUP BY k.id ORDER BY k.nama_kelas ASC";
$kelas = $mysqli->query($q_kelas)->fetch_all(MYSQLI_ASSOC);

// Data Siswa
$q_siswa = "SELECT s.*, k.nama_kelas FROM siswa s 
            LEFT JOIN kelas k ON s.id_kelas=k.id 
            ORDER BY s.id DESC";
$siswa = $mysqli->query($q_siswa)->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Siswa & Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Poppins', sans-serif; }
        .card-custom { border:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
        .table-scroll { max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row g-4">
        
        <div class="col-lg-8">
            
            <div class="card card-custom p-4 mb-4">
                <h5 class="fw-bold mb-3 text-primary">➕ Tambah Siswa Baru</h5>
                <form method="post" class="row g-3">
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">NIS</label>
                        <input name="nis" class="form-control" placeholder="1001" required>
                    </div>
                    <div class="col-md-5">
                        <label class="small fw-bold text-muted">Nama Lengkap</label>
                        <input name="nama" class="form-control" placeholder="Nama Siswa" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">Kelas</label>
                        <select name="id_kelas" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach($kelas as $k): ?>
                                <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button name="tambah_siswa" class="btn btn-primary w-100 fw-bold">Save</button>
                    </div>
                </form>
                <div class="mt-2 small text-muted">* Password siswa otomatis diset sama dengan NIS.</div>
            </div>

            <div class="card card-custom p-4">
                <h5 class="fw-bold mb-3">📋 Daftar Siswa (<?php echo count($siswa); ?>)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $no=1; foreach($siswa as $s): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($s['nis']); ?></td>
                                <td><?php echo htmlspecialchars($s['nama_siswa']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($s['nama_kelas'] ?? '-'); ?></span></td>
                                <td>
                                    <a href="siswa.php?hapus_siswa=<?php echo $s['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Hapus siswa ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-custom p-4 sticky-top" style="top: 20px; z-index:1;">
                <h5 class="fw-bold mb-3 text-success">🏫 Data Kelas</h5>
                
                <form method="post" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="nama_kelas" class="form-control" placeholder="Nama Kelas Baru" required>
                        <button name="tambah_kelas" class="btn btn-success fw-bold">Tambah</button>
                    </div>
                </form>

                <div class="table-scroll border rounded">
                    <ul class="list-group list-group-flush">
                        <?php foreach($kelas as $k): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($k['nama_kelas']); ?></strong>
                                <br><small class="text-muted"><?php echo $k['total_siswa']; ?> Siswa</small>
                            </div>
                            <a href="siswa.php?hapus_kelas=<?php echo $k['id']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('PERINGATAN!\nMenghapus kelas akan menghapus SEMUA SISWA di dalamnya.\n\nYakin hapus kelas <?php echo htmlspecialchars($k['nama_kelas']); ?>?')">
                               ×
                            </a>
                        </li>
                        <?php endforeach; ?>
                        
                        <?php if(empty($kelas)): ?>
                            <li class="list-group-item text-center text-muted py-3">Belum ada kelas.</li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="alert alert-warning mt-3 mb-0 py-2 small">
                    <i class="bi bi-exclamation-triangle"></i> <b>Info:</b> Jika Anda menghapus kelas, semua siswa di kelas tersebut juga akan terhapus.
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>