<?php
// Pastikan variabel $base_admin ada, default ke string kosong jika di root admin/
if (!isset($base_admin)) {
    $base_admin = "";
}

// Hitung pesan yang belum dibaca
$unread_count = 0;
if (isset($conn)) {
    $q_pesan_unread = mysqli_query($conn, "SELECT COUNT(*) as total FROM pesan WHERE status='belum dibaca'");
    if ($q_pesan_unread) {
        $r_unread = mysqli_fetch_assoc($q_pesan_unread);
        $unread_count = $r_unread['total'];
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= $base_admin; ?>index.php">
            <i class="bi bi-mortarboard-fill text-warning me-2 fs-4"></i>
            <span>SMKS Prasetya <small class="fw-normal text-white-50">Admin</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= (isset($active_menu) && $active_menu == 'dashboard') ? 'active fw-bold' : ''; ?>" href="<?= $base_admin; ?>index.php">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (isset($active_menu) && $active_menu == 'jurusan') ? 'active fw-bold' : ''; ?>" href="<?= $base_admin; ?>jurusan/index.php">
                        <i class="bi bi-briefcase me-1"></i> Data Jurusan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (isset($active_menu) && $active_menu == 'berita') ? 'active fw-bold' : ''; ?>" href="<?= $base_admin; ?>berita/index.php">
                        <i class="bi bi-newspaper me-1"></i> Berita & Kegiatan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (isset($active_menu) && $active_menu == 'pesan') ? 'active fw-bold' : ''; ?>" href="<?= $base_admin; ?>pesan/index.php">
                        <i class="bi bi-envelope me-1"></i> Pesan Masuk
                        <?php if ($unread_count > 0) : ?>
                            <span class="badge bg-danger rounded-pill ms-1"><?= $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <a href="<?= $base_admin; ?>../index.php" target="_blank" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-globe me-1"></i> Lihat Web
                </a>
                <div class="vr bg-secondary mx-1 d-none d-lg-block"></div>
                <span class="text-white-50 small d-none d-lg-inline">
                    <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?>
                </span>
                <a href="<?= $base_admin; ?>../logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?');" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>
