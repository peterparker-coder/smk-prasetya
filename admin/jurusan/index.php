<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$active_menu = 'jurusan';
$base_admin  = '../';

// Ambil semua data jurusan
$query = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jurusan - Admin SMKS Prasetya Makassar</title>
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
        <!-- Breadcrumb & Header -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kelola Jurusan</li>
            </ol>
        </nav>

        <!-- Feedback Alert -->
        <?php if (isset($_GET['status'])) : ?>
            <?php if ($_GET['status'] == 'sukses_tambah') : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> Data jurusan baru berhasil ditambahkan!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_edit') : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> Data jurusan berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_hapus') : ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-trash me-1"></i> Data jurusan berhasil dihapus!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card card-custom bg-white p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-briefcase text-primary me-2"></i>Daftar Program Keahlian / Jurusan</h4>
                    <p class="text-muted small mb-0">Kelola informasi program keahlian yang tersedia di SMKS Prasetya Makassar</p>
                </div>
                <a href="tambah.php" class="btn btn-primary fw-medium">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Jurusan Baru
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px;">No</th>
                            <th style="width: 120px;">Singkatan</th>
                            <th>Nama Jurusan</th>
                            <th>Deskripsi Ringkas</th>
                            <th class="text-center" style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query) > 0) :
                            while ($row = mysqli_fetch_assoc($query)) :
                        ?>
                            <tr>
                                <td class="text-center fw-semibold text-secondary"><?= $no++; ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1">
                                        <?= htmlspecialchars($row['singkatan'] ?? 'SMK'); ?>
                                    </span>
                                </td>
                                <td class="fw-semibold text-dark">
                                    <?= htmlspecialchars($row['nama_jurusan']); ?>
                                </td>
                                <td class="text-secondary small">
                                    <?= nl2br(htmlspecialchars($row['deskripsi'])); ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-outline-warning" title="Edit Jurusan">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="hapus.php?id=<?= $row['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus jurusan \'<?= htmlspecialchars(addslashes($row['nama_jurusan'])); ?>\'? Data yang dihapus tidak bisa dikembalikan.')" title="Hapus Jurusan">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else :
                        ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 text-secondary d-block mb-2"></i>
                                    Belum ada data jurusan. Silakan klik tombol "Tambah Jurusan Baru".
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
