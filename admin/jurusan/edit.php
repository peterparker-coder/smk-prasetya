<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/koneksi.php';

$active_menu = 'jurusan';
$base_admin  = '../';
$error = '';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query_data = mysqli_query($conn, "SELECT * FROM jurusan WHERE id = '$id' LIMIT 1");

if (mysqli_num_rows($query_data) == 0) {
    header("Location: index.php");
    exit;
}

$row = mysqli_fetch_assoc($query_data);

if (isset($_POST['update'])) {
    $nama_jurusan = trim(mysqli_real_escape_string($conn, $_POST['nama_jurusan']));
    $singkatan    = trim(mysqli_real_escape_string($conn, $_POST['singkatan']));
    $deskripsi    = trim(mysqli_real_escape_string($conn, $_POST['deskripsi']));

    if (empty($nama_jurusan) || empty($deskripsi)) {
        $error = "Nama Jurusan dan Deskripsi wajib diisi!";
    } else {
        $update_query = mysqli_query($conn, "UPDATE jurusan SET nama_jurusan='$nama_jurusan', singkatan='$singkatan', deskripsi='$deskripsi' WHERE id='$id'");

        if ($update_query) {
            header("Location: index.php?status=sukses_edit");
            exit;
        } else {
            $error = "Gagal memperbarui data jurusan: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jurusan - Admin SMKS Prasetya Makassar</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
    </style>
</head>
<body>

    <!-- Include Navbar Admin -->
    <?php include '../navbar.php'; ?>

    <div class="container my-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Kelola Jurusan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Jurusan</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-custom bg-white p-4">
                    <div class="border-bottom pb-3 mb-4">
                        <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Jurusan</h4>
                        <p class="text-muted small mb-0">Perbarui rincian program keahlian SMKS Prasetya Makassar</p>
                    </div>

                    <?php if (!empty($error)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i> <?= $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama Jurusan <span class="text-danger">*</span></label>
                                <input type="text" name="nama_jurusan" class="form-control" value="<?= htmlspecialchars($row['nama_jurusan']); ?>" required>
                            </div>
                            <div class="col-md-4 mt-3 mt-md-0">
                                <label class="form-label fw-semibold">Singkatan / Kode</label>
                                <input type="text" name="singkatan" class="form-control" value="<?= htmlspecialchars($row['singkatan'] ?? ''); ?>" placeholder="Contoh: TKJ">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Deskripsi Jurusan <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" class="form-control" rows="5" required><?= htmlspecialchars($row['deskripsi']); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" name="update" class="btn btn-warning fw-semibold px-4 text-dark">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
