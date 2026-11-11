<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 1. Ambil nama file foto sebelum record dihapus menggunakan prepared statement
    $stmt_select = mysqli_prepare($conn, "SELECT foto FROM alumni WHERE id = ? LIMIT 1");
    if ($stmt_select) {
        mysqli_stmt_bind_param($stmt_select, "i", $id);
        mysqli_stmt_execute($stmt_select);
        $res = mysqli_stmt_get_result($stmt_select);
        if ($row = mysqli_fetch_assoc($res)) {
            $foto = $row['foto'];
            $file_path = __DIR__ . '/../../assets/uploads/' . $foto;
            if (!empty($foto) && file_exists($file_path)) {
                @unlink($file_path);
            }
        }
        mysqli_stmt_close($stmt_select);
    }

    // 2. Hapus data dari tabel alumni menggunakan prepared statement
    $stmt_delete = mysqli_prepare($conn, "DELETE FROM alumni WHERE id = ?");
    if ($stmt_delete) {
        mysqli_stmt_bind_param($stmt_delete, "i", $id);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
    }
}

header("Location: index.php?status=sukses_hapus");
exit;

