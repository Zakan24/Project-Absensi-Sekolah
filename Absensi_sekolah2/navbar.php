<?php if(!isset($_SESSION)) session_start(); ?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background: linear-gradient(90deg, #4361EE, #3F37C9);">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php">
        <i class="ph ph-graduation-cap me-1"></i> AbsensiSekolah
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="absensi.php">Input Absensi</a></li>
        <li class="nav-item"><a class="nav-link" href="siswa.php">Data Siswa</a></li>
        <li class="nav-item"><a class="nav-link" href="rekap.php">Laporan</a></li>
      </ul>
      
      <?php if(isset($_SESSION['user'])): ?>
        <div class="d-flex align-items-center text-white">
          <div class="me-3 d-none d-lg-block text-end" style="line-height:1.2">
             <small class="d-block text-white-50">Login sebagai</small>
             <span class="fw-bold"><?php echo esc($_SESSION['user']['nama_lengkap']); ?></span>
          </div>
          <a href="logout.php" class="btn btn-light text-primary btn-sm fw-bold rounded-pill px-3">Logout</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>
<div class="mb-3"></div>