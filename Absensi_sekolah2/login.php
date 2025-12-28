<?php
require 'koneksi.php';
if(is_logged_in()) {
    // Redirect sesuai role
    if($_SESSION['user']['role'] == 'siswa') header('Location: student_dashboard.php');
    else header('Location: dashboard.php');
    exit;
}

$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = $_POST['username']; // Bisa diisi Username Admin atau NIS Siswa
    $password = $_POST['password'];
    
    // 1. Cek Login ADMIN/GURU Dulu
    $stmt = $mysqli->prepare("SELECT id,username,password,nama_lengkap,role FROM admin WHERE username=? LIMIT 1");
    $stmt->bind_param('s',$username);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if($user = $res->fetch_assoc()){
        // Verifikasi Password Admin
        if(password_verify($password, $user['password']) || md5($password) === $user['password']){
            unset($user['password']);
            $_SESSION['user'] = $user;
            header('Location: dashboard.php'); exit;
        }
    } else {
        // 2. Jika bukan Admin, Cek Login SISWA (Login pakai NIS)
        $stmt2 = $mysqli->prepare("SELECT id, nis as username, password, nama_siswa as nama_lengkap, 'siswa' as role FROM siswa WHERE nis=? LIMIT 1");
        $stmt2->bind_param('s', $username);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        
        if($siswa = $res2->fetch_assoc()){
            // Verifikasi Password Siswa (Default MD5 dari NIS)
            // Note: Idealnya siswa nanti bisa ganti password, tapi untuk awal kita support MD5
            if(password_verify($password, $siswa['password']) || md5($password) === $siswa['password']){
                unset($siswa['password']);
                $_SESSION['user'] = $siswa; // Simpan data siswa di session
                header('Location: student_dashboard.php'); exit;
            }
        }
    }
    $err = 'Username/NIS atau password salah.';
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Login - Absensi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
          <h4 class="mb-3 fw-bold text-center text-primary">Login Sistem</h4>
          <p class="text-center text-muted mb-4">Silakan login untuk melanjutkan</p>
          
          <?php if($err): ?><div class="alert alert-danger"><?php echo esc($err); ?></div><?php endif; ?>
          
          <form method="post">
            <div class="mb-3">
                <label class="form-label">Username / NIS</label>
                <input name="username" class="form-control" placeholder="Masukan Username atau NIS" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input name="password" type="password" class="form-control" placeholder="Password" required>
            </div>
            <button class="btn btn-primary w-100 py-2 fw-bold">Masuk</button>
          </form>
          
          <div class="text-center mt-3">
              <small class="text-muted">Mahasiswa gunakan <b>NIS</b> sebagai Username & Password awal.</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body></html>