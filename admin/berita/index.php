<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/koneksi.php';

$active_menu = 'berita';
$base_admin  = '../';

// Ambil semua data berita
$query = mysqli_query($conn, "SELECT * FROM berita ORDER BY tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita - Admin SMKS Prasetya Makassar</title>
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
                <li class="breadcrumb-item active" aria-current="page">Kelola Berita</li>
            </ol>
        </nav>

        <!-- Feedback Alert -->
        <?php if (isset($_GET['status'])) : ?>
            <?php if ($_GET['status'] == 'sukses_tambah') : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> Berita baru berhasil dipublikasikan!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_edit') : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> Berita berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_hapus') : ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-trash me-1"></i> Berita beserta gambar berhasil dihapus!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card card-custom bg-white p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-newspaper text-primary me-2"></i>Daftar Berita & Artikel</h4>
                    <p class="text-muted small mb-0">Kelola publikasi kegiatan, prestasi, dan pengumuman sekolah</p>
                </div>
                <a href="tambah.php" class="btn btn-primary fw-medium">
                    <i class="bi bi-plus-lg me-1"></i> Tulis Berita Baru
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 100px;">Gambar</th>
                            <th>Judul Berita</th>
                            <th style="width: 140px;">Penulis</th>
                            <th style="width: 130px;">Tanggal</th>
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
                                    <img src="../../assets/uploads/<?= htmlspecialchars($row['gambar']); ?>" alt="Gambar Berita" class="rounded object-fit-cover shadow-sm" width="80" height="55" onerror="this.src='https://placehold.co/100x80?text=No+Img'">
                                </td>
                                <td>
                                    <span class="fw-bold text-dark d-block">
                                        <?= htmlspecialchars($row['judul']); ?>
                                    </span>
                                    <small class="text-muted text-truncate d-block" style="max-width: 400px;">
                                        <?= strip_tags($row['isi']); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-person me-1"></i><?= htmlspecialchars($row['penulis']); ?>
                                    </span>
                                </td>
                                <td class="small text-secondary text-nowrap">
                                    <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($row['tanggal'])); ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="../../detail-berita.php?id=<?= $row['id']; ?>" target="_blank" class="btn btn-outline-info" title="Lihat di Web">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-outline-warning" title="Edit Berita">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= $row['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus berita ini? Gambar juga akan dihapus dari server.')" title="Hapus Berita">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else :
                        ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-journal-x fs-2 text-secondary d-block mb-2"></i>
                                    Belum ada berita yang dipublikasikan. Silakan klik tombol "Tulis Berita Baru".
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
