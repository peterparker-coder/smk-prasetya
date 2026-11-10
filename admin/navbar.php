
<?php
if (!isset($base_admin)) {
    $base_admin = "";
}

// Hitung pesan yang belum dibaca
$unread_count = 0;

if (isset($conn)) {
    $q_pesan_unread = mysqli_query(
        $conn,
        "SELECT COUNT(*) as total FROM pesan WHERE status='belum dibaca'"
    );

    if ($q_pesan_unread) {
        $r_unread = mysqli_fetch_assoc($q_pesan_unread);
        $unread_count = $r_unread['total'];
    }
}

$nama_admin = htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin');
$logo = $base_admin . "../assets/uploads/logo-smks-prasetya.png";
?>

<link rel="stylesheet"
      href="<?= $base_admin; ?>../assets/css/theme.css">

<script src="<?= $base_admin; ?>../assets/js/theme.js"></script>

<style>
    body {
        padding-left: 260px;
    }

    .admin-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 260px;
        height: 100vh;
        background: #212529;
        color: white;
        display: flex;
        flex-direction: column;
        z-index: 1050;
        padding: 20px 15px;
        overflow-y: auto;
        transition: transform 0.3s ease;
    }

    .sidebar-brand {
        text-align: center;
        padding: 10px 5px 25px;
        border-bottom: 1px solid #495057;
        margin-bottom: 20px;
    }

    .sidebar-brand img {
        width: 85px;
        height: 85px;
        object-fit: contain;
        margin-bottom: 10px;
    }

    .sidebar-brand h6 {
        font-weight: bold;
        margin-bottom: 3px;
    }

    .sidebar-brand small {
        color: #adb5bd;
    }

    .sidebar-menu {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #ced4da;
        text-decoration: none;
        padding: 12px 14px;
        border-radius: 8px;
        transition: 0.2s;
    }

    .sidebar-menu a:hover {
        background: #343a40;
        color: white;
    }

    .sidebar-menu a.active {
        background: #0d6efd;
        color: white;
        font-weight: bold;
    }

    .sidebar-menu i {
        font-size: 18px;
        width: 22px;
    }

    .sidebar-bottom {
        margin-top: auto;
        padding-top: 20px;
        border-top: 1px solid #495057;
    }

    .sidebar-user {
        color: #adb5bd;
        font-size: 13px;
        margin-bottom: 12px;
        overflow-wrap: anywhere;
    }

    .sidebar-bottom .btn {
        width: 100%;
        margin-bottom: 8px;
        text-align: left;
    }

    /* Tombol tema berbentuk lingkaran */
    .theme-toggle-wrapper {
        display: flex;
        justify-content: center;
        margin-bottom: 15px;
    }

    #themeToggle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        font-size: 19px;
        transition: 0.2s;
    }

    .sidebar-toggle {
        display: none;
        position: fixed;
        top: 12px;
        left: 12px;
        z-index: 1040;
    }

    .sidebar-overlay {
        display: none;
    }

    @media (max-width: 768px) {
        body {
            padding-left: 0;
            padding-top: 55px;
        }

        .admin-sidebar {
            transform: translateX(-100%);
        }

        .admin-sidebar.show {
            transform: translateX(0);
        }

        .sidebar-toggle {
            display: block;
        }

        .sidebar-overlay.show {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1045;
        }
    }
</style>

<!-- Tombol Menu Mobile -->
<button type="button"
        class="btn btn-dark sidebar-toggle shadow"
        id="sidebarToggle">
    <i class="bi bi-list fs-4"></i>
</button>

<!-- Overlay Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">

    <!-- Logo dan Nama Sekolah -->
    <div class="sidebar-brand">
        <img src="<?= htmlspecialchars($logo); ?>"
             alt="Logo SMKS Prasetya Makassar">

        <h6>SMKS PRASETYA</h6>
        <small>Panel Administrator</small>
    </div>

    <!-- Menu Navigasi -->
    <nav class="sidebar-menu">

        <a href="<?= $base_admin; ?>index.php"
           class="<?= (isset($active_menu) && $active_menu == 'dashboard') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?= $base_admin; ?>jurusan/index.php"
           class="<?= (isset($active_menu) && $active_menu == 'jurusan') ? 'active' : ''; ?>">
            <i class="bi bi-briefcase"></i>
            <span>Data Jurusan</span>
        </a>

        <a href="<?= $base_admin; ?>berita/index.php"
           class="<?= (isset($active_menu) && $active_menu == 'berita') ? 'active' : ''; ?>">
            <i class="bi bi-newspaper"></i>
            <span>Berita & Kegiatan</span>
        </a>

        <a href="<?= $base_admin; ?>galeri/index.php"
           class="<?= (isset($active_menu) && $active_menu == 'galeri') ? 'active' : ''; ?>">
            <i class="bi bi-images"></i>
            <span>Galeri</span>
        </a>

        <a href="<?= $base_admin; ?>pesan/index.php"
           class="<?= (isset($active_menu) && $active_menu == 'pesan') ? 'active' : ''; ?>">
            <i class="bi bi-envelope"></i>
            <span>Pesan Masuk</span>

            <?php if ($unread_count > 0) : ?>
                <span class="badge bg-danger rounded-pill ms-auto">
                    <?= $unread_count; ?>
                </span>
            <?php endif; ?>
        </a>

    </nav>

    <!-- Bagian Bawah Sidebar -->
    <div class="sidebar-bottom">

        <div class="sidebar-user">
            <i class="bi bi-person-circle me-2"></i>
            <?= $nama_admin; ?>
        </div>

        <!-- Tombol Light Mode / Dark Mode -->
        <div class="theme-toggle-wrapper">
            <button type="button"
                    id="themeToggle"
                    class="btn btn-outline-light"
                    aria-label="Ganti Tema"
                    title="Ganti Tema">
                <i class="bi bi-moon-stars-fill"></i>
            </button>
        </div>

        <a href="<?= $base_admin; ?>../index.php"
           target="_blank"
           class="btn btn-outline-light btn-sm">
            <i class="bi bi-globe me-2"></i>
            Lihat Website
        </a>

        <a href="<?= $base_admin; ?>../logout.php"
           onclick="return confirm('Apakah Anda yakin ingin logout?');"
           class="btn btn-danger btn-sm">
            <i class="bi bi-box-arrow-right me-2"></i>
            Logout
        </a>

    </div>

</aside>

<script>
    const sidebar = document.getElementById('adminSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
    });

    sidebarOverlay.addEventListener('click', function () {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });
</script>
