
<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/tracker.php';
require_once __DIR__ . '/config/seo.php';

// Catat kunjungan pengunjung ke halaman beranda
catat_pengunjung($conn, 'Beranda Utama');

$pesan_status = "";

// Proses pengiriman pesan
if (isset($_POST['kirim_pesan'])) {
    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $subjek = trim($_POST['subjek'] ?? '');
    $pesan  = trim($_POST['pesan'] ?? '');

    if ($nama !== '' && $email !== '' && $subjek !== '' && $pesan !== '') {
        $nama_db   = mysqli_real_escape_string($conn, $nama);
        $email_db  = mysqli_real_escape_string($conn, $email);
        $subjek_db = mysqli_real_escape_string($conn, $subjek);
        $pesan_db  = mysqli_real_escape_string($conn, $pesan);

        $insert = mysqli_query(
            $conn,
            "INSERT INTO pesan
            (nama, email, subjek, pesan, isi_pesan, status)
            VALUES
            ('$nama_db', '$email_db', '$subjek_db', '$pesan_db', '$pesan_db', 'belum dibaca')"
        );

        $pesan_status = $insert ? "sukses" : "gagal";
    } else {
        $pesan_status = "kosong";
    }
}

// Ambil data jurusan
$q_jurusan = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY id ASC");

// Ambil data guru & tenaga kependidikan menggunakan prepared statement (tanpa mengasumsikan kolom created_at)
$q_guru = null;
$stmt_guru_home = mysqli_prepare($conn, "SELECT id, nama, jabatan, kategori, deskripsi, foto FROM guru ORDER BY id ASC LIMIT 8");
if ($stmt_guru_home) {
    mysqli_stmt_execute($stmt_guru_home);
    $q_guru = mysqli_stmt_get_result($stmt_guru_home);
}

// Ambil data fasilitas sekolah menggunakan prepared statement (Ketentuan 13)
$col_nama_fas = 'nama_fasilitas';
$check_col_fas = mysqli_query($conn, "SHOW COLUMNS FROM fasilitas LIKE 'nama_fasilitas'");
if (!$check_col_fas || mysqli_num_rows($check_col_fas) == 0) {
    $col_nama_fas = 'nama';
}
$q_fasilitas = null;
$stmt_fas_home = mysqli_prepare($conn, "SELECT id, $col_nama_fas AS nama_fasilitas, kategori, deskripsi, foto FROM fasilitas ORDER BY id ASC LIMIT 6");
if ($stmt_fas_home) {
    mysqli_stmt_execute($stmt_fas_home);
    $q_fasilitas = mysqli_stmt_get_result($stmt_fas_home);
}

// Ambil data alumni sekolah menggunakan prepared statement (Ketentuan: prepared statement & periksa kolom)
$col_pekerjaan_home = 'pekerjaan';
$check_col_pek = mysqli_query($conn, "SHOW COLUMNS FROM alumni LIKE 'pekerjaan'");
if (!$check_col_pek || mysqli_num_rows($check_col_pek) == 0) {
    $col_pekerjaan_home = 'aktivitas';
}
$q_alumni = null;
$stmt_alumni_home = mysqli_prepare($conn, "SELECT id, nama, tahun_lulus, jurusan, $col_pekerjaan_home AS pekerjaan, foto, testimoni FROM alumni ORDER BY id DESC LIMIT 4");
if ($stmt_alumni_home) {
    mysqli_stmt_execute($stmt_alumni_home);
    $q_alumni = mysqli_stmt_get_result($stmt_alumni_home);
}

// Ambil berita terbaru
$q_berita = mysqli_query($conn, "SELECT * FROM berita ORDER BY tanggal DESC LIMIT 6");

