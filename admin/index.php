<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';

$active_menu = 'dashboard';
$base_admin  = '';

// -------------------------------------------------------------
// 1. DATA STATISTIK PENGUNJUNG
// -------------------------------------------------------------
// Pastikan tabel pengunjung ada
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS `pengunjung` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `ip_address` VARCHAR(50) NOT NULL,
        `user_agent` VARCHAR(255) DEFAULT NULL,
        `halaman` VARCHAR(255) DEFAULT 'Beranda',
        `tanggal` DATE NOT NULL,
        `hits` INT DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_tanggal` (`tanggal`),
        INDEX `idx_ip_tanggal` (`ip_address`, `tanggal`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 1. Total Pengunjung & Hits Website
$q_total_pengunjung = mysqli_query($conn, "SELECT COUNT(*) as total, COALESCE(SUM(hits), 0) as total_hits FROM pengunjung");
$d_total_pengunjung = mysqli_fetch_assoc($q_total_pengunjung);
$total_pengunjung   = (int)($d_total_pengunjung['total'] ?? 0);
$total_hits         = (int)($d_total_pengunjung['total_hits'] ?? 0);

// 2. Jumlah Pengunjung Hari Ini
$q_pengunjung_hari_ini = mysqli_query($conn, "SELECT COUNT(*) as total, COALESCE(SUM(hits), 0) as total_hits FROM pengunjung WHERE tanggal = CURDATE()");
$d_pengunjung_hari_ini = mysqli_fetch_assoc($q_pengunjung_hari_ini);
$pengunjung_hari_ini   = (int)($d_pengunjung_hari_ini['total'] ?? 0);
$hits_hari_ini         = (int)($d_pengunjung_hari_ini['total_hits'] ?? 0);

// 3. Jumlah Pengunjung Bulan Ini
$q_pengunjung_bulan_ini = mysqli_query($conn, "SELECT COUNT(*) as total, COALESCE(SUM(hits), 0) as total_hits FROM pengunjung WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())");
$d_pengunjung_bulan_ini = mysqli_fetch_assoc($q_pengunjung_bulan_ini);
$pengunjung_bulan_ini   = (int)($d_pengunjung_bulan_ini['total'] ?? 0);
$hits_bulan_ini         = (int)($d_pengunjung_bulan_ini['total_hits'] ?? 0);

// Nama bulan Indonesia untuk header
$nama_bulan_arr = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$bulan_ini_nama = ($nama_bulan_arr[(int)date('m')] ?? date('F')) . ' ' . date('Y');

// 4. Data untuk Grafik Pengunjung Harian (14 Hari Terakhir)
$chart_days = 14;
$chart_labels = [];
$chart_pengunjung = [];
$chart_hits = [];

for ($i = $chart_days - 1; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[$tgl] = date('d M', strtotime($tgl));
    $chart_pengunjung[$tgl] = 0;
    $chart_hits[$tgl] = 0;
}

$q_grafik = mysqli_query($conn, "
    SELECT tanggal, COUNT(*) as jml_pengunjung, COALESCE(SUM(hits), 0) as jml_hits
    FROM pengunjung
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL " . ($chart_days - 1) . " DAY)
    GROUP BY tanggal
    ORDER BY tanggal ASC
");

while ($g = mysqli_fetch_assoc($q_grafik)) {
    if (isset($chart_pengunjung[$g['tanggal']])) {
        $chart_pengunjung[$g['tanggal']] = (int)$g['jml_pengunjung'];
        $chart_hits[$g['tanggal']] = (int)$g['jml_hits'];
    }
}

$json_labels     = json_encode(array_values($chart_labels));
$json_pengunjung = json_encode(array_values($chart_pengunjung));
$json_hits       = json_encode(array_values($chart_hits));

// 5. Riwayat Pengunjung Berdasarkan Tanggal (15 Hari Terakhir)
$q_riwayat = mysqli_query($conn, "
    SELECT tanggal, 
           COUNT(*) as jml_pengunjung, 
           COALESCE(SUM(hits), 0) as jml_hits,
           MAX(updated_at) as terakhir_aktif
    FROM pengunjung
    GROUP BY tanggal
    ORDER BY tanggal DESC
    LIMIT 15
");

$riwayat_data = [];
$max_pengunjung_riwayat = 1;

while ($r = mysqli_fetch_assoc($q_riwayat)) {
    $riwayat_data[] = $r;
    if ((int)$r['jml_pengunjung'] > $max_pengunjung_riwayat) {
        $max_pengunjung_riwayat = (int)$r['jml_pengunjung'];
    }
}

// -------------------------------------------------------------
// 2. DATA STATISTIK KONTEN SEKOLAH
// -------------------------------------------------------------
$total_jurusan   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM jurusan"))['total'] ?? 0;
$total_guru      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM guru"))['total'] ?? 0;
$total_fasilitas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM fasilitas"))['total'] ?? 0;
$total_alumni    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alumni"))['total'] ?? 0;
$total_berita    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM berita"))['total'] ?? 0;
$total_pesan   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesan"))['total'] ?? 0;
$pesan_baru    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesan WHERE status='belum dibaca'"))['total'] ?? 0;
$total_admin   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM admin"))['total'] ?? 0;

// Berita & Pesan Terbaru
$q_recent_berita = mysqli_query($conn, "SELECT * FROM berita ORDER BY id DESC LIMIT 5");
$q_recent_pesan  = mysqli_query($conn, "SELECT * FROM pesan ORDER BY id DESC LIMIT 5");

// Fungsi pembantu format tanggal Indonesia
function format_tgl_indo($tanggal) {
    $hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    $bulan = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    $ts = strtotime($tanggal);
    $nama_hari = $hari[date('l', $ts)] ?? '';
    $tgl = date('d', $ts);
    $bln = $bulan[(int)date('m', $ts)] ?? '';
    $thn = date('Y', $ts);
    return "$nama_hari, $tgl $bln $thn";
}
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .chart-container {
            position: relative;
            height: 310px;
            width: 100%;
        }
        .progress-slim {
            height: 6px;
            border-radius: 3px;
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
                    <p class="mb-0 text-white-50">Panel Manajemen Website Profil SMKS Prasetya Makassar. Pantau statistik trafik, kelola konten jurusan, berita, dan pesan pengunjung.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="#statistik-pengunjung" class="btn btn-info text-white fw-semibold shadow-sm">
                        <i class="bi bi-graph-up me-1"></i> Statistik Pengunjung
                    </a>
                    <a href="guru/index.php" class="btn btn-light fw-semibold shadow-sm text-primary">
                        <i class="bi bi-people me-1"></i> Guru & Tendik
                    </a>
                    <a href="fasilitas/index.php" class="btn btn-light fw-semibold shadow-sm text-dark">
                        <i class="bi bi-building me-1"></i> Fasilitas
                    </a>
                    <a href="alumni/index.php" class="btn btn-light fw-semibold shadow-sm text-primary">
                        <i class="bi bi-mortarboard me-1"></i> Alumni
                    </a>
                    <a href="berita/tambah.php" class="btn btn-warning fw-semibold shadow-sm text-dark">
                        <i class="bi bi-plus-circle me-1"></i> Buat Berita
                    </a>
                    <a href="kelola-admin/index.php" class="btn btn-outline-light fw-semibold shadow-sm">
                        <i class="bi bi-person-gear me-1"></i> Kelola Admin
                    </a>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- BAGIAN 1: STATISTIK PENGUNJUNG WEBSITE                         -->
        <!-- ============================================================== -->
        <div id="statistik-pengunjung" class="mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Statistik Pengunjung Website
                    </h5>
                    <small class="text-muted">Ringkasan trafik pengunjung unik dan tayangan halaman secara berkala</small>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-medium">
                    <i class="bi bi-activity me-1"></i> Pelacakan Trafik Aktif
                </span>
            </div>

            <!-- Kartu Statistik Pengunjung -->
            <div class="row g-3 mb-4">
                <!-- 1. Total Pengunjung Website -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card stat-card bg-white p-3 shadow-sm border-start border-primary border-4 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold text-uppercase">Total Pengunjung</span>
                                <h2 class="fw-bold mb-0 text-primary mt-1"><?= number_format($total_pengunjung); ?></h2>
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-eye-fill me-1 text-primary"></i> <?= number_format($total_hits); ?> total tayangan (hits)
                                </small>
                            </div>
                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Pengunjung Hari Ini -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card stat-card bg-white p-3 shadow-sm border-start border-success border-4 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold text-uppercase">Pengunjung Hari Ini</span>
                                <h2 class="fw-bold mb-0 text-success mt-1"><?= number_format($pengunjung_hari_ini); ?></h2>
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-arrow-up-circle-fill me-1 text-success"></i> <?= number_format($hits_hari_ini); ?> tayangan hari ini
                                </small>
                            </div>
                            <div class="stat-icon bg-success-subtle text-success">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Pengunjung Bulan Ini -->
                <div class="col-sm-12 col-lg-4">
                    <div class="card stat-card bg-white p-3 shadow-sm border-start border-info border-4 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold text-uppercase">Pengunjung Bulan Ini</span>
                                <h2 class="fw-bold mb-0 text-info mt-1"><?= number_format($pengunjung_bulan_ini); ?></h2>
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-calendar3 me-1 text-info"></i> <?= $bulan_ini_nama; ?> (<?= number_format($hits_bulan_ini); ?> hits)
                                </small>
                            </div>
                            <div class="stat-icon bg-info-subtle text-info">
                                <i class="bi bi-calendar-month-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik Pengunjung Harian & Riwayat Pengunjung -->
            <div class="row g-4">
                <!-- 4. Grafik Pengunjung Harian -->
                <div class="col-lg-7">
                    <div class="card card-custom bg-white p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0">
                                    <i class="bi bi-graph-up-arrow text-primary me-2"></i>Grafik Pengunjung Harian
                                </h5>
                                <small class="text-muted">Tren kunjungan 14 hari terakhir</small>
                            </div>
                            <span class="badge bg-light text-dark border px-2 py-1 small">
                                14 Hari Terakhir
                            </span>
                        </div>
                        <div class="chart-container">
                            <canvas id="visitorChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 5. Riwayat Pengunjung Berdasarkan Tanggal -->
                <div class="col-lg-5">
                    <div class="card card-custom bg-white p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0">
                                    <i class="bi bi-clock-history text-success me-2"></i>Riwayat Pengunjung
                                </h5>
                                <small class="text-muted">Data kunjungan per tanggal</small>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary small">
                                <?= count($riwayat_data); ?> Hari
                            </span>
                        </div>

                        <?php if (!empty($riwayat_data)) : ?>
                            <div class="table-responsive" style="max-height: 310px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small sticky-top">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th class="text-center">Pengunjung</th>
                                            <th class="text-center">Tayangan</th>
                                            <th style="width: 70px;">Rasio</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php 
                                        foreach ($riwayat_data as $row) : 
                                            $is_today     = ($row['tanggal'] === date('Y-m-d'));
                                            $is_yesterday = ($row['tanggal'] === date('Y-m-d', strtotime('-1 day')));
                                            $persen       = round(((int)$row['jml_pengunjung'] / $max_pengunjung_riwayat) * 100);
                                        ?>
                                            <tr class="<?= $is_today ? 'table-light fw-medium' : ''; ?>">
                                                <td>
                                                    <span class="d-block text-dark"><?= format_tgl_indo($row['tanggal']); ?></span>
                                                    <?php if ($is_today) : ?>
                                                        <span class="badge bg-success-subtle text-success py-0 px-1" style="font-size: 10px;">Hari Ini</span>
                                                    <?php elseif ($is_yesterday) : ?>
                                                        <span class="badge bg-secondary-subtle text-secondary py-0 px-1" style="font-size: 10px;">Kemarin</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary-subtle text-primary px-2 py-1">
                                                        <?= number_format($row['jml_pengunjung']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center text-muted">
                                                    <?= number_format($row['jml_hits']); ?>
                                                </td>
                                                <td>
                                                    <div class="progress progress-slim" title="<?= $persen; ?>% dari trafik puncak">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $persen; ?>%"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-calendar-x fs-1 text-secondary"></i>
                                <p class="mt-2 mb-0">Belum ada riwayat pengunjung.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- BAGIAN 2: STATISTIK KONTEN SEKOLAH                             -->
        <!-- ============================================================== -->
        <h5 class="fw-bold mb-3 text-dark">
            <i class="bi bi-grid-fill text-primary me-2"></i>Statistik Konten & Data Sekolah
        </h5>

        <div class="row g-3 mb-4">
            <!-- Stat Jurusan -->
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card bg-white p-3 shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Program Jurusan</span>
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

            <!-- Stat Guru & Tendik -->
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card bg-white p-3 shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Guru & Tendik</span>
                            <h2 class="fw-bold mb-0 text-dark"><?= $total_guru; ?></h2>
                        </div>
                        <div class="stat-icon bg-warning-subtle text-warning">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <a href="guru/index.php" class="small text-decoration-none text-warning fw-medium">
                            Kelola Guru & Tendik &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stat Fasilitas -->
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card bg-white p-3 shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Fasilitas Sekolah</span>
                            <h2 class="fw-bold mb-0 text-dark"><?= $total_fasilitas; ?></h2>
                        </div>
                        <div class="stat-icon bg-secondary-subtle text-secondary">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <a href="fasilitas/index.php" class="small text-decoration-none text-secondary fw-medium">
                            Kelola Fasilitas &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stat Alumni -->
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card bg-white p-3 shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Data Alumni</span>
                            <h2 class="fw-bold mb-0 text-dark"><?= $total_alumni; ?></h2>
                        </div>
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <a href="alumni/index.php" class="small text-decoration-none text-primary fw-medium">
                            Kelola Alumni &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stat Berita -->
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card bg-white p-3 shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Total Berita</span>
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
                <div class="card stat-card bg-white p-3 shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Pesan Masuk</span>
                            <h2 class="fw-bold mb-0 text-dark" id="dashTotalMsgCount"><?= $total_pesan; ?></h2>
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
                <div class="card stat-card bg-white p-3 shadow-sm border-start border-danger border-4 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Pesan Belum Dibaca</span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <h2 class="fw-bold mb-0 text-danger" id="dashUnreadCount"><?= $pesan_baru; ?></h2>
                                <span id="dashUnreadStatus" class="badge <?= ($pesan_baru > 0) ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle'; ?>">
                                    <?php if ($pesan_baru > 0) : ?>
                                        <span class="spinner-grow spinner-grow-sm me-1" style="width: 7px; height: 7px;"></span>Perlu ditindaklanjuti
                                    <?php else : ?>
                                        <i class="bi bi-check2-all me-1"></i>Semua terbaca
                                    <?php endif; ?>
                                </span>
                            </div>
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

        <!-- Tabel Ringkasan Aktivitas Berita & Pesan -->
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

                    <!-- Alert Notifikasi Pesan Belum Dibaca -->
                    <div id="dashUnreadAlert" class="alert alert-warning py-2 px-3 small d-flex align-items-center justify-content-between mb-3 shadow-sm border-warning border-start border-4 <?= ($pesan_baru > 0) ? '' : 'd-none'; ?>">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-bell-fill text-danger me-2 fs-6 bell-ring"></i>
                            <span>Ada <strong id="dashUnreadAlertCount"><?= $pesan_baru; ?></strong> pesan baru yang belum dibaca!</span>
                        </div>
                        <a href="pesan/index.php" class="btn btn-sm btn-danger fw-medium py-1 px-2" style="font-size: 11px;">
                            <i class="bi bi-envelope-open me-1"></i> Buka Kotak Masuk
                        </a>
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
                                    <?php while ($p = mysqli_fetch_assoc($q_recent_pesan)) : 
                                        $is_unread_p = ($p['status'] === 'belum dibaca');
                                    ?>
                                        <tr class="<?= $is_unread_p ? 'table-warning bg-opacity-25 fw-medium' : ''; ?>">
                                            <td>
                                                <span class="fw-medium text-dark d-block"><?= htmlspecialchars($p['nama']); ?></span>
                                                <small class="text-muted"><?= htmlspecialchars($p['email']); ?></small>
                                            </td>
                                            <td>
                                                <span class="d-block text-truncate text-secondary" style="max-width: 200px;">
                                                    <?= htmlspecialchars($p['subjek']); ?>
                                                </span>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($p['tanggal'] ?? ($p['tanggal_kirim'] ?? 'now'))); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($is_unread_p) : ?>
                                                    <span class="badge bg-danger">
                                                        <span class="spinner-grow spinner-grow-sm me-1" style="width: 5px; height: 5px;"></span>Baru
                                                    </span>
                                                <?php else : ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border">Dibaca</span>
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

    <!-- Inisialisasi Chart.js Grafik Pengunjung Harian -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('visitorChart').getContext('2d');

            // Gradient untuk dataset pengunjung unik
            const gradientPengunjung = ctx.createLinearGradient(0, 0, 0, 280);
            gradientPengunjung.addColorStop(0, 'rgba(13, 110, 253, 0.28)');
            gradientPengunjung.addColorStop(1, 'rgba(13, 110, 253, 0.01)');

            // Gradient untuk dataset tayangan (hits)
            const gradientHits = ctx.createLinearGradient(0, 0, 0, 280);
            gradientHits.addColorStop(0, 'rgba(13, 202, 240, 0.20)');
            gradientHits.addColorStop(1, 'rgba(13, 202, 240, 0.01)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= $json_labels; ?>,
                    datasets: [
                        {
                            label: 'Pengunjung Unik',
                            data: <?= $json_pengunjung; ?>,
                            borderColor: '#0d6efd',
                            backgroundColor: gradientPengunjung,
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#0d6efd',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Total Tayangan (Hits)',
                            data: <?= $json_hits; ?>,
                            borderColor: '#0dcaf0',
                            backgroundColor: gradientHits,
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#0dcaf0',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 1.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: {
                                    family: "'Poppins', sans-serif",
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(33, 37, 41, 0.95)',
                            titleFont: {
                                family: "'Poppins', sans-serif",
                                size: 13,
                                weight: '600'
                            },
                            bodyFont: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + context.parsed.y + ' data';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    family: "'Poppins', sans-serif",
                                    size: 11
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif",
                                    size: 11
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
