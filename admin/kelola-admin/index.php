<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$active_menu = 'admin';
$base_admin  = '../';

// Ambil semua data akun admin
$query = mysqli_query($conn, "SELECT id, username, nama_lengkap FROM admin ORDER BY id ASC");
$total_admin = mysqli_num_rows($query);
$current_admin_id = $_SESSION['admin_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Admin - Admin SMKS Prasetya Makassar</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 15px;
        }
        .avatar-circle-current {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
        }
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
                <li class="breadcrumb-item active" aria-current="page">Manajemen Admin</li>
            </ol>
        </nav>

        <!-- Feedback Alerts -->
        <?php if (isset($_GET['status'])) : ?>
            <?php if ($_GET['status'] == 'sukses_tambah') : ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Akun admin baru berhasil ditambahkan!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_edit') : ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Data profil admin berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_password') : ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-shield-check me-2"></i> Password admin berhasil diubah!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_hapus') : ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-trash-fill me-2"></i> Akun admin berhasil dihapus!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['status'] == 'error_self_delete') : ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Aksi Ditolak:</strong> Anda tidak dapat menghapus akun admin yang sedang Anda gunakan saat ini!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['status'] == 'error_last_admin') : ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Aksi Ditolak:</strong> Tidak dapat menghapus admin terakhir. Minimal harus ada 1 akun admin di sistem!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['status'] == 'error_not_found') : ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-x-circle-fill me-2"></i> Data akun admin tidak ditemukan!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Ringkasan Singkat -->
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-4">
                <div class="card card-custom bg-white p-3 shadow-sm border-start border-primary border-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">TOTAL AKUN ADMIN</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= $total_admin; ?></h3>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-8">
                <div class="card card-custom bg-white p-3 shadow-sm border-start border-success border-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <span class="text-muted small fw-semibold">LOGIN SAAT INI</span>
                            <div class="fw-bold text-dark fs-6 mt-1">
                                <span class="badge bg-success-subtle text-success me-2"><i class="bi bi-person-check-fill me-1"></i>Aktif</span>
                                <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?> 
                                <small class="text-muted fw-normal">(@<?= htmlspecialchars($_SESSION['admin_username'] ?? 'admin'); ?>)</small>
                            </div>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-shield-lock me-1 text-success"></i> ID Akun: <strong>#<?= $current_admin_id; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Admin -->
        <div class="card card-custom bg-white p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-person-gear text-primary me-2"></i>Daftar Akun Administrator</h4>
                    <p class="text-muted small mb-0">Kelola kredensial dan hak akses administrator SMKS Prasetya Makassar</p>
                </div>
                <a href="tambah.php" class="btn btn-primary fw-medium shadow-sm">
                    <i class="bi bi-person-plus-fill me-1"></i> Tambah Admin Baru
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px;">No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Status Akun</th>
                            <th>Tanggal Terdaftar</th>
                            <th class="text-center" style="width: 260px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($total_admin > 0) :
                            while ($row = mysqli_fetch_assoc($query)) :
                                $is_current = ($row['id'] == $current_admin_id);
                                $initial = strtoupper(substr($row['nama_lengkap'], 0, 1));
                        ?>
                            <tr class="<?= $is_current ? 'table-light' : ''; ?>">
                                <td class="text-center fw-semibold text-secondary"><?= $no++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle <?= $is_current ? 'avatar-circle-current' : ''; ?>">
                                            <?= $initial; ?>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark d-block">
                                                <?= htmlspecialchars($row['nama_lengkap']); ?>
                                            </span>
                                            <small class="text-muted">ID: #<?= $row['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="fw-semibold fs-6 text-primary">@<?= htmlspecialchars($row['username']); ?></code>
                                </td>
                                <td>
                                    <?php if ($is_current) : ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-person-check-fill me-1"></i> Akun Anda (Aktif)
                                        </span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                            <i class="bi bi-shield-check me-1"></i> Administrator
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?php if (!empty($row['created_at'])) : ?>
                                        <i class="bi bi-calendar3 me-1"></i><?= date('d M Y, H:i', strtotime($row['created_at'])); ?>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1 flex-wrap">
                                        <!-- Edit Nama & Username -->
                                        <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Nama & Username">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </a>

                                        <!-- Ubah Password -->
                                        <a href="ubah-password.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-warning text-dark" title="Ubah Password">
                                            <i class="bi bi-key-fill me-1"></i> Password
                                        </a>

                                        <!-- Hapus Admin -->
                                        <?php if ($is_current) : ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Anda tidak dapat menghapus akun yang sedang Anda gunakan">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </button>
                                        <?php else : ?>
                                            <a href="hapus.php?id=<?= $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus akun admin \'<?= addslashes(htmlspecialchars($row['nama_lengkap'])); ?>\' (@<?= addslashes(htmlspecialchars($row['username'])); ?>)?\n\nAksi ini tidak dapat dibatalkan.');" 
                                               title="Hapus Akun">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else :
                        ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-people fs-1 text-secondary"></i>
                                    <p class="mt-2 mb-0">Belum ada akun admin yang terdaftar.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer Admin -->
    <footer class="bg-white border-top py-3 mt-5">
        <div class="container text-center text-muted small">
            &copy; 2026 SMKS Prasetya Makassar. Panel Manajemen Admin.
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