// Ambil galeri terbaru
$q_galeri = mysqli_query($conn, "SELECT * FROM galeri ORDER BY id DESC LIMIT 6");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    render_seo_tags([
        'title'       => 'SMKS Prasetya Makassar - Sekolah Menengah Kejuruan Unggul di Makassar',
        'description' => 'Website resmi SMKS Prasetya Makassar. Sekolah Menengah Kejuruan swasta terakreditasi di Tamalanrea, Makassar dengan program keahlian TKJ, RPL, TBSM, OTKP, dan AKL siap kerja.',
        'keywords'    => 'SMKS Prasetya Makassar, SMK Prasetya, SMK Makassar, SMK Tamalanrea, PPDB SMKS Prasetya, Jurusan RPL Makassar, Jurusan TKJ Makassar, TBSM Makassar, Sekolah Kejuruan Makassar',
        'type'        => 'website',
        'image'       => 'assets/uploads/logo-smks-prasetya.png',
    ]);
    render_school_schema();
    ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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

        .hero-section {
            background:
                linear-gradient(rgba(15, 23, 42, 0.85), rgba(30, 60, 114, 0.9)),
                url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1200&auto=format&fit=crop')
                center/cover no-repeat;
            padding: 120px 0 100px;
            color: #fff;
        }

        .section-title {
            position: relative;
            margin-bottom: 45px;
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

        .card-jurusan,
        .card-berita {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            background: #fff;
            height: 100%;
        }

        .card-jurusan:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(30,60,114,0.12);
        }

        .card-berita:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 25px rgba(0,0,0,0.1);
        }

        .jurusan-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
            background: #eff6ff;
            color: var(--primary-color);
        }

        .card-berita {
            overflow: hidden;
        }

        .card-berita img {
            height: 220px;
            object-fit: cover;
            width: 100%;
            transition: transform 0.4s ease;
        }

        .card-berita:hover img {
            transform: scale(1.04);
        }

        .contact-box {
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .info-card {
            border: none;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            height: 100%;
        }

        .info-card i {
            font-size: 30px;
            color: var(--primary-color);
        }

        .card-galeri {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            height: 100%;
            transition: all 0.3s ease;
        }

        .card-galeri:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 25px rgba(0,0,0,0.1);
        }

        .card-galeri img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .card-galeri:hover img {
            transform: scale(1.04);
        }

        .card-alumni {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            background: #fff;
            overflow: hidden;
        }

        .card-alumni:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 30px rgba(30,60,114,0.12);
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

@media (max-width: 576px) {
    .navbar-logo {
        width: 40px;
        height: 40px;
    }

    .navbar-brand span {
        font-size: 13px;
    }
}
    </style>

    <!-- CSS Tema -->
    <link rel="stylesheet" href="assets/css/theme.css">

    <!-- JavaScript Tema -->
    <script src="assets/js/theme.js"></script>
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

        <a class="navbar-brand d-flex align-items-center fw-bold" href="#beranda">
            <img
                src="assets/uploads/logo-smks-prasetya.png"
                alt="Logo Resmi SMKS Prasetya Makassar"
                class="navbar-logo"
                width="48"
                height="48"
                decoding="async">

            <span>SMKS PRASETYA MAKASSAR</span>
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="mainNav">

            <!-- Tombol Dark Mode -->
            <button type="button"
                    id="themeToggle"
                    class="btn btn-outline-light btn-sm me-3 mb-2 mb-lg-0">
                <i class="bi bi-moon-stars-fill me-1"></i> Dark Mode
            </button>

            <ul class="navbar-nav me-3 mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="#beranda">Beranda</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#profil">Profil Sekolah</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#jurusan">Jurusan</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#guru">Guru & Tendik</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#fasilitas">Fasilitas</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#alumni">Alumni</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#berita">Berita</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#galeri">Galeri</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#kontak">Kontak</a>
                </li>
            </ul>

            <a href="login.php" class="btn btn-warning text-dark fw-bold btn-sm px-3 rounded-pill shadow-sm">
                <i class="bi bi-lock-fill me-1"></i> Login Admin
            </a>

        </div>
    </div>
</nav>

<!-- Hero -->
<header class="hero-section text-center" id="beranda">
    <div class="container">

        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold text-uppercase mb-3">
            Sekolah Menengah Kejuruan Swasta
        </span>

        <h1 class="display-4 fw-bold mb-3 text-white">
            SMKS PRASETYA MAKASSAR
        </h1>

        <p class="lead text-white-50 col-lg-8 mx-auto mb-4">
            Selamat datang di website resmi SMKS Prasetya Makassar.
            Temukan informasi sekolah, program keahlian, berita,
            dan berbagai informasi pendidikan di sini.
        </p>

        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="#jurusan" class="btn btn-warning btn-lg px-4 fw-bold text-dark rounded-pill shadow">
                <i class="bi bi-grid-fill me-2"></i>Program Keahlian
            </a>

            <a href="#kontak" class="btn btn-outline-light btn-lg px-4 rounded-pill">
                <i class="bi bi-chat-dots-fill me-2"></i>Hubungi Kami
            </a>
        </div>

    </div>
