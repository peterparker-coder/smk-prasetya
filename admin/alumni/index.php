<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$active_menu = 'alumni';
$base_admin  = '../';

// Periksa nama kolom database sebelum menggunakannya (Ketentuan)
$cols_res = mysqli_query($conn, "SHOW COLUMNS FROM alumni");
$alumni_cols = [];
while ($c = mysqli_fetch_assoc($cols_res)) {
    $alumni_cols[] = $c['Field'];
}

$col_pekerjaan = in_array('pekerjaan', $alumni_cols) ? 'pekerjaan' : 'aktivitas';
$col_jurusan   = in_array('jurusan', $alumni_cols) ? 'jurusan' : 'nama_jurusan';
$col_tahun     = in_array('tahun_lulus', $alumni_cols) ? 'tahun_lulus' : 'tahun';
$has_testimoni = in_array('testimoni', $alumni_cols);

// Filter & Pencarian
$keyword        = isset($_GET['q']) ? trim($_GET['q']) : '';
$jurusan_filter = isset($_GET['jurusan']) ? trim($_GET['jurusan']) : '';
$tahun_filter   = isset($_GET['tahun']) ? trim($_GET['tahun']) : '';

// Bangun query prepared statement
$select_fields = "id, nama, $col_tahun AS tahun_lulus, $col_jurusan AS jurusan, $col_pekerjaan AS pekerjaan, foto" . ($has_testimoni ? ", testimoni" : "");

if ($keyword !== '' && $jurusan_filter !== '' && $tahun_filter !== '') {
    $param_kw = "%$keyword%";
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE (nama LIKE ? OR $col_pekerjaan LIKE ?) AND $col_jurusan = ? AND $col_tahun = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "ssss", $param_kw, $param_kw, $jurusan_filter, $tahun_filter);
} elseif ($keyword !== '' && $jurusan_filter !== '') {
    $param_kw = "%$keyword%";
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE (nama LIKE ? OR $col_pekerjaan LIKE ?) AND $col_jurusan = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "sss", $param_kw, $param_kw, $jurusan_filter);
} elseif ($keyword !== '' && $tahun_filter !== '') {
    $param_kw = "%$keyword%";
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE (nama LIKE ? OR $col_pekerjaan LIKE ?) AND $col_tahun = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "sss", $param_kw, $param_kw, $tahun_filter);
} elseif ($jurusan_filter !== '' && $tahun_filter !== '') {
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE $col_jurusan = ? AND $col_tahun = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "ss", $jurusan_filter, $tahun_filter);
} elseif ($keyword !== '') {
    $param_kw = "%$keyword%";
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE nama LIKE ? OR $col_pekerjaan LIKE ? OR $col_jurusan LIKE ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "sss", $param_kw, $param_kw, $param_kw);
} elseif ($jurusan_filter !== '') {
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE $col_jurusan = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "s", $jurusan_filter);
} elseif ($tahun_filter !== '') {
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE $col_tahun = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "s", $tahun_filter);
} else {
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni ORDER BY id DESC");
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Hitung total alumni via prepared statement
$stmt_stat = mysqli_prepare($conn, "SELECT 
    COUNT(*) as total_semua,
    COUNT(DISTINCT $col_jurusan) as total_jurusan,
    COUNT(DISTINCT $col_tahun) as total_angkatan
    FROM alumni");
mysqli_stmt_execute($stmt_stat);
$stat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_stat));
mysqli_stmt_close($stmt_stat);

$total_semua    = (int)($stat['total_semua'] ?? 0);
$total_jurusan  = (int)($stat['total_jurusan'] ?? 0);
$total_angkatan = (int)($stat['total_angkatan'] ?? 0);

// Ambil daftar jurusan dan tahun unik untuk opsi filter
$list_jurusan = [];
$res_j = mysqli_query($conn, "SELECT DISTINCT $col_jurusan AS j FROM alumni WHERE $col_jurusan IS NOT NULL ORDER BY $col_jurusan ASC");
if ($res_j) {
    while ($rj = mysqli_fetch_assoc($res_j)) {
        $list_jurusan[] = $rj['j'];
    }
}

