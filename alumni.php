<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/tracker.php';
require_once __DIR__ . '/config/seo.php';

// Catat kunjungan pengunjung ke halaman alumni
if (function_exists('catat_pengunjung')) {
    catat_pengunjung($conn, 'Alumni Sekolah');
}

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

// Parameter pencarian & filter
$q              = isset($_GET['q']) ? trim($_GET['q']) : '';
$jurusan_filter = isset($_GET['jurusan']) ? trim($_GET['jurusan']) : '';
$tahun_filter   = isset($_GET['tahun']) ? trim($_GET['tahun']) : '';

// Susun query prepared statement
$select_fields = "id, nama, $col_tahun AS tahun_lulus, $col_jurusan AS jurusan, $col_pekerjaan AS pekerjaan, foto" . ($has_testimoni ? ", testimoni" : "");

if ($q !== '' && $jurusan_filter !== '' && $tahun_filter !== '') {
    $param_kw = "%$q%";
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE (nama LIKE ? OR $col_pekerjaan LIKE ?) AND $col_jurusan = ? AND $col_tahun = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "ssss", $param_kw, $param_kw, $jurusan_filter, $tahun_filter);
} elseif ($q !== '' && $jurusan_filter !== '') {
    $param_kw = "%$q%";
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE (nama LIKE ? OR $col_pekerjaan LIKE ?) AND $col_jurusan = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "sss", $param_kw, $param_kw, $jurusan_filter);
} elseif ($q !== '' && $tahun_filter !== '') {
    $param_kw = "%$q%";
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE (nama LIKE ? OR $col_pekerjaan LIKE ?) AND $col_tahun = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "sss", $param_kw, $param_kw, $tahun_filter);
} elseif ($jurusan_filter !== '' && $tahun_filter !== '') {
    $stmt = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE $col_jurusan = ? AND $col_tahun = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "ss", $jurusan_filter, $tahun_filter);
} elseif ($q !== '') {
    $param_kw = "%$q%";
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

// Ambil total alumni via prepared statement
$stmt_tot = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM alumni");
mysqli_stmt_execute($stmt_tot);
$res_tot = mysqli_stmt_get_result($stmt_tot);
$total_semua = (int)(mysqli_fetch_assoc($res_tot)['total'] ?? 0);
mysqli_stmt_close($stmt_tot);

// Ambil daftar jurusan dan tahun untuk filter
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

    <?php
    render_seo_tags([
        'title'       => 'Jejak Alumni & Kiprah Lulusan - SMKS Prasetya Makassar',
        'description' => 'Jejak sukses dan kiprah lulusan SMKS Prasetya Makassar di dunia industri, startup teknologi, BUMN, perbankan, perhotelan, dan perguruan tinggi terkemuka.',
        'keywords'    => 'Alumni SMKS Prasetya Makassar, Lulusan SMK Prasetya, Jejak Karir Alumni SMK Makassar, Testimoni Alumni Prasetya, Karir Lulusan SMK',
        'type'        => 'website',
        'image'       => 'assets/uploads/logo-smks-prasetya.png',
    ]);
    render_breadcrumb_schema([
        'Beranda' => 'index.php',
        'Jejak Alumni' => 'alumni.php'
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
                url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=1200&auto=format&fit=crop')
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

        .card-alumni {
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

        .card-alumni:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 32px rgba(30,60,114,0.14);
        }

        .alumni-img-wrapper {
            position: relative;
            width: 100%;
            height: 260px;
            background-color: #f1f5f9;
            overflow: hidden;
            cursor: pointer;
        }

        .alumni-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .card-alumni:hover .alumni-img-wrapper img {
            transform: scale(1.05);
        }

        .alumni-year-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            font-size: 0.78rem;
            padding: 5px 12px;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            font-weight: 700;
        }

        .card-alumni .card-body {
            padding: 22px 20px 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card-alumni .nama-alumni {
            font-size: 1.12rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .card-alumni .jurusan-alumni {
            font-size: 0.82rem;
            font-weight: 600;
            color: #0d6efd;
            margin-bottom: 12px;
            display: inline-block;
        }

        .card-alumni .pekerjaan-box {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 12px;
            border-left: 3px solid #f59e0b;
        }

        .card-alumni .pekerjaan-title {
            font-size: 0.74rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 2px;
            display: block;
        }

        .card-alumni .pekerjaan-text {
            font-size: 0.88rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0;
            line-height: 1.4;
        }

        .card-alumni .testimoni-alumni {
            font-size: 0.84rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 0;
            flex-grow: 1;
            font-style: italic;
        }

        .filter-btn {
            border-radius: 30px;
            padding: 8px 18px;
            font-weight: 500;
            font-size: 0.88rem;
            transition: 0.2s;
        }

        /* Dark mode overrides */
        html[data-theme="dark"] .card-alumni {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        html[data-theme="dark"] .card-alumni .nama-alumni {
            color: #f3f4f6 !important;
        }

        html[data-theme="dark"] .card-alumni .jurusan-alumni {
            color: #60a5fa !important;
        }

        html[data-theme="dark"] .card-alumni .pekerjaan-box {
            background-color: #111827 !important;
        }

        html[data-theme="dark"] .card-alumni .pekerjaan-text {
            color: #f3f4f6 !important;
        }

        html[data-theme="dark"] .card-alumni .testimoni-alumni {
            color: #9ca3af !important;
        }

        html[data-theme="dark"] .alumni-img-wrapper {
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
                    <a class="nav-link" href="fasilitas.php">Fasilitas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="alumni.php">Alumni</a>
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
                <li class="breadcrumb-item active text-white" aria-current="page">Alumni Sekolah</li>
            </ol>
        </nav>

        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold text-uppercase mb-3">
            Inspirasi & Jejak Karier Lulusan
        </span>

        <h1 class="display-5 fw-bold mb-3 text-white">
            Alumni SMKS Prasetya Makassar
        </h1>

        <p class="lead text-white-50 col-lg-8 mx-auto mb-0">
            Kisah sukses, kiprah profesional, dan jejak karier alumni SMKS Prasetya Makassar yang telah berkontribusi nyata di dunia kerja, industri teknologi, perbankan, dan wirausaha mandiri.
        </p>
    </div>
</header>

<!-- Main Content: Daftar Alumni -->
<main class="py-5 bg-light" id="daftar-alumni">
    <div class="container py-2">

        <!-- Bar Pencarian & Filter -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 bg-white">
            <form action="alumni.php" method="GET" class="row g-3 align-items-center">
                <!-- Input Pencarian -->
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari nama alumni atau profesi..." value="<?= htmlspecialchars($q); ?>">
                    </div>
                </div>

                <!-- Filter Jurusan -->
                <div class="col-md-4">
                    <select name="jurusan" class="form-select">
                        <option value="">Semua Program Keahlian</option>
                        <?php foreach ($list_jurusan as $j_item) : ?>
                            <option value="<?= htmlspecialchars($j_item); ?>" <?= ($jurusan_filter === $j_item) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($j_item); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Tahun Kelulusan -->
                <div class="col-md-2">
                    <select name="tahun" class="form-select">
                        <option value="">Semua Tahun</option>
                        <?php foreach ($list_tahun as $t_item) : ?>
                            <option value="<?= htmlspecialchars($t_item); ?>" <?= ($tahun_filter === $t_item) ? 'selected' : ''; ?>>
                                Lulus <?= htmlspecialchars($t_item); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tombol Submit & Reset -->
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" title="Cari / Filter">
                        <i class="bi bi-filter"></i>
                    </button>
                    <?php if ($q !== '' || $jurusan_filter !== '' || $tahun_filter !== '') : ?>
                        <a href="alumni.php" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($q !== '' || $jurusan_filter !== '' || $tahun_filter !== '') : ?>
                <div class="mt-3 pt-3 border-top small text-muted d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        Menampilkan hasil pencarian: 
                        <?php if ($jurusan_filter !== '') : ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1"><?= htmlspecialchars($jurusan_filter); ?></span>
                        <?php endif; ?>
                        <?php if ($tahun_filter !== '') : ?>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle me-1">Tahun <?= htmlspecialchars($tahun_filter); ?></span>
                        <?php endif; ?>
                        <?php if ($q !== '') : ?>
                            <span>Kata kunci: "<strong><?= htmlspecialchars($q); ?></strong>"</span>
                        <?php endif; ?>
                    </div>
                    <a href="alumni.php" class="text-decoration-none small text-danger fw-semibold">
                        <i class="bi bi-x-circle me-1"></i>Reset Pencarian
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Grid Kartu Alumni -->
        <div class="row g-4">
            <?php
            $jml_alumni = 0;
            if ($result && mysqli_num_rows($result) > 0) :
                while ($al = mysqli_fetch_assoc($result)) :
                    $jml_alumni++;
                    $foto_file = $al['foto'] ?? '';
                    $foto_src = (!empty($foto_file) && file_exists(__DIR__ . '/assets/uploads/' . $foto_file))
                        ? 'assets/uploads/' . htmlspecialchars($foto_file)
                        : 'https://placehold.co/400x450?text=' . urlencode(substr($al['nama'], 0, 2));
            ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-alumni">
                        <!-- Wrapper Foto -->
                        <div class="alumni-img-wrapper" data-bs-toggle="modal" data-bs-target="#modalAlumni<?= (int)$al['id']; ?>" title="Klik untuk melihat profil lengkap">
                            <img src="<?= $foto_src; ?>"
                                 alt="Foto Alumni <?= htmlspecialchars($al['nama']); ?> - Lulusan SMKS Prasetya Makassar"
                                 loading="lazy"
                                 decoding="async"
                                 onerror="this.src='https://placehold.co/400x450?text=Foto+Alumni'">

                            <!-- Badge Tahun Lulus -->
                            <span class="alumni-year-badge bg-warning text-dark">
                                <i class="bi bi-calendar3 me-1"></i> Lulus <?= htmlspecialchars($al['tahun_lulus']); ?>
                            </span>
                        </div>

                        <!-- Konten Alumni -->
                        <div class="card-body">
                            <h5 class="nama-alumni text-truncate" title="<?= htmlspecialchars($al['nama']); ?>">
                                <?= htmlspecialchars($al['nama']); ?>
                            </h5>

                            <span class="jurusan-alumni text-truncate d-block" title="<?= htmlspecialchars($al['jurusan']); ?>">
                                <i class="bi bi-mortarboard-fill me-1"></i><?= htmlspecialchars($al['jurusan']); ?>
                            </span>

                            <div class="pekerjaan-box">
                                <span class="pekerjaan-title">Pekerjaan / Aktivitas:</span>
                                <p class="pekerjaan-text">
                                    <i class="bi bi-briefcase text-warning me-1"></i>
                                    <?= htmlspecialchars($al['pekerjaan']); ?>
                                </p>
                            </div>

                            <?php if (!empty($al['testimoni'])) : ?>
                                <p class="testimoni-alumni text-truncate-3" style="display:-webkit-box;line-clamp:3;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                                    "<?= htmlspecialchars($al['testimoni']); ?>"
                                </p>
                            <?php else : ?>
                                <p class="testimoni-alumni text-muted small">
                                    Alumni berprestasi lulusan SMKS Prasetya Makassar.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Modal Detail Alumni -->
                <div class="modal fade" id="modalAlumni<?= (int)$al['id']; ?>" tabindex="-1" aria-labelledby="modalLabel<?= (int)$al['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header bg-custom-navy text-white border-0 py-3">
                                <h5 class="modal-title fw-bold" id="modalLabel<?= (int)$al['id']; ?>">
                                    <i class="bi bi-person-circle me-2 text-warning"></i>Profil Alumni
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4 text-center">
                                <img src="<?= $foto_src; ?>"
                                     alt="Foto Profil Alumni <?= htmlspecialchars($al['nama']); ?> - SMKS Prasetya Makassar"
                                     class="rounded-circle shadow-sm mb-3 object-fit-cover"
                                     width="130"
                                     height="130"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.src='https://placehold.co/130x130?text=Alumni'">

                                <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($al['nama']); ?></h4>
                                <span class="badge bg-primary rounded-pill px-3 py-1 mb-3"><?= htmlspecialchars($al['jurusan']); ?></span>

                                <div class="bg-light p-3 rounded-3 text-start mb-3 border">
                                    <div class="row g-2 small">
                                        <div class="col-5 text-muted fw-semibold">Tahun Lulus:</div>
                                        <div class="col-7 fw-bold text-dark"><?= htmlspecialchars($al['tahun_lulus']); ?></div>

                                        <div class="col-5 text-muted fw-semibold">Pekerjaan / Karier:</div>
                                        <div class="col-7 fw-bold text-primary"><?= htmlspecialchars($al['pekerjaan']); ?></div>
                                    </div>
                                </div>

                                <?php if (!empty($al['testimoni'])) : ?>
                                    <div class="p-3 bg-warning-subtle rounded-3 text-start border border-warning-subtle">
                                        <small class="fw-bold text-dark d-block mb-1"><i class="bi bi-chat-quote-fill me-1 text-warning"></i>Kesan & Pesan:</small>
                                        <p class="small text-secondary mb-0 font-italic">"<?= nl2br(htmlspecialchars($al['testimoni'])); ?>"</p>
                                    </div>
                                <?php endif; ?>
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
                        <i class="bi bi-mortarboard fs-1 text-secondary opacity-50 d-block mb-3"></i>
                        <h4 class="fw-bold text-dark">Data Alumni Tidak Ditemukan</h4>
                        <p class="text-muted col-md-6 mx-auto mb-4">
                            <?php if ($q !== '' || $jurusan_filter !== '' || $tahun_filter !== '') : ?>
                                Tidak ditemukan data alumni yang sesuai dengan kriteria filter pencarian Anda.
                            <?php else : ?>
                                Belum ada data alumni yang dipublikasikan saat ini.
                            <?php endif; ?>
                        </p>
                        <a href="alumni.php" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Tampilkan Semua Alumni
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<!-- Hubungi IKA Alumni -->
<section class="py-5 bg-white border-top">
    <div class="container py-3 text-center">
        <div class="col-lg-8 mx-auto">
            <h3 class="fw-bold text-dark mb-2">Anda Alumni SMKS Prasetya Makassar?</h3>
            <p class="text-secondary mb-4">
                Mari jalin silaturahmi, berbagi pengalaman karier, dan bangun jejaring profesional bersama Ikatan Alumni (IKA) SMKS Prasetya Makassar.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="index.php#kontak" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-chat-dots-fill me-1"></i> Kirim Kabar Alumni
                </a>
                <a href="index.php#jurusan" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-grid-fill me-1"></i> Program Keahlian
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
                    <li class="mb-2"><a href="fasilitas.php" class="text-white-50 text-decoration-none">Fasilitas Sekolah</a></li>
                    <li class="mb-2"><a href="alumni.php" class="text-white text-decoration-none fw-semibold">Alumni</a></li>
                    <li class="mb-2"><a href="index.php#berita" class="text-white-50 text-decoration-none">Berita</a></li>
                    <li class="mb-2"><a href="index.php#galeri" class="text-white-50 text-decoration-none">Galeri</a></li>
                    <li class="mb-2"><a href="index.php#kontak" class="text-white-50 text-decoration-none">Kontak Kami</a></li>
                </ul>
            </div>

            <div class="col-md-6 col-lg-4">
                <h6 class="fw-bold text-warning mb-3">Akses Administrator</h6>
                <p class="small text-white-50 mb-2">
                    Pengelola sekolah dapat masuk ke panel administrasi untuk mengelola data jurusan, berita, guru, fasilitas, alumni, dan pesan pengunjung.
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

