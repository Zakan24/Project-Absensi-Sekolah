<?php
require 'koneksi.php';
$msg=''; $err='';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $nama = trim($_POST['nama']);
    $password = $_POST['password'];
    if($username && $password){
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO admin (username,password,nama_lengkap,role) VALUES (?,?,?,'admin')");
        $stmt->bind_param('sss', $username, $hash, $nama);
        if($stmt->execute()) $msg = 'Admin berhasil dibuat. Silakan login.';
        else $err = 'Gagal: '.$stmt->error;
    } else $err = 'Isi username & password.';
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Buat Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-5">
  <div class="col-md-6 mx-auto">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>Buat Akun Admin (Jalankan Sekali)</h4>
        <?php if($msg): ?><div class="alert alert-success"><?php echo esc($msg); ?></div><?php endif; ?>
        <?php if($err): ?><div class="alert alert-danger"><?php echo esc($err); ?></div><?php endif; ?>
        <form method="post">
          <div class="mb-2"><label class="form-label">Nama</label><input name="nama" class="form-control"></div>
          <div class="mb-2"><label class="form-label">Username</label><input name="username" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Password</label><input name="password" type="password" class="form-control" required></div>
          <button class="btn btn-primary">Buat Admin</button>
        </form>
        <hr>
        <a href="login.php">Ke halaman login</a>
      </div>
    </div>
  </div>
</div>
</body></html>
