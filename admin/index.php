<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

require_once '../config/koneksi.php';

$active_menu = 'dashboard';
$base_admin  = '';

// Query Statistik
$total_jurusan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM jurusan"))['total'] ?? 0;
$total_berita  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM berita"))['total'] ?? 0;
$total_pesan   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesan"))['total'] ?? 0;
$pesan_baru    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesan WHERE status='belum dibaca'"))['total'] ?? 0;

// Berita Terbaru
$q_recent_berita = mysqli_query($conn, "SELECT * FROM berita ORDER BY id DESC LIMIT 5");

// Pesan Terbaru
$q_recent_pesan  = mysqli_query($conn, "SELECT * FROM pesan ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SMKS Prasetya Makassar</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }
    </style>
</head>
<body>

    <!-- Include Navbar Admin -->
    <?php include 'navbar.php'; ?>

    <div class="container my-4">
        <!-- Banner Selamat Datang -->
        <div class="card card-custom bg-primary text-white p-4 mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h3 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?>! 👋</h3>
                    <p class="mb-0 text-white-50">Panel Manajemen Website Profil SMKS Prasetya Makassar. Kelola jurusan, berita, dan pesan pengunjung dari satu tempat.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="berita/tambah.php" class="btn btn-warning fw-semibold shadow-sm text-dark">
                        <i class="bi bi-plus-circle me-1"></i> Buat Berita
                    </a>
                    <a href="jurusan/tambah.php" class="btn btn-light fw-semibold shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Jurusan
                    </a>
                </div>
            </div>
        </div>

        <!-- Kartu Statistik -->
        <div class="row g-3 mb-4">
            <!-- Stat Jurusan -->
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card bg-white p-3 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">PROGRAM JURUSAN</span>
                            <h2 class="fw-bold mb-0 text-dark"><?= $total_jurusan; ?></h2>
                        </div>
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <a href="jurusan/index.php" class="small text-decoration-none text-primary fw-medium">
                            Kelola Jurusan &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stat Berita -->
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card bg-white p-3 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">TOTAL BERITA</span>
                            <h2 class="fw-bold mb-0 text-dark"><?= $total_berita; ?></h2>
                        </div>
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="bi bi-newspaper"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <a href="berita/index.php" class="small text-decoration-none text-success fw-medium">
                            Kelola Berita &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stat Pesan Total -->
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card bg-white p-3 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">PESAN MASUK</span>
                            <h2 class="fw-bold mb-0 text-dark"><?= $total_pesan; ?></h2>
                        </div>
                        <div class="stat-icon bg-info-subtle text-info">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <a href="pesan/index.php" class="small text-decoration-none text-info fw-medium">
                            Lihat Kotak Masuk &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stat Pesan Belum Dibaca -->
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card bg-white p-3 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">PESAN BELUM DIBACA</span>
                            <h2 class="fw-bold mb-0 text-danger"><?= $pesan_baru; ?></h2>
                        </div>
                        <div class="stat-icon bg-danger-subtle text-danger">
                            <i class="bi bi-chat-left-dots-fill"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <a href="pesan/index.php" class="small text-decoration-none text-danger fw-medium">
                            Baca Pesan Baru &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Ringkasan Aktivitas -->
        <div class="row g-4">
            <!-- Berita Terkini -->
            <div class="col-lg-6">
                <div class="card card-custom bg-white p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-newspaper me-2 text-primary"></i>Berita Terkini</h5>
                        <a href="berita/index.php" class="btn btn-sm btn-outline-primary">Semua Berita</a>
                    </div>

                    <?php if (mysqli_num_rows($q_recent_berita) > 0) : ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Gambar</th>
                                        <th>Judul Berita</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($b = mysqli_fetch_assoc($q_recent_berita)) : ?>
                                        <tr>
                                            <td style="width: 60px;">
                                                <img src="../assets/uploads/<?= htmlspecialchars($b['gambar']); ?>" alt="thumbnail" class="rounded object-fit-cover" width="50" height="40" onerror="this.src='https://placehold.co/100x80?text=No+Img'">
                                            </td>
                                            <td>
                                                <span class="fw-medium text-dark d-block text-truncate" style="max-width: 250px;">
                                                    <?= htmlspecialchars($b['judul']); ?>
                                                </span>
                                                <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($b['penulis']); ?></small>
                                            </td>
                                            <td class="small text-muted text-nowrap">
                                                <?= date('d M Y', strtotime($b['tanggal'])); ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-file-earmark-x fs-1 text-secondary"></i>
                            <p class="mt-2 mb-0">Belum ada berita yang diterbitkan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pesan Pengunjung Terkini -->
            <div class="col-lg-6">
                <div class="card card-custom bg-white p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-envelope me-2 text-info"></i>Pesan Pengunjung Terbaru</h5>
                        <a href="pesan/index.php" class="btn btn-sm btn-outline-info">Semua Pesan</a>
                    </div>

                    <?php if (mysqli_num_rows($q_recent_pesan) > 0) : ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Pengirim</th>
                                        <th>Subjek</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($p = mysqli_fetch_assoc($q_recent_pesan)) : ?>
                                        <tr>
                                            <td>
                                                <span class="fw-medium text-dark d-block"><?= htmlspecialchars($p['nama']); ?></span>
                                                <small class="text-muted"><?= htmlspecialchars($p['email']); ?></small>
                                            </td>
                                            <td>
                                                <span class="d-block text-truncate text-secondary" style="max-width: 200px;">
                                                    <?= htmlspecialchars($p['subjek']); ?>
                                                </span>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($p['tanggal_kirim'] ?? 'now')); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($p['status'] == 'belum dibaca') : ?>
                                                    <span class="badge bg-danger">Baru</span>
                                                <?php else : ?>
                                                    <span class="badge bg-secondary">Dibaca</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 text-secondary"></i>
                            <p class="mt-2 mb-0">Belum ada pesan masuk dari pengunjung.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Admin -->
    <footer class="bg-white border-top py-3 mt-5">
        <div class="container text-center text-muted small">
            &copy; 2026 SMKS Prasetya Makassar. Sistem Profil Sekolah PHP Native & Bootstrap 5.
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
