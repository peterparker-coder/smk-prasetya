
<?php
session_start();

require_once __DIR__ . '/config/koneksi.php';

// Jika sudah login, arahkan ke dashboard
if (
    isset($_SESSION['admin_logged_in']) &&
    $_SESSION['admin_logged_in'] === true
) {
    header("Location: admin/index.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Silakan isi username dan password!";
    } else {
        // Cari akun admin menggunakan prepared statement
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, password, nama_lengkap
             FROM admin
             WHERE username = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($data = mysqli_fetch_assoc($result)) {
            $hashed = $data['password'];

            $isValid = false;

            // Verifikasi password modern
            if (password_verify($password, $hashed)) {
                $isValid = true;

                // Perbarui hash jika algoritma perlu ditingkatkan
                if (password_needs_rehash($hashed, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);

                    $update = mysqli_prepare(
                        $conn,
                        "UPDATE admin SET password = ? WHERE id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $update,
                        "si",
                        $newHash,
                        $data['id']
                    );

                    mysqli_stmt_execute($update);
                    mysqli_stmt_close($update);
                }
            }
            // Dukungan sementara untuk password MD5 lama
            elseif (md5($password) === $hashed) {
                $isValid = true;

                // Ubah MD5 menjadi password_hash setelah login berhasil
                $newHash = password_hash($password, PASSWORD_DEFAULT);

                $update = mysqli_prepare(
                    $conn,
                    "UPDATE admin SET password = ? WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "si",
                    $newHash,
                    $data['id']
                );

                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
            }

            if ($isValid) {
                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $data['id'];
                $_SESSION['admin_username'] = $data['username'];
                $_SESSION['admin_nama'] = $data['nama_lengkap'];

                header("Location: admin/index.php");
                exit;
            } else {
                $error = "Password yang Anda masukkan salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SMKS Prasetya Makassar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
        }

        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .login-header {
            background: #ffffff;
            padding: 30px 30px 10px;
            text-align: center;
        }

        .school-icon {
            width: 70px;
            height: 70px;
            background: #e8f0fe;
            color: #1e3c72;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 32px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">

            <div class="card login-card bg-white p-4">

                <div class="login-header">
                    <div class="school-icon">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>

                    <h4 class="fw-bold mb-1 text-dark">Panel Admin</h4>
                    <p class="text-muted small">SMKS Prasetya Makassar</p>
                </div>

                <div class="card-body pt-2">

                    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'logout') : ?>
                        <div class="alert alert-success alert-dismissible fade show py-2 small">
                            <i class="bi bi-check-circle me-1"></i>
                            Anda telah berhasil logout.

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show py-2 small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <?= htmlspecialchars($error); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Username</label>

                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-person"></i>
                                </span>

                                <input
                                    type="text"
                                    name="username"
                                    class="form-control"
                                    placeholder="Masukkan username"
                                    required
                                    autofocus
                                    value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>"
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Password</label>

                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-lock"></i>
                                </span>

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Masukkan password"
                                    required
                                >
                            </div>
                        </div>

                        <button
                            type="submit"
                            name="login"
                            class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mt-2"
                        >
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Masuk Sekarang
                        </button>

                    </form>

                    <div class="text-center mt-4 pt-2 border-top">
                        <a href="index.php" class="text-decoration-none small text-muted">
                            <i class="bi bi-arrow-left me-1"></i>
                            Kembali ke Beranda Sekolah
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
