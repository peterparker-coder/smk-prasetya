<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/tracker.php';
require_once __DIR__ . '/config/seo.php';

// Catat kunjungan pengunjung ke halaman fasilitas
if (function_exists('catat_pengunjung')) {
    catat_pengunjung($conn, 'Fasilitas Sekolah');
}

// Periksa struktur tabel sebelum menggunakan nama kolom (Ketentuan 13)
$col_nama = 'nama_fasilitas';
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM fasilitas LIKE 'nama_fasilitas'");
if (!$check_col || mysqli_num_rows($check_col) == 0) {
    $col_nama = 'nama';
}

// Parameter pencarian & filter kategori
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

// Query data menggunakan prepared statement
if ($q !== '' && $kategori !== '') {
    $param_kw = "%$q%";
    $stmt = mysqli_prepare($conn, "SELECT id, $col_nama AS nama_fasilitas, kategori, deskripsi, foto FROM fasilitas WHERE ($col_nama LIKE ? OR deskripsi LIKE ?) AND kategori = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "sss", $param_kw, $param_kw, $kategori);
} elseif ($q !== '') {
    $param_kw = "%$q%";
    $stmt = mysqli_prepare($conn, "SELECT id, $col_nama AS nama_fasilitas, kategori, deskripsi, foto FROM fasilitas WHERE $col_nama LIKE ? OR deskripsi LIKE ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "ss", $param_kw, $param_kw);
} elseif ($kategori !== '') {
    $stmt = mysqli_prepare($conn, "SELECT id, $col_nama AS nama_fasilitas, kategori, deskripsi, foto FROM fasilitas WHERE kategori = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "s", $kategori);
} else {
    $stmt = mysqli_prepare($conn, "SELECT id, $col_nama AS nama_fasilitas, kategori, deskripsi, foto FROM fasilitas ORDER BY id ASC");
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Ambil total semua fasilitas via prepared statement
$stmt_tot = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM fasilitas");
mysqli_stmt_execute($stmt_tot);
$res_tot = mysqli_stmt_get_result($stmt_tot);
$total_semua = (int)(mysqli_fetch_assoc($res_tot)['total'] ?? 0);
mysqli_stmt_close($stmt_tot);

// Ambil daftar kategori beserta jumlahnya via prepared statement
$stmt_kats = mysqli_prepare($conn, "SELECT kategori, COUNT(*) as jml FROM fasilitas WHERE kategori IS NOT NULL AND kategori != '' GROUP BY kategori ORDER BY kategori ASC");
mysqli_stmt_execute($stmt_kats);
$res_kats = mysqli_stmt_get_result($stmt_kats);
$kategori_counts = [];
while ($rk = mysqli_fetch_assoc($res_kats)) {
    $kategori_counts[$rk['kategori']] = (int)$rk['jml'];
}
mysqli_stmt_close($stmt_kats);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    render_seo_tags([
        'title'       => 'Fasilitas Sekolah & Laboratorium Praktik - SMKS Prasetya Makassar',
        'description' => 'Sarana dan prasarana pendidikan lengkap di SMKS Prasetya Makassar: laboratorium komputer modern, bengkel praktik otomotif TBSM, ruang kelas ber-AC, musholla, dan sarana olahraga.',
        'keywords'    => 'Fasilitas SMKS Prasetya Makassar, Laboratorium Komputer SMK Makassar, Bengkel TBSM SMK, Ruang Praktik Siswa, Sarana Sekolah Kejuruan Makassar',
        'type'        => 'website',
        'image'       => 'assets/uploads/logo-smks-prasetya.png',
    ]);
    render_breadcrumb_schema([
        'Beranda' => 'index.php',
        'Fasilitas Sekolah' => 'fasilitas.php'
    ]);
    ?>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Tema Terang / Gelap -->
    <link rel="stylesheet" href="assets/css/theme.css">

    <!-- JavaScript Tema -->
    <script src="assets/js/theme.js"></script>

    <style>
        :root {
            --primary-color: #1e3c72;
            --primary-dark: #14284b;
            --accent-color: #f59e0b;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #334155;
            scroll-behavior: smooth;
        }

        .bg-custom-navy {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        .page-hero {
            background:
                linear-gradient(rgba(15, 23, 42, 0.88), rgba(30, 60, 114, 0.92)),
                url('https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=1200&auto=format&fit=crop')
                center/cover no-repeat;
            padding: 90px 0 70px;
            color: #fff;
        }

        .navbar-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            margin-right: 10px;
        }

        .navbar-brand span {
            font-size: 16px;
        }

        .card-fasilitas {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            background: #fff;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .card-fasilitas:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 32px rgba(30,60,114,0.14);
        }

        .fasilitas-img-wrapper {
            position: relative;
            width: 100%;
            height: 230px;
            background-color: #f1f5f9;
            overflow: hidden;
            cursor: pointer;
        }

        .fasilitas-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .card-fasilitas:hover .fasilitas-img-wrapper img {
            transform: scale(1.06);
        }

        .fasilitas-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            font-size: 0.78rem;
            padding: 6px 14px;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.18);
            font-weight: 600;
        }

        .card-fasilitas .card-body {
            padding: 22px 22px 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card-fasilitas .nama-fasilitas {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .card-fasilitas .deskripsi-fasilitas {
            font-size: 0.88rem;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 0;
            flex-grow: 1;
        }

        .filter-btn {
            border-radius: 30px;
            padding: 8px 18px;
            font-weight: 500;
            font-size: 0.88rem;
            transition: 0.2s;
        }

        /* Dark mode overrides */
        html[data-theme="dark"] .card-fasilitas {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        html[data-theme="dark"] .card-fasilitas .nama-fasilitas {
            color: #f3f4f6 !important;
        }

        html[data-theme="dark"] .card-fasilitas .deskripsi-fasilitas {
            color: #9ca3af !important;
        }

        html[data-theme="dark"] .fasilitas-img-wrapper {
            background-color: #111827 !important;
        }

        @media (max-width: 576px) {
            .navbar-logo {
                width: 40px;
                height: 40px;
            }
            .navbar-brand span {
                font-size: 13px;
            }
            .page-hero {
                padding: 60px 0 45px;
            }
        }
    </style>
</head>
<body>

<!-- Topbar -->
<div class="bg-dark text-white py-2 small d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <span class="me-3">
                <i class="bi bi-geo-alt-fill text-warning me-1"></i>
                Jl. Buntusu NHP / BTP No. 17, Makassar
            </span>
            <span>
                <i class="bi bi-telephone-fill text-warning me-1"></i>
                0411 4770832
            </span>
        </div>
        <div>
            <span>
                <i class="bi bi-clock-fill text-warning me-1"></i>
                Sehari Penuh, 5 Hari
            </span>
        </div>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-custom-navy sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
            <img src="assets/uploads/logo-smks-prasetya.png"
                 alt="Logo Resmi SMKS Prasetya Makassar"
                 class="navbar-logo"
                 width="48"
                 height="48"
                 decoding="async"
                 onerror="this.src='https://placehold.co/50x50?text=SMKS'">
            <span>SMKS PRASETYA MAKASSAR</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="mainNav">
            <!-- Tombol Dark Mode -->
            <button type="button" id="themeToggle" class="btn btn-outline-light btn-sm me-3 mb-2 mb-lg-0">
                <i class="bi bi-moon-stars-fill me-1"></i> Dark Mode
            </button>

            <ul class="navbar-nav me-3 mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#profil">Profil Sekolah</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#jurusan">Jurusan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="guru.php">Guru & Tendik</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="fasilitas.php">Fasilitas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="alumni.php">Alumni</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#berita">Berita</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#galeri">Galeri</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#kontak">Kontak</a>
                </li>
            </ul>

            <a href="login.php" class="btn btn-warning text-dark fw-bold btn-sm px-3 rounded-pill shadow-sm">
                <i class="bi bi-lock-fill me-1"></i> Login Admin
            </a>
        </div>
    </div>
</nav>

<!-- Page Hero Header -->
<header class="page-hero text-center">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-3">
                <li class="breadcrumb-item"><a href="index.php" class="text-white-50 text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Fasilitas Sekolah</li>
            </ol>
        </nav>

        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold text-uppercase mb-3">
            Sarana & Prasarana Pendidikan
        </span>

        <h1 class="display-5 fw-bold mb-3 text-white">
            Fasilitas SMKS Prasetya Makassar
        </h1>

        <p class="lead text-white-50 col-lg-8 mx-auto mb-0">
            Fasilitas ruang praktik kejuruan standar industri, ruang belajar representatif ber-AC, laboratorium komputer terstandar, dan sarana penunjang lengkap untuk mengoptimalkan potensi peserta didik.
        </p>
    </div>
</header>

<!-- Main Content: Daftar Fasilitas Sekolah -->
<main class="py-5 bg-light" id="daftar-fasilitas">
    <div class="container py-2">

        <!-- Bar Pencarian & Filter Kategori -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 bg-white">
            <div class="row g-3 align-items-center">
                <!-- Tombol Filter Kategori -->
                <div class="col-lg-7">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="fasilitas.php<?= ($q !== '') ? '?q=' . urlencode($q) : ''; ?>"
                           class="btn filter-btn <?= ($kategori === '') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-grid-fill me-1"></i> Semua
                            <span class="badge <?= ($kategori === '') ? 'bg-light text-primary' : 'bg-secondary text-white'; ?> rounded-pill ms-1"><?= $total_semua; ?></span>
                        </a>

                        <?php foreach ($kategori_counts as $kat_name => $kat_total) : ?>
                            <a href="fasilitas.php?kategori=<?= urlencode($kat_name); ?><?= ($q !== '') ? '&q=' . urlencode($q) : ''; ?>"
                               class="btn filter-btn <?= ($kategori === $kat_name) ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                                <?= htmlspecialchars($kat_name); ?>
                                <span class="badge <?= ($kategori === $kat_name) ? 'bg-light text-primary' : 'bg-secondary text-white'; ?> rounded-pill ms-1"><?= $kat_total; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Input Pencarian -->
                <div class="col-lg-5">
                    <form action="fasilitas.php" method="GET" class="d-flex gap-2">
                        <?php if ($kategori !== '') : ?>
                            <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori); ?>">
                        <?php endif; ?>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari nama fasilitas / laboratorium..." value="<?= htmlspecialchars($q); ?>">
                            <button type="submit" class="btn btn-primary px-3">Cari</button>
                        </div>
                        <?php if ($q !== '' || $kategori !== '') : ?>
                            <a href="fasilitas.php" class="btn btn-outline-secondary" title="Hapus Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <?php if ($q !== '' || $kategori !== '') : ?>
                <div class="mt-3 pt-3 border-top small text-muted d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        Menampilkan hasil filter: 
                        <?php if ($kategori !== '') : ?>
                            <span class="badge bg-secondary me-1"><?= htmlspecialchars($kategori); ?></span>
                        <?php endif; ?>
                        <?php if ($q !== '') : ?>
                            <span>Kata kunci: "<strong><?= htmlspecialchars($q); ?></strong>"</span>
                        <?php endif; ?>
                    </div>
                    <a href="fasilitas.php" class="text-decoration-none small text-danger fw-semibold">
                        <i class="bi bi-x-circle me-1"></i>Reset Pencarian
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Grid Kartu Fasilitas -->
        <div class="row g-4">
            <?php
            $jml_fasilitas = 0;
            if ($result && mysqli_num_rows($result) > 0) :
                while ($fas = mysqli_fetch_assoc($result)) :
                    $jml_fasilitas++;
                    $foto_file = $fas['foto'] ?? '';
                    $foto_src = (!empty($foto_file) && file_exists(__DIR__ . '/assets/uploads/' . $foto_file))
                        ? 'assets/uploads/' . htmlspecialchars($foto_file)
                        : 'https://placehold.co/600x400?text=' . urlencode($fas['nama_fasilitas']);

                    // Tentukan warna badge kategori
                    $badge_bg = 'bg-primary';
                    $kat_lower = strtolower($fas['kategori'] ?? '');
                    if (strpos($kat_lower, 'lab') !== false || strpos($kat_lower, 'praktik') !== false) {
                        $badge_bg = 'bg-danger';
                    } elseif (strpos($kat_lower, 'belajar') !== false || strpos($kat_lower, 'kelas') !== false || strpos($kat_lower, 'pustaka') !== false) {
                        $badge_bg = 'bg-success';
                    } elseif (strpos($kat_lower, 'olahraga') !== false) {
                        $badge_bg = 'bg-warning text-dark';
                    } elseif (strpos($kat_lower, 'penunjang') !== false || strpos($kat_lower, 'ibadah') !== false) {
                        $badge_bg = 'bg-info text-white';
                    }
            ?>
                <div class="col-sm-6 col-lg-4">
                    <div class="card card-fasilitas">
                        <!-- Wrapper Foto -->
                        <div class="fasilitas-img-wrapper" data-bs-toggle="modal" data-bs-target="#modalFasilitas<?= (int)$fas['id']; ?>" title="Klik untuk memperbesar">
                            <img src="<?= $foto_src; ?>"
                                 alt="Fasilitas <?= htmlspecialchars($fas['nama_fasilitas']); ?> - SMKS Prasetya Makassar"
                                 loading="lazy"
                                 decoding="async"
                                 onerror="this.src='https://placehold.co/600x400?text=Fasilitas+Sekolah'">

                            <span class="fasilitas-badge <?= $badge_bg; ?> text-white">
                                <i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($fas['kategori'] ?? 'Umum'); ?>
                            </span>
                        </div>

                        <!-- Konten Fasilitas -->
                        <div class="card-body">
                            <h5 class="nama-fasilitas">
                                <?= htmlspecialchars($fas['nama_fasilitas']); ?>
                            </h5>

                            <p class="deskripsi-fasilitas">
                                <?= !empty($fas['deskripsi']) ? nl2br(htmlspecialchars($fas['deskripsi'])) : 'Sarana dan prasarana penunjang kegiatan pembelajaran siswa SMKS Prasetya Makassar.'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Detail & Foto Perbesar -->
                <div class="modal fade" id="modalFasilitas<?= (int)$fas['id']; ?>" tabindex="-1" aria-labelledby="modalLabel<?= (int)$fas['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header bg-custom-navy text-white border-0 py-3">
                                <h5 class="modal-title fw-bold" id="modalLabel<?= (int)$fas['id']; ?>">
                                    <i class="bi bi-building me-2 text-warning"></i><?= htmlspecialchars($fas['nama_fasilitas']); ?>
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <img src="<?= $foto_src; ?>"
                                     alt="Detail Fasilitas <?= htmlspecialchars($fas['nama_fasilitas']); ?> - SMKS Prasetya Makassar"
                                     class="w-100 object-fit-cover"
                                     style="max-height: 420px;"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.src='https://placehold.co/800x500?text=Fasilitas+Sekolah'">
                                <div class="p-4">
                                    <div class="mb-3">
                                        <span class="badge <?= $badge_bg; ?> rounded-pill px-3 py-2 fw-semibold">
                                            <i class="bi bi-tag-fill me-1"></i> Kategori: <?= htmlspecialchars($fas['kategori'] ?? 'Umum'); ?>
                                        </span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2">Deskripsi & Kelengkapan Fasilitas:</h6>
                                    <p class="text-secondary small mb-0" style="line-height: 1.8;">
                                        <?= !empty($fas['deskripsi']) ? nl2br(htmlspecialchars($fas['deskripsi'])) : 'Sarana prasarana sekolah terstandar untuk kegiatan akademik dan kejuruan.'; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="modal-footer border-top bg-light py-2">
                                <button type="button" class="btn btn-secondary btn-sm px-4 rounded-pill" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>

            <?php
                endwhile;
                mysqli_stmt_close($stmt);
            else :
            ?>
                <div class="col-12">
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm p-5">
                        <i class="bi bi-building-x fs-1 text-secondary opacity-50 d-block mb-3"></i>
                        <h4 class="fw-bold text-dark">Fasilitas Tidak Ditemukan</h4>
                        <p class="text-muted col-md-6 mx-auto mb-4">
                            <?php if ($q !== '') : ?>
                                Tidak ditemukan fasilitas sekolah dengan kata kunci "<strong><?= htmlspecialchars($q); ?></strong>".
                            <?php else : ?>
                                Belum ada data fasilitas yang terdaftar pada kategori yang dipilih.
                            <?php endif; ?>
                        </p>
                        <a href="fasilitas.php" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Tampilkan Semua Fasilitas
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<!-- Info Kunjungan & Kemitraan -->
<section class="py-5 bg-white border-top">
    <div class="container py-3 text-center">
        <div class="col-lg-8 mx-auto">
            <h3 class="fw-bold text-dark mb-2">Tertarik Melihat Langsung Kampus SMKS Prasetya?</h3>
            <p class="text-secondary mb-4">
                Kami menyambut calon siswa, orang tua, dan mitra industri yang ingin mengunjungi laboratorium, bengkel praktik, serta fasilitas pembelajaran kami.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="index.php#kontak" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-geo-alt-fill me-1"></i> Kunjungi Kami
                </a>
                <a href="guru.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-people-fill me-1"></i> Kenali Guru & Tendik
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-3">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-5">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="bi bi-mortarboard-fill text-warning me-2"></i>
                    SMKS Prasetya Makassar
                </h5>
                <p class="text-white-50 small pe-lg-4">
                    SMKS Prasetya Makassar merupakan sekolah menengah kejuruan swasta yang berlokasi di Kecamatan Tamalanrea, Kota Makassar, Sulawesi Selatan. Berkomitmen mencetak lulusan berakhlak mulia dan berdaya saing global.
                </p>
            </div>

            <div class="col-md-6 col-lg-3">
                <h6 class="fw-bold text-warning mb-3">Navigasi Halaman</h6>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2"><a href="index.php#beranda" class="text-white-50 text-decoration-none">Beranda</a></li>
                    <li class="mb-2"><a href="index.php#profil" class="text-white-50 text-decoration-none">Profil Sekolah</a></li>
                    <li class="mb-2"><a href="index.php#jurusan" class="text-white-50 text-decoration-none">Program Keahlian</a></li>
                    <li class="mb-2"><a href="guru.php" class="text-white-50 text-decoration-none">Guru & Tendik</a></li>
                    <li class="mb-2"><a href="fasilitas.php" class="text-white text-decoration-none fw-semibold">Fasilitas Sekolah</a></li>
                    <li class="mb-2"><a href="alumni.php" class="text-white-50 text-decoration-none">Alumni</a></li>
                    <li class="mb-2"><a href="index.php#berita" class="text-white-50 text-decoration-none">Berita</a></li>
                    <li class="mb-2"><a href="index.php#galeri" class="text-white-50 text-decoration-none">Galeri</a></li>
                    <li class="mb-2"><a href="index.php#kontak" class="text-white-50 text-decoration-none">Kontak Kami</a></li>
                </ul>
            </div>

            <div class="col-md-6 col-lg-4">
                <h6 class="fw-bold text-warning mb-3">Akses Administrator</h6>
                <p class="small text-white-50 mb-2">
                    Pengelola sekolah dapat masuk ke panel administrasi untuk mengelola data jurusan, berita, guru, fasilitas, dan pesan pengunjung.
                </p>
                <a href="login.php" class="btn btn-outline-warning btn-sm rounded-pill">
                    <i class="bi bi-person-fill-lock me-1"></i> Masuk Panel Admin
                </a>
            </div>
        </div>

        <hr class="border-secondary">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 text-white-50 small">
            <div>
                &copy; <?= date('Y'); ?> SMKS Prasetya Makassar. All Rights Reserved.
            </div>
            <div>
                Dirancang dengan PHP Native & Bootstrap 5
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

