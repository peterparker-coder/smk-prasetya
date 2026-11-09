<?php
// ==============================================================
// KONFIGURASI DATABASE
// SMKS Prasetya Makassar
// ==============================================================

$host = "localhost";
$user = "root";
$pass = "";
$db   = "profil_smks_prasetya_makassar";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:25px;background:#ffeef0;color:#842029;border:1px solid #f5c2c7;border-radius:8px;margin:30px auto;max-width:650px;box-shadow:0 4px 12px rgba(0,0,0,0.08);'>" .
        "<h3 style='margin-top:0;'>⚠️ Gagal Terhubung ke Database MySQL!</h3>" .
        "<p>Pastikan langkah berikut telah dilakukan:</p>" .
        "<ol style='line-height:1.6;'>" .
        "<li>Buka <b>Laragon</b> dan klik <b>Start All</b>.</li>" .
        "<li>Buka <b>HeidiSQL</b> dan buat database dengan nama: <code>profil_smks_prasetya_makassar</code></li>" .
        "<li>Jalankan file <code>database.sql</code> di HeidiSQL.</li>" .
        "</ol>" .
        "<hr style='border:0;border-top:1px solid #f5c2c7;margin:15px 0;'>" .
        "<small><b>Detail Error:</b> " . mysqli_connect_error() . "</small></div>");
}

mysqli_set_charset($conn, "utf8mb4");
?>
