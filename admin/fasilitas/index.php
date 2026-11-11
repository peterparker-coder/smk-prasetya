<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$active_menu = 'fasilitas';
$base_admin  = '../';

// Periksa struktur tabel sebelum menggunakan nama kolom (Ketentuan 13)
$col_nama = 'nama_fasilitas';
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM fasilitas LIKE 'nama_fasilitas'");
if (!$check_col || mysqli_num_rows($check_col) == 0) {
    $col_nama = 'nama';
}

// Filter & Pencarian
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$kategori_filter = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

// Gunakan prepared statement untuk pengambilan data
if ($keyword !== '' && $kategori_filter !== '') {
    $param_kw = "%$keyword%";
    $stmt = mysqli_prepare($conn, "SELECT id, $col_nama AS nama_fasilitas, kategori, deskripsi, foto FROM fasilitas WHERE ($col_nama LIKE ? OR deskripsi LIKE ?) AND kategori = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "sss", $param_kw, $param_kw, $kategori_filter);
} elseif ($keyword !== '') {
    $param_kw = "%$keyword%";
    $stmt = mysqli_prepare($conn, "SELECT id, $col_nama AS nama_fasilitas, kategori, deskripsi, foto FROM fasilitas WHERE $col_nama LIKE ? OR deskripsi LIKE ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "ss", $param_kw, $param_kw);
} elseif ($kategori_filter !== '') {
    $stmt = mysqli_prepare($conn, "SELECT id, $col_nama AS nama_fasilitas, kategori, deskripsi, foto FROM fasilitas WHERE kategori = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "s", $kategori_filter);
} else {
    $stmt = mysqli_prepare($conn, "SELECT id, $col_nama AS nama_fasilitas, kategori, deskripsi, foto FROM fasilitas ORDER BY id ASC");
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Hitung total statistik fasilitas via prepared statement
$stmt_stat = mysqli_prepare($conn, "SELECT 
    COUNT(*) as total_semua,
    SUM(CASE WHEN kategori LIKE '%Laboratorium%' OR kategori LIKE '%Praktik%' THEN 1 ELSE 0 END) as total_lab,
    SUM(CASE WHEN kategori LIKE '%Pembelajaran%' OR kategori LIKE '%Kelas%' THEN 1 ELSE 0 END) as total_belajar,
    SUM(CASE WHEN kategori LIKE '%Olahraga%' THEN 1 ELSE 0 END) as total_olahraga,
    SUM(CASE WHEN kategori LIKE '%Penunjang%' OR kategori LIKE '%Umum%' THEN 1 ELSE 0 END) as total_penunjang
    FROM fasilitas");
mysqli_stmt_execute($stmt_stat);
$res_stat = mysqli_stmt_get_result($stmt_stat);
$stat = mysqli_fetch_assoc($res_stat);
mysqli_stmt_close($stmt_stat);

$total_semua     = (int)($stat['total_semua'] ?? 0);
$total_lab       = (int)($stat['total_lab'] ?? 0);
$total_belajar   = (int)($stat['total_belajar'] ?? 0);
$total_olahraga  = (int)($stat['total_olahraga'] ?? 0);
$total_penunjang = (int)($stat['total_penunjang'] ?? 0);

// Ambil semua daftar kategori unik untuk dropdown filter
$kat_list = [];
$res_kat = mysqli_query($conn, "SELECT DISTINCT kategori FROM fasilitas WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
if ($res_kat) {
    while ($rk = mysqli_fetch_assoc($res_kat)) {
        $kat_list[] = $rk['kategori'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Fasilitas Sekolah - Admin SMKS Prasetya Makassar</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .fasilitas-thumb {
            width: 80px;
            height: 56px;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
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
                <li class="breadcrumb-item active" aria-current="page">Fasilitas Sekolah</li>
            </ol>
        </nav>

        <!-- Feedback Alerts -->
        <?php if (isset($_GET['status'])) : ?>
            <?php if ($_GET['status'] == 'sukses_tambah') : ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Fasilitas baru berhasil ditambahkan!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_edit') : ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Data fasilitas berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_hapus') : ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-trash-fill me-2"></i> Fasilitas beserta foto berhasil dihapus!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'gagal') : ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Terjadi kesalahan saat memproses data fasilitas!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Kartu Ringkasan Statistik Singkat -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-md-3">
                <div class="card card-custom bg-white p-3 d-flex flex-row align-items-center justify-content-between h-100">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Fasilitas</span>
                        <h3 class="fw-bold mb-0 text-primary mt-1"><?= $total_semua; ?></h3>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 fs-4">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-custom bg-white p-3 d-flex flex-row align-items-center justify-content-between h-100">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Lab & Praktik</span>
                        <h3 class="fw-bold mb-0 text-danger mt-1"><?= $total_lab; ?></h3>
                    </div>
                    <div class="rounded-circle bg-danger-subtle text-danger p-3 fs-4">
                        <i class="bi bi-laptop"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-custom bg-white p-3 d-flex flex-row align-items-center justify-content-between h-100">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Ruang Belajar</span>
                        <h3 class="fw-bold mb-0 text-success mt-1"><?= $total_belajar; ?></h3>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success p-3 fs-4">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-custom bg-white p-3 d-flex flex-row align-items-center justify-content-between h-100">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Olahraga & Penunjang</span>
                        <h3 class="fw-bold mb-0 text-info mt-1"><?= ($total_olahraga + $total_penunjang); ?></h3>
                    </div>
                    <div class="rounded-circle bg-info-subtle text-info p-3 fs-4">
                        <i class="bi bi-dribbble"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Kelola Data -->
        <div class="card card-custom bg-white p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-building-gear text-primary me-2"></i>Daftar Fasilitas Sekolah
                    </h4>
                    <p class="text-muted small mb-0">Kelola sarana dan prasarana penunjang kegiatan belajar mengajar SMKS Prasetya</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="../../fasilitas.php" target="_blank" class="btn btn-outline-secondary fw-medium">
                        <i class="bi bi-eye me-1"></i> Pratinjau Publik
                    </a>
                    <a href="tambah.php" class="btn btn-primary fw-medium">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Fasilitas
                    </a>
                </div>
            </div>

            <!-- Form Pencarian & Filter Kategori -->
            <form action="index.php" method="GET" class="row g-2 mb-4 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari nama fasilitas atau deskripsi..." value="<?= htmlspecialchars($keyword); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($kat_list as $k_item) : ?>
                            <option value="<?= htmlspecialchars($k_item); ?>" <?= ($kategori_filter === $k_item) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($k_item); ?>
                            </option>
                        <?php endforeach; ?>
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

            <!-- Tabel Data Fasilitas -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 100px;">Foto</th>
                            <th>Nama Fasilitas</th>
                            <th style="width: 200px;">Kategori</th>
                            <th>Deskripsi Fasilitas</th>
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
                                    : "https://placehold.co/120x80?text=No+Foto";
                        ?>
                            <tr>
                                <td class="text-center fw-semibold text-secondary"><?= $no++; ?></td>
                                <td>
                                    <img src="<?= htmlspecialchars($foto_src); ?>"
                                         alt="<?= htmlspecialchars($row['nama_fasilitas']); ?>"
                                         class="fasilitas-thumb"
                                         onerror="this.src='https://placehold.co/120x80?text=Fasilitas'">
                                </td>
                                <td>
                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($row['nama_fasilitas']); ?></span>
                                    <small class="text-muted">ID: #<?= (int)$row['id']; ?></small>
                                </td>
                                <td>
                                    <?php
                                    $badge_class = 'bg-primary-subtle text-primary border-primary-subtle';
                                    $kat_lower = strtolower($row['kategori'] ?? '');
                                    if (strpos($kat_lower, 'lab') !== false || strpos($kat_lower, 'praktik') !== false) {
                                        $badge_class = 'bg-danger-subtle text-danger border-danger-subtle';
                                    } elseif (strpos($kat_lower, 'belajar') !== false || strpos($kat_lower, 'kelas') !== false || strpos($kat_lower, 'pustaka') !== false) {
                                        $badge_class = 'bg-success-subtle text-success border-success-subtle';
                                    } elseif (strpos($kat_lower, 'olahraga') !== false) {
                                        $badge_class = 'bg-warning-subtle text-warning border-warning-subtle';
                                    } elseif (strpos($kat_lower, 'penunjang') !== false || strpos($kat_lower, 'ibadah') !== false) {
                                        $badge_class = 'bg-info-subtle text-info border-info-subtle';
                                    }
                                    ?>
                                    <span class="badge <?= $badge_class; ?> border rounded-pill px-3 py-1 fw-semibold">
                                        <?= htmlspecialchars($row['kategori'] ?? 'Umum'); ?>
                                    </span>
                                </td>
                                <td>
                                    <p class="text-secondary small mb-0 text-truncate" style="max-width: 320px;" title="<?= htmlspecialchars($row['deskripsi'] ?? '-'); ?>">
                                        <?= htmlspecialchars($row['deskripsi'] ?? '-'); ?>
                                    </p>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="edit.php?id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Fasilitas">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= (int)$row['id']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus fasilitas <?= htmlspecialchars(addslashes($row['nama_fasilitas'])); ?>? Foto terkait juga akan dihapus.');"
                                           title="Hapus Fasilitas">
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
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-building fs-1 text-secondary d-block mb-2"></i>
                                    <p class="mb-2">Tidak ada data fasilitas yang ditemukan.</p>
                                    <a href="tambah.php" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Fasilitas Baru
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 text-muted small">
                <span>Menampilkan fasilitas dan sarana prasarana sekolah</span>
                <span>SMKS Prasetya Makassar</span>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

