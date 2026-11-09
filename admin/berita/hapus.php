<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/koneksi.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Ambil info gambar sebelum dihapus dari database
    $q_gambar = mysqli_query($conn, "SELECT gambar FROM berita WHERE id = '$id' LIMIT 1");
    if ($q_gambar && mysqli_num_rows($q_gambar) > 0) {
        $row = mysqli_fetch_assoc($q_gambar);
        $gambar = $row['gambar'];

        // Hapus file gambar fisik jika ada
        $file_path = "../../assets/uploads/" . $gambar;
        if (!empty($gambar) && file_exists($file_path)) {
            unlink($file_path);
        }

        // Hapus record dari database
        mysqli_query($conn, "DELETE FROM berita WHERE id = '$id'");
    }
}

header("Location: index.php?status=sukses_hapus");
exit;
?>
