<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/tracker.php';
require_once __DIR__ . '/config/seo.php';

// Catat kunjungan pengunjung ke halaman Guru & Tenaga Kependidikan
if (function_exists('catat_pengunjung')) {
    catat_pengunjung($conn, 'Guru & Tenaga Kependidikan');
}

// Parameter pencarian & filter kategori
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

// Query data menggunakan prepared statement (tanpa mengasumsikan kolom created_at)
if ($q !== '' && $kategori !== '') {
    $param_kw = "%$q%";
    $stmt = mysqli_prepare($conn, "SELECT id, nama, jabatan, kategori, deskripsi, foto FROM guru WHERE (nama LIKE ? OR jabatan LIKE ? OR deskripsi LIKE ?) AND kategori = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "ssss", $param_kw, $param_kw, $param_kw, $kategori);
} elseif ($q !== '') {
    $param_kw = "%$q%";
    $stmt = mysqli_prepare($conn, "SELECT id, nama, jabatan, kategori, deskripsi, foto FROM guru WHERE nama LIKE ? OR jabatan LIKE ? OR deskripsi LIKE ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "sss", $param_kw, $param_kw, $param_kw);
} elseif ($kategori !== '') {
    $stmt = mysqli_prepare($conn, "SELECT id, nama, jabatan, kategori, deskripsi, foto FROM guru WHERE kategori = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "s", $kategori);
} else {
    $stmt = mysqli_prepare($conn, "SELECT id, nama, jabatan, kategori, deskripsi, foto FROM guru ORDER BY id ASC");
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Hitung total guru & tendik untuk badge filter menggunakan prepared statement
$stmt_count = mysqli_prepare($conn, "SELECT 
    COUNT(*) as total_semua,
    SUM(CASE WHEN kategori = 'Guru' THEN 1 ELSE 0 END) as total_guru,
    SUM(CASE WHEN kategori = 'Tenaga Kependidikan' THEN 1 ELSE 0 END) as total_tendik
    FROM guru");
mysqli_stmt_execute($stmt_count);
$res_count = mysqli_stmt_get_result($stmt_count);
$counts = mysqli_fetch_assoc($res_count);
mysqli_stmt_close($stmt_count);

$total_semua  = (int)($counts['total_semua'] ?? 0);
$total_guru   = (int)($counts['total_guru'] ?? 0);
$total_tendik = (int)($counts['total_tendik'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    render_seo_tags([
        'title'       => 'Guru & Tenaga Kependidikan - SMKS Prasetya Makassar',
        'description' => 'Daftar pendidik profesional dan staf tenaga kependidikan berdedikasi di SMKS Prasetya Makassar yang berkomitmen mencetak lulusan berkarakter dan berdaya saing global.',
        'keywords'    => 'Guru SMKS Prasetya Makassar, Tenaga Kependidikan SMKS Prasetya, Pendidik SMK Makassar, Guru RPL Makassar, Guru TKJ Makassar, Guru Produktif SMK',
        'type'        => 'website',
        'image'       => 'assets/uploads/logo-smks-prasetya.png',
    ]);
    render_breadcrumb_schema([
        'Beranda' => 'index.php',
        'Guru & Tenaga Kependidikan' => 'guru.php'
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
                url('https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=1200&auto=format&fit=crop')
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

        .card-guru {
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

        .card-guru:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 32px rgba(30,60,114,0.14);
        }

        .guru-img-wrapper {
            position: relative;
            width: 100%;
            height: 270px;
            background-color: #f1f5f9;
            overflow: hidden;
        }

        .guru-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .card-guru:hover .guru-img-wrapper img {
            transform: scale(1.05);
        }

        .guru-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            font-size: 0.75rem;
            padding: 5px 12px;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            font-weight: 600;
        }

        .card-guru .card-body {
            padding: 22px 20px 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card-guru .nama-guru {
            font-size: 1.12rem;
            font-weight: 700;
            margin-bottom: 4px;
            color: #1e293b;
        }

        .card-guru .jabatan-guru {
            font-size: 0.88rem;
            font-weight: 600;
            color: #0d6efd;
            margin-bottom: 12px;
            display: inline-block;
        }

        .card-guru .deskripsi-guru {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.65;
            margin-bottom: 0;
            flex-grow: 1;
        }

        .filter-btn {
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: 0.2s;
        }

        .section-title {
            position: relative;
            margin-bottom: 35px;
        }

        .section-title h2 {
            font-weight: 700;
            color: #1e293b;
        }

        .section-title .divider {
            width: 70px;
            height: 4px;
            background: var(--accent-color);
            margin: 12px auto 0;
            border-radius: 2px;
        }

        /* Dark mode overrides for card guru */
        html[data-theme="dark"] .card-guru {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        html[data-theme="dark"] .card-guru .nama-guru {
            color: #f3f4f6 !important;
        }

        html[data-theme="dark"] .card-guru .jabatan-guru {
            color: #60a5fa !important;
        }

        html[data-theme="dark"] .card-guru .deskripsi-guru {
            color: #9ca3af !important;
        }

        html[data-theme="dark"] .guru-img-wrapper {
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
                    <a class="nav-link active fw-semibold" href="guru.php">Guru & Tendik</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="fasilitas.php">Fasilitas</a>
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
                <li class="breadcrumb-item active text-white" aria-current="page">Guru & Tenaga Kependidikan</li>
            </ol>
        </nav>

        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold text-uppercase mb-3">
            Pendidik & Tenaga Kependidikan
        </span>

        <h1 class="display-5 fw-bold mb-3 text-white">
            Guru & Tenaga Kependidikan
        </h1>

        <p class="lead text-white-50 col-lg-8 mx-auto mb-0">
            Mengenal jajaran pendidik profesional dan tenaga kependidikan berdedikasi di SMKS Prasetya Makassar yang berkomitmen mencetak generasi terampil, kompeten, dan berakhlak mulia.
        </p>
    </div>
</header>

<!-- Main Content: Daftar Guru & Tenaga Kependidikan -->
<main class="py-5 bg-light" id="daftar-guru">
    <div class="container py-2">

        <!-- Bar Pencarian & Filter Kategori -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 bg-white">
            <div class="row g-3 align-items-center">
                <!-- Tabs / Tombol Filter Kategori -->
                <div class="col-lg-7">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="guru.php<?= ($q !== '') ? '?q=' . urlencode($q) : ''; ?>"
                           class="btn filter-btn <?= ($kategori === '') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-grid-fill me-1"></i> Semua
                            <span class="badge <?= ($kategori === '') ? 'bg-light text-primary' : 'bg-secondary text-white'; ?> rounded-pill ms-1"><?= $total_semua; ?></span>
                        </a>

                        <a href="guru.php?kategori=Guru<?= ($q !== '') ? '&q=' . urlencode($q) : ''; ?>"
                           class="btn filter-btn <?= ($kategori === 'Guru') ? 'btn-success' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-mortarboard-fill me-1"></i> Guru / Pendidik
                            <span class="badge <?= ($kategori === 'Guru') ? 'bg-light text-success' : 'bg-secondary text-white'; ?> rounded-pill ms-1"><?= $total_guru; ?></span>
                        </a>

                        <a href="guru.php?kategori=Tenaga+Kependidikan<?= ($q !== '') ? '&q=' . urlencode($q) : ''; ?>"
                           class="btn filter-btn <?= ($kategori === 'Tenaga Kependidikan') ? 'btn-info text-white' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-person-badge-fill me-1"></i> Tenaga Kependidikan
                            <span class="badge <?= ($kategori === 'Tenaga Kependidikan') ? 'bg-light text-info' : 'bg-secondary text-white'; ?> rounded-pill ms-1"><?= $total_tendik; ?></span>
                        </a>
                    </div>
                </div>

                <!-- Input Pencarian -->
                <div class="col-lg-5">
                    <form action="guru.php" method="GET" class="d-flex gap-2">
                        <?php if ($kategori !== '') : ?>
                            <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori); ?>">
                        <?php endif; ?>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari nama guru atau jabatan..." value="<?= htmlspecialchars($q); ?>">
                            <button type="submit" class="btn btn-primary px-3">Cari</button>
                        </div>
                        <?php if ($q !== '' || $kategori !== '') : ?>
                            <a href="guru.php" class="btn btn-outline-secondary" title="Hapus Filter">
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
                    <a href="guru.php" class="text-decoration-none small text-danger fw-semibold">
                        <i class="bi bi-x-circle me-1"></i>Reset Pencarian
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Grid Kartu Guru & Tenaga Kependidikan -->
        <div class="row g-4">
            <?php
            $jml_tampil = 0;
            if ($result && mysqli_num_rows($result) > 0) :
                while ($guru = mysqli_fetch_assoc($result)) :
                    $jml_tampil++;
                    $foto_file = $guru['foto'] ?? '';
                    $foto_src = (!empty($foto_file) && file_exists(__DIR__ . '/assets/uploads/' . $foto_file))
                        ? 'assets/uploads/' . htmlspecialchars($foto_file)
                        : 'https://placehold.co/400x450?text=' . urlencode(substr($guru['nama'], 0, 2));

                    $is_tendik = (($guru['kategori'] ?? '') === 'Tenaga Kependidikan');
            ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-guru">
                        <!-- Wrapper Foto -->
                        <div class="guru-img-wrapper">
                            <img src="<?= $foto_src; ?>"
                                 alt="Foto <?= htmlspecialchars($guru['nama']); ?> - <?= htmlspecialchars($guru['jabatan']); ?> SMKS Prasetya Makassar"
                                 loading="lazy"
                                 decoding="async"
                                 onerror="this.src='https://placehold.co/400x450?text=Foto+Guru'">

                            <!-- Badge Kategori -->
                            <?php if ($is_tendik) : ?>
                                <span class="guru-badge bg-info text-white">
                                    <i class="bi bi-person-badge me-1"></i> Tendik
                                </span>
                            <?php else : ?>
                                <span class="guru-badge bg-success text-white">
                                    <i class="bi bi-mortarboard me-1"></i> Pendidik
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Konten Profil -->
                        <div class="card-body">
                            <h5 class="nama-guru">
                                <?= htmlspecialchars($guru['nama']); ?>
                            </h5>

                            <span class="jabatan-guru">
                                <i class="bi bi-briefcase me-1"></i><?= htmlspecialchars($guru['jabatan']); ?>
                            </span>

                            <p class="deskripsi-guru">
                                <?= !empty($guru['deskripsi']) ? nl2br(htmlspecialchars($guru['deskripsi'])) : 'Pendidik dan tenaga kependidikan berdedikasi di SMKS Prasetya Makassar.'; ?>
                            </p>
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
                        <i class="bi bi-people-fill fs-1 text-secondary opacity-50 d-block mb-3"></i>
                        <h4 class="fw-bold text-dark">Data Tidak Ditemukan</h4>
                        <p class="text-muted col-md-6 mx-auto mb-4">
                            <?php if ($q !== '') : ?>
                                Tidak ditemukan guru atau tenaga kependidikan dengan kata kunci "<strong><?= htmlspecialchars($q); ?></strong>".
                            <?php else : ?>
                                Saat ini belum ada data guru atau tenaga kependidikan yang terdaftar pada kategori yang dipilih.
                            <?php endif; ?>
                        </p>
                        <a href="guru.php" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Tampilkan Semua Guru & Tendik
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<!-- Hubungi Pihak Sekolah -->
<section class="py-5 bg-white border-top">
    <div class="container py-3 text-center">
        <div class="col-lg-8 mx-auto">
            <h3 class="fw-bold text-dark mb-2">Ingin Berkonsultasi Mengenai Program Kejuruan?</h3>
            <p class="text-secondary mb-4">
                Pendidik dan staf administrasi SMKS Prasetya Makassar siap melayani pertanyaan Anda seputar kurikulum, bimbingan siswa, dan pendaftaran peserta didik baru (PPDB).
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="index.php#kontak" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-chat-dots-fill me-1"></i> Hubungi Kami
                </a>
                <a href="index.php#jurusan" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-grid-fill me-1"></i> Lihat Program Keahlian
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
                    <li class="mb-2"><a href="guru.php" class="text-white text-decoration-none fw-semibold">Guru & Tendik</a></li>
                    <li class="mb-2"><a href="fasilitas.php" class="text-white-50 text-decoration-none">Fasilitas Sekolah</a></li>
                    <li class="mb-2"><a href="alumni.php" class="text-white-50 text-decoration-none">Alumni</a></li>
                    <li class="mb-2"><a href="index.php#berita" class="text-white-50 text-decoration-none">Berita</a></li>
                    <li class="mb-2"><a href="index.php#galeri" class="text-white-50 text-decoration-none">Galeri</a></li>
                    <li class="mb-2"><a href="index.php#kontak" class="text-white-50 text-decoration-none">Kontak Kami</a></li>
                </ul>
            </div>

            <div class="col-md-6 col-lg-4">
                <h6 class="fw-bold text-warning mb-3">Akses Administrator</h6>
                <p class="small text-white-50 mb-2">
                    Pengelola sekolah dapat masuk ke panel administrasi untuk mengelola data jurusan, berita, guru, dan pesan pengunjung.
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

