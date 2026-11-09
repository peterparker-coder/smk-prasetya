<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/koneksi.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    mysqli_query($conn, "DELETE FROM pesan WHERE id = '$id'");
}

header("Location: index.php?status=sukses_hapus");
exit;
?>
