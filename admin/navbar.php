
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

    /* Efek animasi denyut (pulse) pada badge notifikasi pesan baru */
    @keyframes badge-pulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        50% { transform: scale(1.06); box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
    .badge-pulse {
        animation: badge-pulse 2s infinite ease-in-out;
    }

    /* Animasi getar / lonceng */
    @keyframes bell-ring {
        0% { transform: rotate(0); }
        10% { transform: rotate(14deg); }
        20% { transform: rotate(-14deg); }
        30% { transform: rotate(10deg); }
        40% { transform: rotate(-10deg); }
        50% { transform: rotate(6deg); }
        60% { transform: rotate(-6deg); }
        70% { transform: rotate(0); }
        100% { transform: rotate(0); }
    }
    .bell-ring {
        display: inline-block;
        animation: bell-ring 2.5s infinite ease;
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

        <a href="<?= $base_admin; ?>index.php#statistik-pengunjung"
           class="<?= (isset($active_menu) && $active_menu == 'statistik') ? 'active' : ''; ?>">
            <i class="bi bi-bar-chart-line"></i>
            <span>Statistik Pengunjung</span>
        </a>

        <a href="<?= $base_admin; ?>jurusan/index.php"
           class="<?= (isset($active_menu) && $active_menu == 'jurusan') ? 'active' : ''; ?>">
            <i class="bi bi-briefcase"></i>
            <span>Data Jurusan</span>
        </a>

        <a href="<?= $base_admin; ?>guru/index.php"
           class="<?= (isset($active_menu) && $active_menu == 'guru') ? 'active' : ''; ?>">
            <i class="bi bi-people"></i>
            <span>Guru & Tenaga Kependidikan</span>
        </a>

        <a href="<?= $base_admin; ?>fasilitas/index.php"
           class="<?= (isset($active_menu) && $active_menu == 'fasilitas') ? 'active' : ''; ?>">
            <i class="bi bi-building"></i>
            <span>Fasilitas Sekolah</span>
        </a>

        <a href="<?= $base_admin; ?>alumni/index.php"
           class="<?= (isset($active_menu) && $active_menu == 'alumni') ? 'active' : ''; ?>">
            <i class="bi bi-mortarboard"></i>
            <span>Data Alumni</span>
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

            <span class="badge bg-danger rounded-pill ms-auto fw-semibold <?= ($unread_count > 0) ? 'badge-pulse' : 'd-none'; ?>" 
                  id="sidebarMsgBadge" 
                  title="<?= $unread_count; ?> pesan belum dibaca">
                <span id="sidebarMsgCount"><?= $unread_count; ?></span> Baru
            </span>
        </a>

        <a href="<?= $base_admin; ?>kelola-admin/index.php"
           class="<?= (isset($active_menu) && $active_menu == 'admin') ? 'active' : ''; ?>">
            <i class="bi bi-person-gear"></i>
            <span>Manajemen Admin</span>
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

<!-- Toast Container Notifikasi Pesan Baru -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1099;">
    <div id="toastPesanBaru" class="toast shadow-lg border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="10000">
        <div class="toast-header bg-primary text-white">
            <i class="bi bi-bell-fill text-warning me-2 bell-ring"></i>
            <strong class="me-auto">Pesan Baru Masuk!</strong>
            <small class="text-white-50" id="toastWaktu">Baru saja</small>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body bg-white text-dark p-3 rounded-bottom">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; min-width: 38px;">
                    <i class="bi bi-person-fill fs-5"></i>
                </div>
                <div class="overflow-hidden">
                    <h6 class="mb-0 fw-bold text-truncate text-dark" id="toastPengirim">Pengirim</h6>
                    <small class="text-muted text-truncate d-block" id="toastSubjek">Subjek Pesan</small>
                </div>
            </div>
            <p class="small text-secondary mb-3 text-truncate" id="toastIsi" style="max-width: 320px;"></p>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="toast">Tutup</button>
                <a href="<?= $base_admin; ?>pesan/index.php" class="btn btn-sm btn-primary fw-medium">
                    <i class="bi bi-envelope-open me-1"></i> Buka Pesan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Script Pembaruan Notifikasi Pesan Otomatis (Tanpa Refresh) -->
<script>
    (function () {
        let lastUnreadCount = <?= (int)$unread_count; ?>;
        let lastMaxId = 0;
        let isFirstCheck = true;
        const originalTitle = document.title;
        const baseAdminUrl = '<?= $base_admin; ?>';
        const apiUrl = baseAdminUrl + 'api-pesan.php';

        // Sintesis suara notifikasi yang lembut menggunakan Web Audio API
        function playChime() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // Nada D5
                osc.frequency.setValueAtTime(880, ctx.currentTime + 0.12); // Nada A5

                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start();
                osc.stop(ctx.currentTime + 0.35);
            } catch (e) {
                // AudioContext dicegah sebelum ada gestur klik pengguna (abaikan jika terblokir)
            }
        }

        function checkNewMessages() {
            fetch(apiUrl, { cache: 'no-store' })
                .then(function (res) {
                    if (!res.ok) throw new Error('API request failed');
                    return res.json();
                })
                .then(function (data) {
                    if (data.status !== 'success') return;

                    const count = parseInt(data.unread_count) || 0;
                    const maxId = parseInt(data.max_id) || 0;
                    const totalCount = parseInt(data.total_count) || 0;

                    // 1. Pembaruan Badge Sidebar secara Real-time
                    const sidebarBadge = document.getElementById('sidebarMsgBadge');
                    const sidebarCount = document.getElementById('sidebarMsgCount');
                    if (sidebarBadge && sidebarCount) {
                        sidebarCount.textContent = count;
                        if (count > 0) {
                            sidebarBadge.classList.remove('d-none');
                            sidebarBadge.classList.add('badge-pulse');
                            sidebarBadge.title = count + ' pesan belum dibaca';
                        } else {
                            sidebarBadge.classList.add('d-none');
                            sidebarBadge.classList.remove('badge-pulse');
                        }
                    }

                    // 2. Pembaruan Title Bar Browser
                    if (count > 0) {
                        document.title = '(' + count + ') ' + originalTitle.replace(/^\(\d+\)\s*/, '');
                    } else {
                        document.title = originalTitle.replace(/^\(\d+\)\s*/, '');
                    }

                    // 3. Pembaruan Indikator di Dashboard Admin (jika sedang membuka halaman dashboard)
                    const dashUnread = document.getElementById('dashUnreadCount');
                    if (dashUnread) {
                        dashUnread.textContent = count;
                    }

                    const dashTotal = document.getElementById('dashTotalMsgCount');
                    if (dashTotal) {
                        dashTotal.textContent = totalCount;
                    }

                    const dashAlert = document.getElementById('dashUnreadAlert');
                    const dashAlertCount = document.getElementById('dashUnreadAlertCount');
                    if (dashAlert) {
                        if (count > 0) {
                            dashAlert.classList.remove('d-none');
                            if (dashAlertCount) dashAlertCount.textContent = count;
                        } else {
                            dashAlert.classList.add('d-none');
                        }
                    }

                    const dashStatus = document.getElementById('dashUnreadStatus');
                    if (dashStatus) {
                        if (count > 0) {
                            dashStatus.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
                            dashStatus.innerHTML = '<span class="spinner-grow spinner-grow-sm me-1" style="width: 7px; height: 7px;"></span>Perlu ditindaklanjuti';
                        } else {
                            dashStatus.className = 'badge bg-success-subtle text-success border border-success-subtle';
                            dashStatus.innerHTML = '<i class="bi bi-check2-all me-1"></i>Semua terbaca';
                        }
                    }

                    // 4. Deteksi Pesan Baru & Tampilkan Toast Notifikasi Mengambang
                    if (!isFirstCheck && (count > lastUnreadCount || (maxId > lastMaxId && count > 0))) {
                        if (data.latest_unread) {
                            const toastEl = document.getElementById('toastPesanBaru');
                            if (toastEl && window.bootstrap && typeof window.bootstrap.Toast === 'function') {
                                document.getElementById('toastPengirim').textContent = data.latest_unread.nama || 'Pengunjung';
                                document.getElementById('toastSubjek').textContent = data.latest_unread.subjek || 'Tanpa Subjek';
                                document.getElementById('toastIsi').textContent = data.latest_unread.pesan || '';
                                document.getElementById('toastWaktu').textContent = data.latest_unread.waktu || 'Baru saja';

                                const toast = new bootstrap.Toast(toastEl);
                                toast.show();
                                playChime();
                            }
                        }
                    }

                    lastUnreadCount = count;
                    lastMaxId = maxId;
                    isFirstCheck = false;
                })
                .catch(function () {});
        }

        // Pengecekan otomatis berjalan setiap 5 detik tanpa perlu me-refresh halaman
        setInterval(checkNewMessages, 5000);
    })();
</script>
