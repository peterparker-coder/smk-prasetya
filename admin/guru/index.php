<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$active_menu = 'guru';
$base_admin  = '../';

// Filter & Pencarian
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$kategori_filter = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

// Gunakan prepared statement untuk pengambilan data
if ($keyword !== '' && $kategori_filter !== '') {
    $param_kw = "%$keyword%";
    $stmt = mysqli_prepare($conn, "SELECT id, nama, jabatan, kategori, deskripsi, foto FROM guru WHERE (nama LIKE ? OR jabatan LIKE ? OR deskripsi LIKE ?) AND kategori = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "ssss", $param_kw, $param_kw, $param_kw, $kategori_filter);
} elseif ($keyword !== '') {
    $param_kw = "%$keyword%";
    $stmt = mysqli_prepare($conn, "SELECT id, nama, jabatan, kategori, deskripsi, foto FROM guru WHERE nama LIKE ? OR jabatan LIKE ? OR deskripsi LIKE ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "sss", $param_kw, $param_kw, $param_kw);
} elseif ($kategori_filter !== '') {
    $stmt = mysqli_prepare($conn, "SELECT id, nama, jabatan, kategori, deskripsi, foto FROM guru WHERE kategori = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "s", $kategori_filter);
} else {
    $stmt = mysqli_prepare($conn, "SELECT id, nama, jabatan, kategori, deskripsi, foto FROM guru ORDER BY id ASC");
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Hitung total guru dan staf menggunakan prepared statement
$stmt_stat = mysqli_prepare($conn, "SELECT 
    COUNT(*) as total_semua,
    SUM(CASE WHEN kategori = 'Guru' THEN 1 ELSE 0 END) as total_guru,
    SUM(CASE WHEN kategori = 'Tenaga Kependidikan' THEN 1 ELSE 0 END) as total_tendik
    FROM guru");
mysqli_stmt_execute($stmt_stat);
$res_stat = mysqli_stmt_get_result($stmt_stat);
$stat = mysqli_fetch_assoc($res_stat);
mysqli_stmt_close($stmt_stat);

$total_semua  = (int)($stat['total_semua'] ?? 0);
$total_guru   = (int)($stat['total_guru'] ?? 0);
$total_tendik = (int)($stat['total_tendik'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Guru & Tenaga Kependidikan - Admin SMKS Prasetya Makassar</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .guru-thumb {
            width: 54px;
            height: 54px;
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .stat-badge {
            font-size: 0.85rem;
            padding: 8px 14px;
            border-radius: 20px;
        }
    </style>
</head>
<body>

    <!-- Include Navbar Admin -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <div class="container my-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Guru & Tenaga Kependidikan</li>
            </ol>
        </nav>

        <!-- Feedback Alerts -->
        <?php if (isset($_GET['status'])) : ?>
            <?php if ($_GET['status'] == 'sukses_tambah') : ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Data guru / tenaga kependidikan berhasil ditambahkan!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_edit') : ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Data guru / tenaga kependidikan berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_hapus') : ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-trash-fill me-2"></i> Data guru / tenaga kependidikan berhasil dihapus!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'gagal') : ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Terjadi kesalahan saat memproses data!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Ringkasan Statistik Singkat -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="card card-custom bg-white p-3 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Personel</span>
                        <h3 class="fw-bold mb-0 text-primary mt-1"><?= $total_semua; ?></h3>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 fs-4">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-custom bg-white p-3 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tenaga Pendidik (Guru)</span>
                        <h3 class="fw-bold mb-0 text-success mt-1"><?= $total_guru; ?></h3>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success p-3 fs-4">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-custom bg-white p-3 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tenaga Kependidikan</span>
                        <h3 class="fw-bold mb-0 text-info mt-1"><?= $total_tendik; ?></h3>
                    </div>
                    <div class="rounded-circle bg-info-subtle text-info p-3 fs-4">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Kelola Data -->
        <div class="card card-custom bg-white p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-people text-primary me-2"></i>Daftar Guru & Tenaga Kependidikan
                    </h4>
                    <p class="text-muted small mb-0">Kelola informasi profil guru, jabatan, foto, dan deskripsi kependidikan</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="../../guru.php" target="_blank" class="btn btn-outline-secondary fw-medium">
                        <i class="bi bi-eye me-1"></i> Pratinjau Publik
                    </a>
                    <a href="tambah.php" class="btn btn-primary fw-medium">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Data Baru
                    </a>
                </div>
            </div>

            <!-- Form Pencarian & Filter Kategori -->
            <form action="index.php" method="GET" class="row g-2 mb-4 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari nama, jabatan, deskripsi..." value="<?= htmlspecialchars($keyword); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        <option value="Guru" <?= ($kategori_filter == 'Guru') ? 'selected' : ''; ?>>Guru / Tenaga Pendidik</option>
                        <option value="Tenaga Kependidikan" <?= ($kategori_filter == 'Tenaga Kependidikan') ? 'selected' : ''; ?>>Tenaga Kependidikan</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 fw-medium">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <?php if ($keyword !== '' || $kategori_filter !== '') : ?>
                        <a href="index.php" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Tabel Data -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 80px;">Foto</th>
                            <th>Nama Lengkap</th>
                            <th style="width: 220px;">Jabatan</th>
                            <th style="width: 170px;">Kategori</th>
                            <th>Deskripsi Singkat</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($result && mysqli_num_rows($result) > 0) :
                            while ($row = mysqli_fetch_assoc($result)) :
                                $foto_path = "../../assets/uploads/" . ($row['foto'] ?? '');
                                $foto_src = (!empty($row['foto']) && file_exists($foto_path))
                                    ? $foto_path
                                    : "https://placehold.co/100x100?text=" . urlencode(substr($row['nama'], 0, 2));
                        ?>
                            <tr>
                                <td class="text-center fw-semibold text-secondary"><?= $no++; ?></td>
                                <td>
                                    <img src="<?= htmlspecialchars($foto_src); ?>"
                                         alt="<?= htmlspecialchars($row['nama']); ?>"
                                         class="guru-thumb"
                                         onerror="this.src='https://placehold.co/100x100?text=Foto'">
                                </td>
                                <td>
                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($row['nama']); ?></span>
                                    <small class="text-muted">ID: #<?= (int)$row['id']; ?></small>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary"><?= htmlspecialchars($row['jabatan']); ?></span>
                                </td>
                                <td>
                                    <?php if (($row['kategori'] ?? '') === 'Tenaga Kependidikan') : ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 fw-semibold">
                                            <i class="bi bi-person-badge me-1"></i> Tendik
                                        </span>
                                    <?php else : ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">
                                            <i class="bi bi-mortarboard me-1"></i> Guru
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <p class="text-secondary small mb-0 text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($row['deskripsi'] ?? '-'); ?>">
                                        <?= htmlspecialchars($row['deskripsi'] ?? '-'); ?>
                                    </p>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="edit.php?id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Data">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= (int)$row['id']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data <?= htmlspecialchars(addslashes($row['nama'])); ?>? Tindakan ini tidak dapat dibatalkan.');"
                                           title="Hapus Data">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                            mysqli_stmt_close($stmt);
                        else :
                        ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 text-secondary d-block mb-2"></i>
                                    <p class="mb-2">Tidak ada data guru atau tenaga kependidikan yang ditemukan.</p>
                                    <a href="tambah.php" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Data Sekarang
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 text-muted small">
                <span>Menampilkan data tenaga pendidik & kependidikan aktif</span>
                <span>SMKS Prasetya Makassar</span>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
