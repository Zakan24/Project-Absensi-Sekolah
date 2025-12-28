<?php
require 'koneksi.php';
require_login();
$kelas = $mysqli->query("SELECT * FROM kelas")->fetch_all(MYSQLI_ASSOC);
$tanggal = $_GET['tanggal'] ?? '';
$id_kelas = intval($_GET['kelas'] ?? 0);
$rows = [];
if($tanggal){
    $q = "SELECT s.nis,s.nama_siswa,k.nama_kelas,a.status,a.tanggal
          FROM absensi a
          JOIN siswa s ON a.id_siswa=s.id
          JOIN kelas k ON s.id_kelas=k.id
          WHERE a.tanggal = ?";
    $stmt = $mysqli->prepare($q);
    $stmt->bind_param('s',$tanggal);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if($id_kelas){
        $rows = array_filter($rows, fn($r) => $r['nama_kelas'] === get_kelas_name($id_kelas));
    }
}
function get_kelas_name($id){ global $mysqli; $r = $mysqli->query('SELECT nama_kelas FROM kelas WHERE id='.(int)$id)->fetch_assoc(); return $r['nama_kelas'] ?? ''; }
?>
<!doctype html><html><head><meta charset="utf-8"><title>Rekap Absensi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<?php include 'navbar.php'; ?>
<div class="container py-4">
  <h3>Rekap & Export</h3>
  <form method="get" class="row g-2 mb-3">
    <div class="col-md-3"><input type="date" name="tanggal" class="form-control" value="<?php echo esc($tanggal); ?>"></div>
    <div class="col-md-3">
      <select name="kelas" class="form-select">
        <option value="0">-- Semua Kelas --</option>
        <?php foreach($kelas as $k) echo '<option value="'.esc($k['id']).'">'.esc($k['nama_kelas']).'</option>'; ?>
      </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary">Tampilkan</button></div>
  </form>

  <?php if($tanggal): ?>
    <div class="mb-2">
      <a href="export_csv.php?tanggal=<?php echo esc($tanggal); ?>&kelas=<?php echo $id_kelas; ?>" class="btn btn-outline-success">Export CSV</a>
      <a href="export_pdf.php?tanggal=<?php echo esc($tanggal); ?>&kelas=<?php echo $id_kelas; ?>" class="btn btn-outline-danger" target="_blank">Export PDF</a>
    </div>
    <table class="table table-striped">
      <thead><tr><th>#</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Status</th></tr></thead>
      <tbody>
      <?php $no=1; foreach($rows as $r): ?>
        <tr>
          <td><?php echo $no++;?></td>
          <td><?php echo esc($r['nis']);?></td>
          <td><?php echo esc($r['nama_siswa']);?></td>
          <td><?php echo esc($r['nama_kelas']);?></td>
          <td><?php echo esc($r['status']);?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info">Pilih tanggal untuk menampilkan rekap.</div>
  <?php endif; ?>
</div>
</body></html>
