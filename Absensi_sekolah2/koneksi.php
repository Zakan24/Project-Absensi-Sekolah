<?php
// koneksi.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'db_absensi2';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die('Koneksi database gagal: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

session_start();

function is_logged_in(){ return isset($_SESSION['user']); }
function require_login(){ if(!is_logged_in()){ header('Location: login.php'); exit; } }
function esc($s){ return htmlspecialchars($s, ENT_QUOTES); }
?>
