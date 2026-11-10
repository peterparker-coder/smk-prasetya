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

// Ambil data admin yang akan diedit
$stmt = mysqli_prepare($conn, "SELECT id, username, nama_lengkap FROM admin WHERE id = ? LIMIT 1");
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

$nama_lengkap = $admin['nama_lengkap'];
$username     = $admin['username'];
$is_current   = ($id == ($_SESSION['admin_id'] ?? 0));

if (isset($_POST['update'])) {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username     = strtolower(trim($_POST['username'] ?? ''));

    // Validasi Kolom Kosong
    if (empty($nama_lengkap) || empty($username)) {
        $error = "Kolom Nama Lengkap dan Username wajib diisi!";
    }
    // Validasi Format Username
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $error = "Username hanya boleh berupa huruf, angka, dan underscore (_) dengan panjang 3 sampai 50 karakter!";
    }
    else {
        // Cek apakah username sudah digunakan oleh admin lain
        $stmt_check = mysqli_prepare($conn, "SELECT id FROM admin WHERE username = ? AND id != ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_check, "si", $username, $id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Username '<strong>" . htmlspecialchars($username) . "</strong>' sudah digunakan oleh akun admin lain. Silakan pilih username yang berbeda!";
            mysqli_stmt_close($stmt_check);
        } else {
            mysqli_stmt_close($stmt_check);

            // Update data admin
            $stmt_update = mysqli_prepare($conn, "UPDATE admin SET nama_lengkap = ?, username = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_update, "ssi", $nama_lengkap, $username, $id);

            if (mysqli_stmt_execute($stmt_update)) {
                mysqli_stmt_close($stmt_update);

                // Jika mengedit akun sendiri, perbarui data di session saat ini
                if ($is_current) {
                    $_SESSION['admin_nama']     = $nama_lengkap;
                    $_SESSION['admin_username'] = $username;
                }

                header("Location: index.php?status=sukses_edit");
                exit;
            } else {
                $error = "Gagal memperbarui data admin: " . htmlspecialchars(mysqli_error($conn));
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
    <title>Edit Akun Admin - Admin SMKS Prasetya Makassar</title>
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
                <li class="breadcrumb-item active" aria-current="page">Edit Admin</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card card-custom bg-white p-4">
                    <div class="border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Profil Admin</h4>
                            <p class="text-muted small mb-0">Ubah nama lengkap dan username akun administrator (ID: #<?= $id; ?>)</p>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>

                    <?php if ($is_current) : ?>
                        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                            <div>
                                Anda sedang mengedit <strong>akun Anda sendiri</strong>. Nama dan username pada sistem akan langsung diperbarui setelah disimpan.
                            </div>
                        </div>
                    <?php endif; ?>

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
                                       value="<?= htmlspecialchars($nama_lengkap); ?>" 
                                       maxlength="100" 
                                       required 
                                       autofocus>
                            </div>
                            <small class="text-muted">Nama yang akan ditampilkan pada sistem dan dashboard.</small>
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
                                       value="<?= htmlspecialchars($username); ?>" 
                                       maxlength="50" 
                                       required>
                            </div>
                            <small class="text-muted">Digunakan untuk login (huruf kecil, angka, underscore, tanpa spasi).</small>
                        </div>

                        <!-- Info Ubah Password -->
                        <div class="p-3 bg-light rounded-3 mb-4 border d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="fw-semibold text-dark d-block"><i class="bi bi-key-fill text-warning me-1"></i>Ingin mengubah password akun ini?</span>
                                <small class="text-muted">Password dikelola secara terpisah untuk keamanan sistem.</small>
                            </div>
                            <a href="ubah-password.php?id=<?= $id; ?>" class="btn btn-sm btn-outline-warning text-dark fw-medium">
                                <i class="bi bi-shield-lock me-1"></i> Ganti Password
                            </a>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                            <a href="index.php" class="btn btn-light px-4">Batal</a>
                            <button type="submit" name="update" class="btn btn-primary px-4 fw-medium">
                                <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
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
</body>
</html>