</header>

<!-- Informasi Sekolah -->
<section class="py-5 bg-white border-bottom shadow-sm">
    <div class="container">
        <div class="row g-4 text-center">

            <div class="col-6 col-md-3">
                <div class="info-card p-3">
                    <i class="bi bi-building"></i>
                    <h5 class="fw-bold mt-2 mb-1">SMK</h5>
                    <p class="text-muted small mb-0">Bentuk Pendidikan</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="info-card p-3">
                    <i class="bi bi-person-badge"></i>
                    <h5 class="fw-bold mt-2 mb-1">Swasta</h5>
                    <p class="text-muted small mb-0">Status Sekolah</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="info-card p-3">
                    <i class="bi bi-card-checklist"></i>
                    <h5 class="fw-bold mt-2 mb-1">40316697</h5>
                    <p class="text-muted small mb-0">NPSN</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="info-card p-3">
                    <i class="bi bi-calendar-week"></i>
                    <h5 class="fw-bold mt-2 mb-1">5 Hari</h5>
                    <p class="text-muted small mb-0">Hari Sekolah</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Profil Sekolah -->
<section class="py-5 bg-light" id="profil">
    <div class="container py-4">

        <div class="section-title text-center">
            <span class="text-primary fw-semibold small text-uppercase">
                Mengenal Lebih Dekat
            </span>

            <h2 class="fw-bold">Profil SMKS Prasetya Makassar</h2>
            <div class="divider"></div>
        </div>

        <div class="row g-4 align-items-center">

            <div class="col-lg-7">
                <div class="pe-lg-4">

                    <h3 class="fw-bold text-dark mb-3">
                        SMKS Prasetya Makassar
                    </h3>

                    <p class="text-secondary" style="line-height: 1.8;">
                        SMKS Prasetya Makassar merupakan sekolah menengah
                        kejuruan swasta yang berlokasi di Kota Makassar,
                        Provinsi Sulawesi Selatan.
                    </p>

                    <p class="text-secondary" style="line-height: 1.8;">
                        Sekolah ini beralamat di Jalan Buntusu NHP / BTP
                        Nomor 17 Makassar, Kecamatan Tamalanrea.
                        Kegiatan pembelajaran berlangsung sehari penuh
                        selama lima hari dalam satu minggu.
                    </p>

                    <div class="row g-3 mt-3">

                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                                <span class="fw-medium">Status Swasta</span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                                <span class="fw-medium">Jenjang Pendidikan SMK</span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                                <span class="fw-medium">Kecamatan Tamalanrea</span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                                <span class="fw-medium">Kota Makassar</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4 bg-white">

                    <h4 class="fw-bold text-primary mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Identitas Sekolah
                    </h4>

                    <div class="mb-3">
                        <small class="text-muted">Nama Sekolah</small>
                        <p class="fw-semibold mb-0">SMKS Prasetya Makassar</p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">NPSN</small>
                        <p class="fw-semibold mb-0">40316697</p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Kepala Sekolah</small>
                        <p class="fw-semibold mb-0">Rizkiani Razak</p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Status Sekolah</small>
                        <p class="fw-semibold mb-0">Swasta</p>
                    </div>

                    <div class="mb-0">
                        <small class="text-muted">Bentuk Pendidikan</small>
                        <p class="fw-semibold mb-0">Sekolah Menengah Kejuruan</p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- Program Jurusan -->
<section class="py-5 bg-white" id="jurusan">
    <div class="container py-4">

        <div class="section-title text-center">
            <span class="text-primary fw-semibold small text-uppercase">
                Informasi Pendidikan
            </span>

            <h2 class="fw-bold">Program Keahlian / Jurusan</h2>
            <div class="divider"></div>

            <p class="text-muted mt-2">
                Informasi program keahlian yang tersedia di SMKS Prasetya Makassar.
            </p>
        </div>

        <div class="row g-4">

            <?php if ($q_jurusan && mysqli_num_rows($q_jurusan) > 0) : ?>

                <?php while ($j = mysqli_fetch_assoc($q_jurusan)) : ?>

                    <div class="col-md-6 col-lg-4">
                        <div class="card card-jurusan p-4">

                            <div class="d-flex justify-content-between align-items-start">

                                <div class="jurusan-icon">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>

                                <?php if (!empty($j['singkatan'])) : ?>
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill">
                                        <?= htmlspecialchars($j['singkatan']); ?>
                                    </span>
                                <?php endif; ?>

                            </div>

                            <h4 class="fw-bold text-dark mb-2">
                                <?= htmlspecialchars($j['nama_jurusan']); ?>
                            </h4>

                            <p class="text-secondary small mb-0" style="line-height: 1.7;">
                                <?= nl2br(htmlspecialchars($j['deskripsi'])); ?>
                            </p>

                        </div>
                    </div>

                <?php endwhile; ?>

            <?php else : ?>

                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 text-secondary d-block mb-2"></i>
                    <p>Data program keahlian belum tersedia.</p>
                </div>

            <?php endif; ?>

        </div>
    </div>
