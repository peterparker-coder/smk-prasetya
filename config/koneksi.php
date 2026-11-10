
<?php
// Konfigurasi Database SMKS Prasetya Makassar

$host = "localhost";
$user = "root";
$pass = "";
$db   = "profil_smks_prasetya_makassar";

// Koneksi database
$conn = mysqli_connect($host, $user, $pass, $db);

date_default_timezone_set('Asia/Makassar');

if (!$conn) {
    die(
        "<div style='font-family:sans-serif;padding:25px;background:#ffeef0;color:#842029;border:1px solid #f5c2c7;border-radius:8px;margin:30px auto;max-width:650px;'>
            <h3>Gagal Terhubung ke Database MySQL!</h3>
            <p>Pastikan Laragon sudah berjalan dan database tersedia.</p>
            <small><b>Detail Error:</b> " . htmlspecialchars(mysqli_connect_error()) . "</small>
        </div>"
    );
}

mysqli_set_charset($conn, "utf8mb4");
