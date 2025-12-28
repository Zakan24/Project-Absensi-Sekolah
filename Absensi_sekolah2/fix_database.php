<?php
require 'koneksi.php';

echo "<h3>🛠️ PERBAIKAN DATABASE OTOMATIS</h3>";

// 1. Cek & Tambah Kolom 'id_guru' di tabel 'absensi'
echo "Memeriksa tabel 'absensi'...<br>";
$check = $mysqli->query("SHOW COLUMNS FROM absensi LIKE 'id_guru'");
if($check->num_rows == 0){
    // Jika kolom belum ada, buat kolomnya
    $sql = "ALTER TABLE absensi ADD COLUMN id_guru INT DEFAULT NULL";
    if($mysqli->query($sql)){
        echo "<h4 style='color:green'>✅ Sukses: Kolom 'id_guru' berhasil ditambahkan!</h4>";
        echo "Sekarang sistem bisa menyimpan data guru pengabsen.";
    } else {
        echo "<h4 style='color:red'>❌ Gagal: " . $mysqli->error . "</h4>";
    }
} else {
    echo "<h4 style='color:blue'>ℹ️ Kolom 'id_guru' sudah ada. Tidak perlu perbaikan.</h4>";
}

echo "<hr>";
echo "<a href='absensi.php' style='padding:10px 20px; background:blue; color:white; text-decoration:none; border-radius:5px;'>Kembali ke Input Absensi</a>";
?>