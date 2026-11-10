<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php?status=error_not_found");
    exit;
}

$current_admin_id = intval($_SESSION['admin_id'] ?? 0);

// 1. Mencegah admin menghapus akun yang sedang aktif digunakan
if ($id === $current_admin_id) {
    header("Location: index.php?status=error_self_delete");
    exit;
}

// 2. Mencegah penghapusan jika hanya tersisa 1 admin di sistem
$count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM admin");
$count_data = mysqli_fetch_assoc($count_res);
if (($count_data['total'] ?? 0) <= 1) {
    header("Location: index.php?status=error_last_admin");
    exit;
}

// 3. Pastikan akun admin yang dituju ada di database
$stmt_check = mysqli_prepare($conn, "SELECT id FROM admin WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_check, "i", $id);
mysqli_stmt_execute($stmt_check);
$res_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($res_check) === 0) {
    mysqli_stmt_close($stmt_check);
    header("Location: index.php?status=error_not_found");
    exit;
}
mysqli_stmt_close($stmt_check);

// 4. Lakukan penghapusan akun admin dengan prepared statement
$stmt_del = mysqli_prepare($conn, "DELETE FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt_del, "i", $id);
mysqli_stmt_execute($stmt_del);
mysqli_stmt_close($stmt_del);

header("Location: index.php?status=sukses_hapus");
exit;

