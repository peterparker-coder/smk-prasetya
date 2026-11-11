<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/tracker.php';
require_once __DIR__ . '/config/seo.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt_b = mysqli_prepare($conn, "SELECT * FROM berita WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_b, "i", $id);
mysqli_stmt_execute($stmt_b);
$query = mysqli_stmt_get_result($stmt_b);

if (!$query || mysqli_num_rows($query) == 0) {
    header("Location: index.php#berita");
    exit;
}

$berita = mysqli_fetch_assoc($query);
mysqli_stmt_close($stmt_b);

// Catat kunjungan ke artikel berita ini
catat_pengunjung($conn, 'Berita: ' . ($berita['judul'] ?? 'Artikel'));

// Berita lainnya untuk sidebar
$stmt_lain = mysqli_prepare($conn, "SELECT * FROM berita WHERE id != ? ORDER BY tanggal DESC LIMIT 4");
mysqli_stmt_bind_param($stmt_lain, "i", $id);
mysqli_stmt_execute($stmt_lain);
$q_lainnya = mysqli_stmt_get_result($stmt_lain);

// Ringkasan untuk meta deskripsi
$snippet_desc = mb_substr(preg_replace('/\s+/', ' ', strip_tags($berita['isi'] ?? '')), 0, 160);
$news_image = (!empty($berita['gambar']) && file_exists(__DIR__ . '/assets/uploads/' . $berita['gambar']))
    ? 'assets/uploads/' . $berita['gambar']
    : 'assets/uploads/logo-smks-prasetya.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    render_seo_tags([
        'title'       => $berita['judul'] . ' - SMKS Prasetya Makassar',
        'description' => $snippet_desc,
        'keywords'    => 'Berita SMKS Prasetya Makassar, Informasi Sekolah SMK Makassar, Kegiatan SMK Prasetya, ' . $berita['judul'],
        'author'      => !empty($berita['penulis']) ? $berita['penulis'] : 'Humas SMKS Prasetya Makassar',
        'type'        => 'article',
        'image'       => $news_image,
    ]);
    render_article_schema($berita);
    render_breadcrumb_schema([
        'Beranda' => 'index.php',
        'Berita'  => 'index.php#berita',
        $berita['judul'] => 'detail-berita.php?id=' . $id
    ]);
    ?>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Tema Terang / Gelap -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <!-- JavaScript Tema -->
    <script src="assets/js/theme.js"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; color: #334155; }
        .navbar-brand span { font-size: 1.25rem; font-weight: 700; }
        .article-content { line-height: 1.8; font-size: 1.05rem; white-space: pre-line; }
        .hero-article-img { max-height: 450px; width: 100%; object-fit: cover; border-radius: 12px; }
        .sidebar-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .navbar-logo { width: 44px; height: 44px; object-fit: contain; margin-right: 10px; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm" style="background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%) !important;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
                <img src="assets/uploads/logo-smks-prasetya.png"
                     alt="Logo Resmi SMKS Prasetya Makassar"
                     class="navbar-logo"
                     width="44"
                     height="44"
                     decoding="async"
                     onerror="this.src='https://placehold.co/50x50?text=SMKS'">
                <span>SMKS PRASETYA MAKASSAR</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarMain">
                <!-- Tombol Dark Mode -->
                <button type="button" id="themeToggle" class="btn btn-outline-light btn-sm me-3 mb-2 mb-lg-0">
                    <i class="bi bi-moon-stars-fill me-1"></i> Dark Mode
                </button>

                <ul class="navbar-nav me-3">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#profil">Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#jurusan">Jurusan</a></li>
                    <li class="nav-item"><a class="nav-link" href="guru.php">Guru & Tendik</a></li>
                    <li class="nav-item"><a class="nav-link" href="fasilitas.php">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link" href="alumni.php">Alumni</a></li>
                    <li class="nav-item"><a class="nav-link active fw-semibold" href="index.php#berita">Berita</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#kontak">Kontak</a></li>
                </ul>
                <a href="login.php" class="btn btn-warning text-dark fw-bold btn-sm px-3 rounded-pill shadow-sm">
                    <i class="bi bi-lock-fill me-1"></i> Login Admin
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container my-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item"><a href="index.php#berita" class="text-decoration-none">Berita</a></li>
                <li class="breadcrumb-item active text-truncate" style="max-width: 300px;" aria-current="page"><?= htmlspecialchars($berita['judul']); ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Artikel Utama -->
            <div class="col-lg-8">
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                    <h1 class="fw-bold text-dark mb-3"><?= htmlspecialchars($berita['judul']); ?></h1>

                    <div class="d-flex align-items-center gap-3 text-muted small mb-4 pb-3 border-bottom flex-wrap">
                        <span><i class="bi bi-calendar3 text-primary me-1"></i><?= date('l, d F Y', strtotime($berita['tanggal'])); ?></span>
                        <span><i class="bi bi-person text-primary me-1"></i>Oleh: <strong><?= htmlspecialchars($berita['penulis']); ?></strong></span>
                        <span><i class="bi bi-building text-primary me-1"></i>SMKS Prasetya Makassar</span>
                    </div>

                    <div class="mb-4">
                        <img src="assets/uploads/<?= htmlspecialchars($berita['gambar']); ?>"
                             alt="<?= htmlspecialchars($berita['judul']); ?> - Berita SMKS Prasetya Makassar"
                             class="hero-article-img shadow-sm"
                             decoding="async"
                             onerror="this.src='https://placehold.co/800x450?text=Gambar+Berita'">
                    </div>

                    <article class="article-content text-secondary">
                        <?= nl2br(htmlspecialchars($berita['isi'])); ?>
                    </article>

                    <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <a href="index.php#berita" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Berita
                        </a>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted">Bagikan:</span>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode($berita['judul'] . ' - ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-sm btn-success rounded-circle" title="Share ke WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Berita Lainnya -->
            <div class="col-lg-4">
                <div class="sidebar-card bg-white p-4 rounded-4 shadow-sm border mb-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">
                        <i class="bi bi-newspaper text-primary me-2"></i>Berita Lainnya
                    </h5>

                    <?php if (mysqli_num_rows($q_lainnya) > 0) : ?>
                        <div class="d-flex flex-column gap-3">
                            <?php while ($bl = mysqli_fetch_assoc($q_lainnya)) : ?>
                                <div class="d-flex gap-3 align-items-center">
                                    <img src="assets/uploads/<?= htmlspecialchars($bl['gambar']); ?>"
                                         alt="<?= htmlspecialchars($bl['judul']); ?> - SMKS Prasetya Makassar"
                                         class="rounded object-fit-cover flex-shrink-0"
                                         width="80"
                                         height="65"
                                         loading="lazy"
                                         decoding="async"
                                         onerror="this.src='https://placehold.co/100x80?text=News'">
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 11px;">
                                            <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($bl['tanggal'])); ?>
                                        </small>
                                        <a href="detail-berita.php?id=<?= $bl['id']; ?>" class="fw-semibold text-dark text-decoration-none small d-block line-clamp-2">
                                            <?= htmlspecialchars($bl['judul']); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else : ?>
                        <p class="text-muted small mb-0">Belum ada berita lainnya.</p>
                    <?php endif; ?>
                </div>

                <!-- Card Info PPDB / Kontak -->
                <div class="sidebar-card bg-primary text-white p-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;">
                    <h5 class="fw-bold mb-2">Ingin Bergabung?</h5>
                    <p class="small text-white-50 mb-3">Penerimaan Peserta Didik Baru (PPDB) SMKS Prasetya Makassar sedang dibuka. Daftarkan diri Anda sekarang!</p>
                    <a href="index.php#kontak" class="btn btn-warning btn-sm w-100 fw-bold text-dark">
                        <i class="bi bi-telephone-fill me-1"></i> Hubungi Panitia PPDB
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-5">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-mortarboard-fill text-warning me-2"></i> SMKS Prasetya Makassar
                    </h5>
                    <p class="text-white-50 small pe-lg-4">
                        Sekolah Menengah Kejuruan terakreditasi yang berdedikasi mencetak lulusan kompeten, siap kerja, dan berkarakter mulia di Kota Makassar, Sulawesi Selatan.
                    </p>
                </div>
                <div class="col-md-6 col-lg-3">
                    <h6 class="fw-bold text-warning mb-3">Tautan Cepat</h6>
                    <ul class="list-unstyled small text-white-50">
                        <li class="mb-2"><a href="index.php" class="text-white-50 text-decoration-none">Beranda</a></li>
                        <li class="mb-2"><a href="index.php#profil" class="text-white-50 text-decoration-none">Profil & Visi Misi</a></li>
                        <li class="mb-2"><a href="index.php#jurusan" class="text-white-50 text-decoration-none">Program Keahlian</a></li>
                        <li class="mb-2"><a href="guru.php" class="text-white-50 text-decoration-none">Guru & Tendik</a></li>
                        <li class="mb-2"><a href="fasilitas.php" class="text-white-50 text-decoration-none">Fasilitas Sekolah</a></li>
                        <li class="mb-2"><a href="alumni.php" class="text-white-50 text-decoration-none">Alumni</a></li>
                        <li class="mb-2"><a href="index.php#berita" class="text-white-50 text-decoration-none">Berita & Kegiatan</a></li>
                    </ul>
                </div>
                <div class="col-md-6 col-lg-4">
                    <h6 class="fw-bold text-warning mb-3">Kontak Sekolah</h6>
                    <p class="small text-white-50 mb-1"><i class="bi bi-geo-alt-fill text-warning me-2"></i>Jl. Prasetya, Kota Makassar, Sulawesi Selatan</p>
                    <p class="small text-white-50 mb-1"><i class="bi bi-telephone-fill text-warning me-2"></i>(0411) 123456 / 0812-3456-7890</p>
                    <p class="small text-white-50 mb-1"><i class="bi bi-envelope-fill text-warning me-2"></i>info@smksprasetyamakassar.sch.id</p>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center text-white-50 small">
                &copy; 2026 SMKS Prasetya Makassar. Seluruh Hak Cipta Dilindungi.
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
