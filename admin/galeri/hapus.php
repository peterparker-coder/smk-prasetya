
<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data foto
$stmt = mysqli_prepare($conn, "SELECT nama_file FROM galeri WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: index.php");
    exit;
}

// Hapus data dari database
$stmt = mysqli_prepare($conn, "DELETE FROM galeri WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    // Hapus file foto dari folder uploads
    if (!empty($data['nama_file'])) {
        $nama_file = basename($data['nama_file']);
        $lokasi_file = '../../assets/uploads/' . $nama_file;

        if (is_file($lokasi_file)) {
            unlink($lokasi_file);
        }
    }

    header("Location: index.php?status=sukses_hapus");
    exit;
}

mysqli_stmt_close($stmt);

header("Location: index.php?status=gagal_hapus");
exit;
?>