</section>

<!-- Guru & Tenaga Kependidikan -->
<section class="py-5 bg-white border-top" id="guru">
    <div class="container py-4">

        <div class="section-title text-center">
            <span class="text-primary fw-semibold small text-uppercase">
                Pendidik & Tenaga Kependidikan
            </span>

            <h2 class="fw-bold">Guru & Tenaga Kependidikan</h2>
            <div class="divider"></div>

            <p class="text-muted mt-2">
                Mengenal jajaran guru dan tenaga kependidikan SMKS Prasetya Makassar.
            </p>
        </div>

        <div class="row g-4">
            <?php if ($q_guru && mysqli_num_rows($q_guru) > 0) : ?>
                <?php while ($g = mysqli_fetch_assoc($q_guru)) : 
                    $g_foto = $g['foto'] ?? '';
                    $g_src = (!empty($g_foto) && file_exists(__DIR__ . '/assets/uploads/' . $g_foto))
                        ? 'assets/uploads/' . htmlspecialchars($g_foto)
                        : 'https://placehold.co/400x450?text=' . urlencode(substr($g['nama'], 0, 2));
                    $is_tendik = (($g['kategori'] ?? '') === 'Tenaga Kependidikan');
                ?>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-guru h-100">
                            <div class="guru-img-wrapper position-relative" style="height: 250px; overflow: hidden; background: #f1f5f9;">
                                <img src="<?= $g_src; ?>"
                                     alt="Foto <?= htmlspecialchars($g['nama']); ?> - Pendidik SMKS Prasetya Makassar"
                                     class="w-100 h-100 object-fit-cover"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.src='https://placehold.co/400x450?text=Foto+Guru'">
                                <?php if ($is_tendik) : ?>
                                    <span class="position-absolute top-0 end-0 m-3 badge bg-info text-white shadow-sm rounded-pill px-3 py-1 small">
                                        <i class="bi bi-person-badge me-1"></i> Tendik
                                    </span>
                                <?php else : ?>
                                    <span class="position-absolute top-0 end-0 m-3 badge bg-success text-white shadow-sm rounded-pill px-3 py-1 small">
                                        <i class="bi bi-mortarboard me-1"></i> Guru
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-3 d-flex flex-column">
                                <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($g['nama']); ?></h6>
                                <small class="text-primary fw-semibold mb-2 d-block"><?= htmlspecialchars($g['jabatan']); ?></small>
                                <p class="text-secondary small mb-0 flex-grow-1" style="display:-webkit-box;line-clamp:3;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                                    <?= htmlspecialchars($g['deskripsi'] ?? 'Pendidik berdedikasi SMKS Prasetya Makassar.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 text-secondary d-block mb-2"></i>
                    <p>Data guru dan tenaga kependidikan belum tersedia.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4 pt-2">
            <a href="guru.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                Lihat Seluruh Guru & Tenaga Kependidikan <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>

