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
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php?status=error_not_found");
    exit;
}

// Ambil data admin
$stmt = mysqli_prepare($conn, "SELECT id, username, password, nama_lengkap FROM admin WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    header("Location: index.php?status=error_not_found");
    exit;
}

$admin = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$is_current = ($id == ($_SESSION['admin_id'] ?? 0));

if (isset($_POST['update_password'])) {
    $password_baru       = $_POST['password_baru'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    // Jika mengganti password akun sendiri, verifikasi password saat ini
    $valid_old_pass = true;
    if ($is_current) {
        $password_lama = $_POST['password_lama'] ?? '';
        if (empty($password_lama)) {
            $error = "Silakan masukkan password lama Anda untuk verifikasi!";
            $valid_old_pass = false;
        } else {
            $hashed = $admin['password'];
            if (!password_verify($password_lama, $hashed) && md5($password_lama) !== $hashed) {
                $error = "Password lama yang Anda masukkan salah!";
                $valid_old_pass = false;
            }
        }
    }

    if ($valid_old_pass) {
        if (empty($password_baru) || empty($konfirmasi_password)) {
            $error = "Password baru dan konfirmasi password wajib diisi!";
        } elseif (strlen($password_baru) < 6) {
            $error = "Password baru minimal terdiri dari 6 karakter!";
        } elseif ($password_baru !== $konfirmasi_password) {
            $error = "Konfirmasi password baru tidak cocok!";
        } else {
            // Hash password baru
            $new_hash = password_hash($password_baru, PASSWORD_DEFAULT);

            $stmt_update = mysqli_prepare($conn, "UPDATE admin SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_update, "si", $new_hash, $id);

            if (mysqli_stmt_execute($stmt_update)) {
                mysqli_stmt_close($stmt_update);
                header("Location: index.php?status=sukses_password");
                exit;
            } else {
                $error = "Gagal memperbarui password: " . htmlspecialchars(mysqli_error($conn));
                mysqli_stmt_close($stmt_update);
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
    <title>Ubah Password Admin - Admin SMKS Prasetya Makassar</title>
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
                <li class="breadcrumb-item active" aria-current="page">Ubah Password</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card card-custom bg-white p-4">
                    <div class="border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-1"><i class="bi bi-key-fill text-warning me-2"></i>Ubah Password Admin</h4>
                            <p class="text-muted small mb-0">Perbarui kata sandi untuk akun administrator</p>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>

                    <!-- Target Account Card -->
                    <div class="p-3 bg-light rounded-3 mb-4 border d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                            <?= strtoupper(substr($admin['nama_lengkap'], 0, 1)); ?>
                        </div>
                        <div>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($admin['nama_lengkap']); ?></div>
                            <small class="text-muted">Username: <code class="text-primary">@<?= htmlspecialchars($admin['username']); ?></code></small>
                            <?php if ($is_current) : ?>
                                <span class="badge bg-success-subtle text-success ms-2"><i class="bi bi-person-check-fill me-1"></i>Akun Anda</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($error)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" autocomplete="off">
                        <?php if ($is_current) : ?>
                            <!-- Password Lama (Wajib jika akun sendiri) -->
                            <div class="mb-3">
                                <label for="password_lama" class="form-label fw-semibold">
                                    Password Lama <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_lama" 
                                           name="password_lama" 
                                           placeholder="Masukkan password Anda saat ini" 
                                           required 
                                           autofocus>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password_lama', 'iconOld')">
                                        <i class="bi bi-eye" id="iconOld"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Diperlukan verifikasi password saat ini demi keamanan.</small>
                            </div>
                            <hr class="my-3">
                        <?php endif; ?>

                        <!-- Password Baru -->
                        <div class="mb-3">
                            <label for="password_baru" class="form-label fw-semibold">
                                Password Baru <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_baru" 
                                       name="password_baru" 
                                       placeholder="Minimal 6 karakter" 
                                       minlength="6" 
                                       required 
                                       <?= !$is_current ? 'autofocus' : ''; ?>>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password_baru', 'iconNew')">
                                    <i class="bi bi-eye" id="iconNew"></i>
                                </button>
                            </div>
                            <small class="text-muted">Gunakan kombinasi huruf dan angka agar lebih aman.</small>
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="mb-4">
                            <label for="konfirmasi_password" class="form-label fw-semibold">
                                Konfirmasi Password Baru <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-check"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="konfirmasi_password" 
                                       name="konfirmasi_password" 
                                       placeholder="Ulangi password baru" 
                                       minlength="6" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePass('konfirmasi_password', 'iconKonf')">
                                    <i class="bi bi-eye" id="iconKonf"></i>
                                </button>
                            </div>
                            <small class="text-muted">Pastikan sama dengan password baru di atas.</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                            <a href="index.php" class="btn btn-light px-4">Batal</a>
                            <button type="submit" name="update_password" class="btn btn-warning text-dark px-4 fw-semibold">
                                <i class="bi bi-check-lg me-1"></i> Perbarui Password
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

