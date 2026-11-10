<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$active_menu = 'admin';
$base_admin  = '../';

$error = '';
$nama_lengkap = '';
$username     = '';

if (isset($_POST['simpan'])) {
    $nama_lengkap        = trim($_POST['nama_lengkap'] ?? '');
    $username            = strtolower(trim($_POST['username'] ?? ''));
    $password            = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    // Validasi Kolom Kosong
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($konfirmasi_password)) {
        $error = "Semua kolom formulir wajib diisi!";
    } 
    // Validasi Format Username (hanya huruf, angka, underscore, 3-50 karakter)
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $error = "Username hanya boleh berupa huruf, angka, dan underscore (_) dengan panjang 3 sampai 50 karakter!";
    }
    // Validasi Panjang Password
    elseif (strlen($password) < 6) {
        $error = "Password minimal terdiri dari 6 karakter!";
    }
    // Validasi Konfirmasi Password
    elseif ($password !== $konfirmasi_password) {
        $error = "Konfirmasi password tidak cocok dengan password yang dimasukkan!";
    } 
    else {
        // Cek apakah username sudah digunakan
        $stmt_check = mysqli_prepare($conn, "SELECT id FROM admin WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_check, "s", $username);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Username '<strong>" . htmlspecialchars($username) . "</strong>' sudah digunakan. Silakan gunakan username lain!";
            mysqli_stmt_close($stmt_check);
        } else {
            mysqli_stmt_close($stmt_check);

            // Hash password secara aman
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan akun admin baru menggunakan prepared statement
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO admin (username, password, nama_lengkap) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, "sss", $username, $hashed_password, $nama_lengkap);

            if (mysqli_stmt_execute($stmt_insert)) {
                mysqli_stmt_close($stmt_insert);
                header("Location: index.php?status=sukses_tambah");
                exit;
            } else {
                $error = "Gagal menyimpan akun admin baru: " . htmlspecialchars(mysqli_error($conn));
                mysqli_stmt_close($stmt_insert);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Akun Admin - Admin SMKS Prasetya Makassar</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
    </style>
</head>
<body>

    <!-- Include Navbar Admin -->
    <?php include '../navbar.php'; ?>

    <div class="container my-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Manajemen Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Admin</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card card-custom bg-white p-4">
                    <div class="border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-1"><i class="bi bi-person-plus-fill text-primary me-2"></i>Tambah Akun Admin</h4>
                            <p class="text-muted small mb-0">Daftarkan akun administrator baru untuk mengelola website profil sekolah</p>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>

                    <?php if (!empty($error)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" autocomplete="off">
                        <!-- Nama Lengkap -->
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label fw-semibold">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="nama_lengkap" 
                                       name="nama_lengkap" 
                                       placeholder="Contoh: Achmad Fauzi, S.Kom." 
                                       value="<?= htmlspecialchars($nama_lengkap); ?>" 
                                       maxlength="100" 
                                       required 
                                       autofocus>
                            </div>
                            <small class="text-muted">Nama lengkap pengurus/pengelola akun admin.</small>
                        </div>

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">
                                Username <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">@</span>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       placeholder="Contoh: admin_fauzi" 
                                       value="<?= htmlspecialchars($username); ?>" 
                                       maxlength="50" 
                                       required>
                            </div>
                            <small class="text-muted">Gunakan huruf kecil, angka, atau underscore tanpa spasi (3-50 karakter).</small>
                        </div>

                        <div class="row">
                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Minimal 6 karakter" 
                                           minlength="6" 
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password', 'iconPass')">
                                        <i class="bi bi-eye" id="iconPass"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Panjang minimal 6 karakter.</small>
                            </div>

                            <!-- Konfirmasi Password -->
                            <div class="col-md-6 mb-3">
                                <label for="konfirmasi_password" class="form-label fw-semibold">
                                    Konfirmasi Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-check"></i></span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="konfirmasi_password" 
                                           name="konfirmasi_password" 
                                           placeholder="Ulangi password" 
                                           minlength="6" 
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePass('konfirmasi_password', 'iconKonf')">
                                        <i class="bi bi-eye" id="iconKonf"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Ketik ulang password untuk verifikasi.</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="index.php" class="btn btn-light px-4">Batal</a>
                            <button type="submit" name="simpan" class="btn btn-primary px-4 fw-medium">
                                <i class="bi bi-check-lg me-1"></i> Simpan Akun Admin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Admin -->
    <footer class="bg-white border-top py-3 mt-5">
        <div class="container text-center text-muted small">
            &copy; 2026 SMKS Prasetya Makassar. Panel Manajemen Admin.
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>