<!-- Fasilitas Sekolah -->
<section class="py-5 bg-light border-top" id="fasilitas">
    <div class="container py-4">

        <div class="section-title text-center">
            <span class="text-primary fw-semibold small text-uppercase">
                Sarana & Prasarana
            </span>

            <h2 class="fw-bold">Fasilitas SMKS Prasetya Makassar</h2>
            <div class="divider"></div>

            <p class="text-muted mt-2">
                Fasilitas pembelajaran representatif dan sarana praktikum kejuruan standar industri.
            </p>
        </div>

        <div class="row g-4">
            <?php if ($q_fasilitas && mysqli_num_rows($q_fasilitas) > 0) : ?>
                <?php while ($f = mysqli_fetch_assoc($q_fasilitas)) : 
                    $f_foto = $f['foto'] ?? '';
                    $f_src = (!empty($f_foto) && file_exists(__DIR__ . '/assets/uploads/' . $f_foto))
                        ? 'assets/uploads/' . htmlspecialchars($f_foto)
                        : 'https://placehold.co/600x400?text=' . urlencode($f['nama_fasilitas']);

                    $badge_f_bg = 'bg-primary';
                    $kat_f_lower = strtolower($f['kategori'] ?? '');
                    if (strpos($kat_f_lower, 'lab') !== false || strpos($kat_f_lower, 'praktik') !== false) {
                        $badge_f_bg = 'bg-danger';
                    } elseif (strpos($kat_f_lower, 'belajar') !== false || strpos($kat_f_lower, 'kelas') !== false || strpos($kat_f_lower, 'pustaka') !== false) {
                        $badge_f_bg = 'bg-success';
                    } elseif (strpos($kat_f_lower, 'olahraga') !== false) {
                        $badge_f_bg = 'bg-warning text-dark';
                    } elseif (strpos($kat_f_lower, 'penunjang') !== false || strpos($kat_f_lower, 'ibadah') !== false) {
                        $badge_f_bg = 'bg-info text-white';
                    }
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-fasilitas h-100">
                            <div class="fasilitas-img-wrapper position-relative" style="height: 220px; overflow: hidden; background: #f1f5f9;">
                                <img src="<?= $f_src; ?>"
                                     alt="Fasilitas <?= htmlspecialchars($f['nama_fasilitas']); ?> - SMKS Prasetya Makassar"
                                     class="w-100 h-100 object-fit-cover"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.src='https://placehold.co/600x400?text=Fasilitas+Sekolah'">
                                <span class="position-absolute top-0 end-0 m-3 badge <?= $badge_f_bg; ?> shadow-sm rounded-pill px-3 py-1 small">
                                    <i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($f['kategori'] ?? 'Umum'); ?>
                                </span>
                            </div>
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($f['nama_fasilitas']); ?></h5>
                                <p class="text-secondary small mb-0 flex-grow-1" style="display:-webkit-box;line-clamp:3;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;line-height:1.7;">
                                    <?= htmlspecialchars($f['deskripsi'] ?? 'Fasilitas sarana prasarana SMKS Prasetya Makassar.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-building fs-1 text-secondary d-block mb-2"></i>
                    <p>Data fasilitas sekolah belum tersedia.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4 pt-2">
            <a href="fasilitas.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                Lihat Seluruh Fasilitas Sekolah <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>

<!-- Jejak Alumni Sekolah -->
<section class="py-5 bg-white border-top" id="alumni">
    <div class="container py-4">

        <div class="section-title text-center">
            <span class="text-primary fw-semibold small text-uppercase">
                Kiprah & Prestasi Lulusan
            </span>

            <h2 class="fw-bold">Jejak Alumni SMKS Prasetya Makassar</h2>
            <div class="divider"></div>

            <p class="text-muted mt-2">
                Membuktikan kompetensi vokasi unggul, siap kerja, wirausaha, dan berdaya saing di industri.
            </p>
        </div>

        <div class="row g-4">
            <?php if ($q_alumni && mysqli_num_rows($q_alumni) > 0) : ?>
                <?php while ($al = mysqli_fetch_assoc($q_alumni)) : 
                    $al_foto = $al['foto'] ?? '';
                    $al_src = (!empty($al_foto) && file_exists(__DIR__ . '/assets/uploads/' . $al_foto))
                        ? 'assets/uploads/' . htmlspecialchars($al_foto)
                        : 'https://placehold.co/400x500?text=' . urlencode($al['nama']);
                ?>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-alumni h-100 d-flex flex-column">
                            <div class="position-relative" style="height: 250px; overflow: hidden; background: #f1f5f9;">
                                <img src="<?= $al_src; ?>"
                                     alt="Foto Alumni <?= htmlspecialchars($al['nama']); ?> - Lulusan SMKS Prasetya Makassar"
                                     class="w-100 h-100 object-fit-cover"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.src='https://placehold.co/400x500?text=Foto+Alumni'">
                                <span class="position-absolute top-0 end-0 m-3 badge bg-primary shadow-sm rounded-pill px-3 py-1 small">
                                    <i class="bi bi-mortarboard-fill me-1"></i> Lulus <?= htmlspecialchars($al['tahun_lulus']); ?>
                                </span>
                            </div>
                            <div class="p-4 d-flex flex-column flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($al['nama']); ?></h6>
                                <span class="text-primary small fw-semibold mb-2 d-block">
                                    <?= htmlspecialchars($al['jurusan']); ?>
                                </span>
                                <div class="p-2 rounded bg-light border-start border-3 border-warning mb-2">
                                    <small class="text-muted d-block" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700;">Aktivitas / Pekerjaan:</small>
                                    <small class="text-dark fw-semibold" style="display:-webkit-box;line-clamp:2;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                        <?= htmlspecialchars($al['pekerjaan']); ?>
                                    </small>
                                </div>
                                <?php if (!empty($al['testimoni'])) : ?>
                                    <p class="text-secondary small fst-italic mb-0 mt-auto" style="display:-webkit-box;line-clamp:2;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;font-size:0.8rem;">
                                        "<?= htmlspecialchars($al['testimoni']); ?>"
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-mortarboard fs-1 text-secondary d-block mb-2"></i>
                    <p>Data alumni sekolah belum tersedia.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4 pt-2">
            <a href="alumni.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                Lihat Seluruh Jejak Alumni <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>

<!-- Berita -->
<section class="py-5 bg-light" id="berita">
    <div class="container py-4">

        <div class="section-title text-center">
            <span class="text-primary fw-semibold small text-uppercase">
                Informasi Terkini
            </span>

            <h2 class="fw-bold">Berita & Kegiatan Sekolah</h2>
            <div class="divider"></div>

            <p class="text-muted mt-2">
                Informasi berita dan kegiatan terbaru SMKS Prasetya Makassar.
            </p>
        </div>

        <div class="row g-4">

            <?php if ($q_berita && mysqli_num_rows($q_berita) > 0) : ?>

                <?php while ($b = mysqli_fetch_assoc($q_berita)) : ?>

                    <div class="col-md-6 col-lg-4">
                        <div class="card card-berita">

                            <div class="position-relative overflow-hidden">

                                <img
                                    src="assets/uploads/<?= htmlspecialchars($b['gambar'] ?? ''); ?>"
                                    alt="<?= htmlspecialchars($b['judul'] ?? 'Berita Sekolah'); ?> - SMKS Prasetya Makassar"
                                    class="w-100 object-fit-cover"
                                    loading="lazy"
                                    decoding="async"
                                    onerror="this.src='https://placehold.co/600x400?text=SMKS+Prasetya'">

                                <span class="position-absolute top-0 end-0 bg-primary text-white small px-3 py-1 m-3 rounded-pill shadow-sm">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= !empty($b['tanggal']) ? date('d M Y', strtotime($b['tanggal'])) : '-'; ?>
                                </span>

                            </div>

                            <div class="card-body p-4 d-flex flex-column">

                                <div class="text-muted small mb-2">
                                    <i class="bi bi-person me-1"></i>
                                    Oleh: <?= htmlspecialchars($b['penulis'] ?? '-'); ?>
                                </div>

                                <h5 class="fw-bold text-dark mb-2">
                                    <?= htmlspecialchars($b['judul'] ?? ''); ?>
                                </h5>

                              <p class="text-secondary small flex-grow-1"
                                style="display:-webkit-box;line-clamp:3;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                              </p>

                                <div class="mt-3 pt-3 border-top">
                                    <a href="detail-berita.php?id=<?= (int)$b['id']; ?>"
                                       class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold">
                                        Baca Selengkapnya
                                        <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>

            <?php else : ?>

                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-1 text-secondary d-block mb-2"></i>
                    <p>Belum ada berita yang dipublikasikan.</p>
                </div>

            <?php endif; ?>

        </div>
    </div>
</section>

<!-- Galeri Sekolah -->
<section class="py-5 bg-white" id="galeri">
    <div class="container py-4">

        <div class="section-title text-center">
            <span class="text-primary fw-semibold small text-uppercase">
                Dokumentasi Sekolah
            </span>

            <h2 class="fw-bold">Galeri SMKS Prasetya Makassar</h2>
            <div class="divider"></div>

            <p class="text-muted mt-2">
                Dokumentasi kegiatan dan aktivitas SMKS Prasetya Makassar.
            </p>
        </div>

        <div class="row g-4">

            <?php if ($q_galeri && mysqli_num_rows($q_galeri) > 0) : ?>

                <?php while ($g = mysqli_fetch_assoc($q_galeri)) : ?>

                    <div class="col-md-6 col-lg-4">
                        <div class="card card-galeri">

                            <img
                                src="assets/uploads/<?= htmlspecialchars($g['nama_file']); ?>"
                                alt="Dokumentasi <?= htmlspecialchars($g['judul']); ?> - SMKS Prasetya Makassar"
                                class="w-100"
                                loading="lazy"
                                decoding="async"
                                onerror="this.src='https://placehold.co/600x400?text=Galeri+Sekolah'">

                            <div class="card-body p-3">
                                <h5 class="fw-bold text-dark mb-2">
                                    <?= htmlspecialchars($g['judul']); ?>
                                </h5>

                                <p class="text-secondary small mb-0">
                                    <?= htmlspecialchars($g['deskripsi'] ?? 'Dokumentasi kegiatan sekolah.'); ?>
                                </p>
                            </div>

                        </div>
                    </div>

                <?php endwhile; ?>

            <?php else : ?>

                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-images fs-1 d-block mb-2"></i>
                    <p>Belum ada dokumentasi galeri.</p>
                </div>

            <?php endif; ?>

        </div>
    </div>
</section>

<!-- Kontak -->
<section class="py-5 bg-white" id="kontak">
    <div class="container py-4">

        <div class="section-title text-center">
            <span class="text-primary fw-semibold small text-uppercase">
                Layanan Informasi
            </span>

            <h2 class="fw-bold">Hubungi Kami</h2>
            <div class="divider"></div>

            <p class="text-muted mt-2">
                Hubungi SMKS Prasetya Makassar untuk mendapatkan informasi lebih lanjut.
            </p>
        </div>

        <!-- Notifikasi Pesan -->
        <?php if ($pesan_status == 'sukses') : ?>

            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-start border-success border-4 mb-4" id="alertPesanSukses">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-3 me-3 text-success"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Pesan Anda Berhasil Terkirim!</h6>
                        <p class="mb-0 small text-muted">Terima kasih telah menghubungi SMKS Prasetya Makassar. Pesan baru Anda telah diterima dan masuk ke panel notifikasi administrator sekolah.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const el = document.getElementById('alertPesanSukses');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            </script>

        <?php elseif ($pesan_status == 'gagal') : ?>

            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-start border-danger border-4 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <strong>Gagal Mengirim Pesan:</strong> Terjadi gangguan saat menyimpan pesan Anda. Silakan coba kembali beberapa saat lagi.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        <?php elseif ($pesan_status == 'kosong') : ?>

            <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm border-start border-warning border-4 mb-4">
                <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                <strong>Formulir Belum Lengkap:</strong> Mohon lengkapi Nama, Email, Subjek, dan Isi Pesan sebelum mengirimkan formulir kontak.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        <?php endif; ?>

        <div class="row g-4">

            <!-- Informasi Kontak -->
            <div class="col-lg-5">

                <div class="bg-custom-navy text-white p-4 p-md-5 rounded-4 h-100 shadow-sm">

                    <h4 class="fw-bold mb-4">Informasi Kontak</h4>

                    <div class="d-flex align-items-start mb-4">
                        <div class="me-3 fs-4 text-warning">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>

                        <div>
                            <h6 class="fw-bold mb-1">Alamat Sekolah</h6>
                            <p class="text-white-50 small mb-0">
                                Jl. Buntusu NHP / BTP No. 17,
                                Kecamatan Tamalanrea, Kota Makassar,
                                Sulawesi Selatan 90245.
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="me-3 fs-4 text-warning">
                            <i class="bi bi-telephone-fill"></i>
                        </div>

                        <div>
                            <h6 class="fw-bold mb-1">Telepon</h6>
                            <p class="text-white-50 small mb-0">
                                0411 4770832
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="me-3 fs-4 text-warning">
                            <i class="bi bi-envelope-fill"></i>
                        </div>

                        <div>
                            <h6 class="fw-bold mb-1">Email</h6>
                            <p class="text-white-50 small mb-0">
                                smkprasetya.makassar@yahoo.co.id
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="me-3 fs-4 text-warning">
                            <i class="bi bi-person-fill"></i>
                        </div>

                        <div>
                            <h6 class="fw-bold mb-1">Kepala Sekolah</h6>
                            <p class="text-white-50 small mb-0">
                                Rizkiani Razak
                            </p>
                        </div>
                    </div>

                    <!-- Google Maps -->
                    <div class="mt-4 rounded-3 overflow-hidden shadow-sm" style="height: 220px;">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3973.7677266020887!2d119.51389357353868!3d-5.1410558519865655!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dbee4abf566c9a7%3A0x9749c2594ac4e215!2sSMP-SMK%20PRASETYA%20MAKASSAR!5e0!3m2!1sid!2sid!4v1794240737419!5m2!1sid!2sid"
                            width="100%"
                            height="220"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin">
                        </iframe>
                    </div>

                </div>
            </div>

            <!-- Form Pesan -->
            <div class="col-lg-7">

                <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4 bg-light">

                    <h4 class="fw-bold text-dark mb-2">
                        Kirimkan Pesan Anda
                    </h4>

                    <p class="text-muted small mb-4">
                        Isi formulir berikut untuk menyampaikan pertanyaan,
                        kritik, atau saran.
                    </p>

                    <form action="#kontak" method="POST">

                        <div class="row g-3 mb-3">

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="nama"
                                    class="form-control bg-white"
                                    placeholder="Nama Anda"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">
                                    Alamat Email <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control bg-white"
                                    placeholder="email@contoh.com"
                                    required>
                            </div>

                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                Subjek Pesan <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="subjek"
                                class="form-control bg-white"
                                placeholder="Subjek pesan"
                                required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold">
                                Isi Pesan <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="pesan"
                                class="form-control bg-white"
                                rows="5"
                                placeholder="Tuliskan pertanyaan atau pesan Anda..."
                                required></textarea>
                        </div>

                        <button
                            type="submit"
                            name="kirim_pesan"
                            class="btn btn-primary px-4 py-2 fw-semibold rounded-pill shadow-sm">

                            <i class="bi bi-send-fill me-1"></i>
                            Kirim Pesan

                        </button>

                    </form>

                </div>
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
                    SMKS Prasetya Makassar merupakan sekolah menengah
                    kejuruan swasta yang berlokasi di Kecamatan
                    Tamalanrea, Kota Makassar, Sulawesi Selatan.
                </p>

            </div>

            <div class="col-md-6 col-lg-3">

                <h6 class="fw-bold text-warning mb-3">
                    Navigasi Halaman
                </h6>

                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2">
                        <a href="#beranda" class="text-white-50 text-decoration-none">Beranda</a>
                    </li>

                    <li class="mb-2">
                        <a href="#profil" class="text-white-50 text-decoration-none">Profil Sekolah</a>
                    </li>

                    <li class="mb-2">
                        <a href="#jurusan" class="text-white-50 text-decoration-none">Program Keahlian</a>
                    </li>

                    <li class="mb-2">
                        <a href="guru.php" class="text-white-50 text-decoration-none">Guru & Tendik</a>
                    </li>

                    <li class="mb-2">
                        <a href="fasilitas.php" class="text-white-50 text-decoration-none">Fasilitas Sekolah</a>
                    </li>

                    <li class="mb-2">
                        <a href="alumni.php" class="text-white-50 text-decoration-none">Alumni</a>
                    </li>

                    <li class="mb-2">
                        <a href="#berita" class="text-white-50 text-decoration-none">Berita</a>
                    </li>

                    <li class="mb-2">
                        <a href="#galeri" class="text-white-50 text-decoration-none">Galeri</a>
                    </li>

                    <li class="mb-2">
                        <a href="#kontak" class="text-white-50 text-decoration-none">Kontak Kami</a>
                    </li>
                </ul>

            </div>

            <div class="col-md-6 col-lg-4">

                <h6 class="fw-bold text-warning mb-3">
                    Akses Admin
                </h6>

                <p class="small text-white-50 mb-2">
                    Pengelola sekolah dapat masuk ke panel administrasi
                    melalui tautan berikut.
                </p>

                <a href="login.php" class="btn btn-outline-warning btn-sm rounded-pill">
                    <i class="bi bi-person-fill-lock me-1"></i>
                    Masuk Panel Admin
                </a>

            </div>

        </div>

        <hr class="border-secondary">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 text-white-50 small">

            <div>
                &copy; <?= date('Y'); ?> SMKS Prasetya Makassar.
                All Rights Reserved.
            </div>

            <div>
                Dirancang dengan PHP Native & Bootstrap 5
            </div>

        </div>

    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