$list_tahun = [];
$res_t = mysqli_query($conn, "SELECT DISTINCT $col_tahun AS t FROM alumni WHERE $col_tahun IS NOT NULL ORDER BY $col_tahun DESC");
if ($res_t) {
    while ($rt = mysqli_fetch_assoc($res_t)) {
        $list_tahun[] = $rt['t'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Alumni - Admin SMKS Prasetya Makassar</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .alumni-thumb {
            width: 52px;
            height: 52px;
            border-radius: 10px;
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
                <li class="breadcrumb-item active" aria-current="page">Data Alumni</li>
            </ol>
        </nav>

        <!-- Feedback Alerts -->
        <?php if (isset($_GET['status'])) : ?>
            <?php if ($_GET['status'] == 'sukses_tambah') : ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Data alumni baru berhasil ditambahkan!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_edit') : ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Data alumni berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'sukses_hapus') : ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-trash-fill me-2"></i> Data alumni beserta foto berhasil dihapus!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['status'] == 'gagal') : ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Terjadi kesalahan saat memproses data alumni!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Kartu Statistik Ringkas -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="card card-custom bg-white p-3 d-flex flex-row align-items-center justify-content-between h-100">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Alumni Terdata</span>
                        <h3 class="fw-bold mb-0 text-primary mt-1"><?= $total_semua; ?></h3>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 fs-4">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-custom bg-white p-3 d-flex flex-row align-items-center justify-content-between h-100">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Program Keahlian</span>
                        <h3 class="fw-bold mb-0 text-success mt-1"><?= $total_jurusan; ?></h3>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success p-3 fs-4">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-custom bg-white p-3 d-flex flex-row align-items-center justify-content-between h-100">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Angkatan Kelulusan</span>
                        <h3 class="fw-bold mb-0 text-info mt-1"><?= $total_angkatan; ?></h3>
                    </div>
                    <div class="rounded-circle bg-info-subtle text-info p-3 fs-4">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Kelola Data -->
        <div class="card card-custom bg-white p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-mortarboard text-primary me-2"></i>Daftar Alumni SMKS Prasetya
                    </h4>
                    <p class="text-muted small mb-0">Kelola informasi jejak karier, tahun lulus, jurusan, dan aktivitas alumni</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="../../alumni.php" target="_blank" class="btn btn-outline-secondary fw-medium">
                        <i class="bi bi-eye me-1"></i> Pratinjau Publik
                    </a>
                    <a href="tambah.php" class="btn btn-primary fw-medium">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Alumni
                    </a>
                </div>
            </div>

            <!-- Form Pencarian & Filter -->
            <form action="index.php" method="GET" class="row g-2 mb-4 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari nama atau pekerjaan..." value="<?= htmlspecialchars($keyword); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="jurusan" class="form-select">
                        <option value="">Semua Jurusan</option>
                        <?php foreach ($list_jurusan as $j_opt) : ?>
                            <option value="<?= htmlspecialchars($j_opt); ?>" <?= ($jurusan_filter === $j_opt) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($j_opt); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="tahun" class="form-select">
                        <option value="">Semua Tahun</option>
                        <?php foreach ($list_tahun as $t_opt) : ?>
                            <option value="<?= htmlspecialchars($t_opt); ?>" <?= ($tahun_filter === $t_opt) ? 'selected' : ''; ?>>
                                Lulus <?= htmlspecialchars($t_opt); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 fw-medium">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <?php if ($keyword !== '' || $jurusan_filter !== '' || $tahun_filter !== '') : ?>
                        <a href="index.php" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Tabel Data Alumni -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 80px;">Foto</th>
                            <th>Nama Alumni</th>
                            <th style="width: 120px;">Tahun Lulus</th>
                            <th style="width: 200px;">Jurusan</th>
                            <th>Pekerjaan / Aktivitas</th>
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
                                         class="alumni-thumb"
                                         onerror="this.src='https://placehold.co/100x100?text=Foto'">
                                </td>
                                <td>
                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($row['nama']); ?></span>
                                    <small class="text-muted">ID: #<?= (int)$row['id']; ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-3 py-1 fw-bold">
                                        <?= htmlspecialchars($row['tahun_lulus']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold text-wrap">
                                        <?= htmlspecialchars($row['jurusan']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($row['pekerjaan']); ?></span>
                                    <?php if (!empty($row['testimoni'])) : ?>
                                        <small class="text-muted text-truncate d-block" style="max-width: 260px;" title="<?= htmlspecialchars($row['testimoni']); ?>">
                                            <i class="bi bi-chat-quote me-1"></i>"<?= htmlspecialchars($row['testimoni']); ?>"
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="edit.php?id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Alumni">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= (int)$row['id']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data alumni <?= htmlspecialchars(addslashes($row['nama'])); ?>? Foto terkait akan ikut dihapus.');"
                                           title="Hapus Alumni">
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
                                    <i class="bi bi-mortarboard fs-1 text-secondary d-block mb-2"></i>
                                    <p class="mb-2">Tidak ada data alumni yang ditemukan.</p>
                                    <a href="tambah.php" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Data Alumni
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 text-muted small">
                <span>Menampilkan jejak karier lulusan SMKS Prasetya</span>
                <span>SMKS Prasetya Makassar</span>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

