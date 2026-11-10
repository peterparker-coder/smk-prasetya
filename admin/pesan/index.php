<?php

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$active_menu = 'pesan';
$base_admin  = '../';

// Proses tandai sudah dibaca jika ada parameter GET
if (isset($_GET['action']) && $_GET['action'] == 'read' && isset($_GET['id'])) {
    $read_id = intval($_GET['id']);

    mysqli_query($conn, "UPDATE pesan SET status='sudah dibaca' WHERE id='$read_id'");

    header("Location: index.php");
    exit;
}

// Proses tandai semua pesan sebagai sudah dibaca
if (isset($_GET['action']) && $_GET['action'] == 'read_all') {
    mysqli_query($conn, "UPDATE pesan SET status='sudah dibaca' WHERE status='belum dibaca'");

    header("Location: index.php?status=sukses_baca_semua");
    exit;
}

// Ambil semua pesan masuk dari yang terbaru
$query = mysqli_query($conn, "SELECT * FROM pesan ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Masuk - Admin SMKS Prasetya Makassar</title>

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

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .unread-row {
            background-color: #f0f7ff !important;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <!-- Include Navbar Admin -->
    <?php include '../navbar.php'; ?>

    <div class="container my-4">

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="../index.php" class="text-decoration-none">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    Pesan Masuk
                </li>
            </ol>
        </nav>

        <!-- Feedback Alert -->
        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_hapus') : ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-trash me-1"></i>
                Pesan pengunjung berhasil dihapus!

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'sukses_baca_semua') : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check2-all me-1"></i>
                Seluruh pesan berhasil ditandai sebagai sudah dibaca!

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card card-custom bg-white p-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-envelope text-primary me-2"></i>
                        Kotak Masuk Pesan Pengunjung
                    </h4>

                    <p class="text-muted small mb-0">
                        Daftar pertanyaan, aspirasi, dan pesan kontak dari pengunjung website profil sekolah
                    </p>
                </div>

                <?php if ($unread_count > 0) : ?>
                    <a href="index.php?action=read_all" 
                       class="btn btn-outline-success btn-sm fw-medium"
                       onclick="return confirm('Tandai seluruh pesan baru sebagai sudah dibaca?');">
                        <i class="bi bi-check2-all me-1"></i> Tandai Semua Sudah Dibaca
                    </a>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 140px;">Waktu</th>
                            <th style="width: 200px;">Pengirim</th>
                            <th>Subjek & Pesan</th>
                            <th style="width: 130px;">Status</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        $no = 1;

                        if (mysqli_num_rows($query) > 0) :
                            while ($row = mysqli_fetch_assoc($query)) :

                                $is_unread = ($row['status'] == 'belum dibaca');
                        ?>

                                <tr class="<?= $is_unread ? 'unread-row' : ''; ?>">

                                    <td class="text-center fw-semibold text-secondary">
                                        <?= $no++; ?>
                                    </td>

                                    <td class="small text-secondary text-nowrap">
                                        <i class="bi bi-clock me-1"></i>

                                        <?= !empty($row['tanggal_kirim'])
                                            ? date('d/m/Y', strtotime($row['tanggal_kirim']))
                                            : '-'; ?>

                                        <br>

                                        <small class="text-muted">
                                            <?= !empty($row['tanggal_kirim'])
                                                ? date('H:i', strtotime($row['tanggal_kirim'])) . ' WITA'
                                                : '-'; ?>
                                        </small>
                                    </td>

                                    <td>
                                        <span class="fw-bold text-dark d-block">
                                            <?= htmlspecialchars($row['nama']); ?>
                                        </span>

                                        <a href="mailto:<?= htmlspecialchars($row['email']); ?>"
                                            class="small text-muted text-decoration-none">

                                            <i class="bi bi-envelope-at me-1"></i>
                                            <?= htmlspecialchars($row['email']); ?>

                                        </a>
                                    </td>

                                    <td>
                                        <strong class="d-block text-dark">
                                            <?= htmlspecialchars($row['subjek']); ?>
                                        </strong>

                                        <p class="small text-muted mb-0 text-truncate" style="max-width: 380px;">
                                            <?= htmlspecialchars($row['pesan']); ?>
                                        </p>
                                    </td>

                                    <td>
                                        <?php if ($is_unread) : ?>

                                            <span class="badge bg-danger">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>
                                                Belum Dibaca
                                            </span>

                                        <?php else : ?>

                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                Sudah Dibaca
                                            </span>

                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">

                                        <div class="btn-group btn-group-sm">

                                            <!-- Tombol Modal Baca Pesan -->
                                            <button type="button"
                                                class="btn btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalPesan<?= $row['id']; ?>"
                                                title="Baca Isi Lengkap">

                                                <i class="bi bi-eye"></i>

                                            </button>

                                            <?php if ($is_unread) : ?>

                                                <a href="index.php?action=read&id=<?= $row['id']; ?>"
                                                    class="btn btn-outline-success"
                                                    title="Tandai Sudah Dibaca">

                                                    <i class="bi bi-check2"></i>

                                                </a>

                                            <?php endif; ?>

                                            <a href="hapus.php?id=<?= $row['id']; ?>"
                                                class="btn btn-outline-danger"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus pesan ini?')"
                                                title="Hapus Pesan">

                                                <i class="bi bi-trash"></i>

                                            </a>

                                        </div>

                                        <!-- Modal Detail Pesan -->
                                        <div class="modal fade"
                                            id="modalPesan<?= $row['id']; ?>"
                                            tabindex="-1"
                                            aria-labelledby="modalLabel<?= $row['id']; ?>"
                                            aria-hidden="true">

                                            <div class="modal-dialog modal-dialog-centered">

                                                <div class="modal-content text-start">

                                                    <div class="modal-header bg-light">

                                                        <h5 class="modal-title fs-6 fw-bold"
                                                            id="modalLabel<?= $row['id']; ?>">

                                                            <i class="bi bi-envelope-open text-primary me-2"></i>
                                                            Rincian Pesan Pengunjung

                                                        </h5>

                                                        <button type="button"
                                                            class="btn-close"
                                                            data-bs-dismiss="modal">
                                                        </button>

                                                    </div>

                                                    <div class="modal-body p-4">

                                                        <div class="mb-3">
                                                            <label class="form-label text-muted small mb-0">
                                                                Nama Pengirim:
                                                            </label>

                                                            <div class="fw-bold fs-6">
                                                                <?= htmlspecialchars($row['nama']); ?>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label text-muted small mb-0">
                                                                Email:
                                                            </label>

                                                            <div>
                                                                <a href="mailto:<?= htmlspecialchars($row['email']); ?>">
                                                                    <?= htmlspecialchars($row['email']); ?>
                                                                </a>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label text-muted small mb-0">
                                                                Waktu Kirim:
                                                            </label>

                                                            <div class="small">
                                                                <?= !empty($row['tanggal_kirim'])
                                                                    ? date('l, d F Y - H:i', strtotime($row['tanggal_kirim'])) . ' WITA'
                                                                    : '-'; ?>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label text-muted small mb-0">
                                                                Subjek:
                                                            </label>

                                                            <div class="fw-semibold text-primary">
                                                                <?= htmlspecialchars($row['subjek']); ?>
                                                            </div>
                                                        </div>

                                                        <div class="mb-0">
                                                            <label class="form-label text-muted small mb-1">
                                                                Isi Pesan:
                                                            </label>

                                                            <div class="p-3 bg-light rounded border text-secondary"
                                                                style="white-space: pre-wrap; line-height: 1.6;">

                                                                <?= htmlspecialchars($row['pesan']); ?>

                                                            </div>
                                                        </div>

                                                    </div>

                                                    <div class="modal-footer bg-light py-2">

                                                        <?php if ($is_unread) : ?>

                                                            <a href="index.php?action=read&id=<?= $row['id']; ?>"
                                                                class="btn btn-sm btn-success">

                                                                <i class="bi bi-check2 me-1"></i>
                                                                Tandai Sudah Dibaca

                                                            </a>

                                                        <?php endif; ?>

                                                        <a href="mailto:<?= htmlspecialchars($row['email']); ?>?subject=Re: <?= urlencode($row['subjek']); ?>"
                                                            class="btn btn-sm btn-primary">

                                                            <i class="bi bi-reply-fill me-1"></i>
                                                            Balas Email

                                                        </a>

                                                        <button type="button"
                                                            class="btn btn-sm btn-secondary"
                                                            data-bs-dismiss="modal">

                                                            Tutup

                                                        </button>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>
                                        <!-- End Modal -->

                                    </td>

                                </tr>

                        <?php
                            endwhile;
                        else :
                        ?>

                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">

                                    <i class="bi bi-inbox fs-2 text-secondary d-block mb-2"></i>

                                    Belum ada pesan masuk dari pengunjung.

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