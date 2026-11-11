<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$active_menu = 'fasilitas';
$base_admin  = '../';
$error       = '';

// Periksa struktur tabel sebelum menggunakan nama kolom (Ketentuan 13)
$col_nama = 'nama_fasilitas';
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM fasilitas LIKE 'nama_fasilitas'");
if (!$check_col || mysqli_num_rows($check_col) == 0) {
    $col_nama = 'nama';
}

if (isset($_POST['simpan'])) {
    $nama_fasilitas = trim($_POST['nama_fasilitas'] ?? '');
    $kategori       = trim($_POST['kategori'] ?? '');
    $deskripsi      = trim($_POST['deskripsi'] ?? '');

    if ($nama_fasilitas === '' || $kategori === '') {
        $error = "Nama fasilitas dan kategori wajib diisi!";
    } else {
        $new_filename = null;

        // Cek apakah ada file foto yang diunggah
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $error = "Terjadi kesalahan saat mengunggah foto (Kode error: " . $_FILES['foto']['error'] . ")!";
            } else {
                $file_tmp  = $_FILES['foto']['tmp_name'];
                $file_size = $_FILES['foto']['size'];
                $file_name = $_FILES['foto']['name'];
                $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

                // Validasi MIME type
                $mime_type = '';
                if (function_exists('finfo_open')) {
                    $finfo     = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $file_tmp);
                    finfo_close($finfo);
                } elseif (function_exists('mime_content_type')) {
                    $mime_type = mime_content_type($file_tmp);
                }

                $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];

                if (!in_array($file_ext, $allowed_ext, true)) {
                    $error = "Format foto tidak didukung! Harap unggah file dengan format JPG, JPEG, PNG, atau WEBP.";
                } elseif (!empty($mime_type) && !in_array($mime_type, $allowed_mime, true)) {
                    $error = "File yang diunggah bukan gambar valid ($mime_type)!";
                } elseif ($file_size > 5 * 1024 * 1024) {
                    $error = "Ukuran foto terlalu besar! Maksimal ukuran file adalah 5 MB.";
                } else {
                    $upload_dir = __DIR__ . '/../../assets/uploads/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $new_filename = bin2hex(random_bytes(16)) . '.' . $file_ext;
                    $destination  = $upload_dir . $new_filename;

                    if (!move_uploaded_file($file_tmp, $destination)) {
                        $error = "Gagal memindahkan file foto ke folder assets/uploads/!";
                        $new_filename = null;
                    }
                }
            }
        }

        // Jika tidak ada error, simpan data menggunakan prepared statement
        if (empty($error)) {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO fasilitas ($col_nama, kategori, deskripsi, foto) VALUES (?, ?, ?, ?)"
            );

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssss", $nama_fasilitas, $kategori, $deskripsi, $new_filename);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: index.php?status=sukses_tambah");
                    exit;
                } else {
                    $error = "Gagal menyimpan data ke database: " . htmlspecialchars(mysqli_stmt_error($stmt));
                    mysqli_stmt_close($stmt);
                }
            } else {
                $error = "Gagal menyiapkan query prepared statement: " . htmlspecialchars(mysqli_error($conn));
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
    <title>Tambah Fasilitas Sekolah - Admin SMKS Prasetya Makassar</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        #previewContainer {
            width: 100%;
            height: 180px;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: #f8fafc;
        }
        #previewImg {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }
    </style>
</head>
<body>

    <!-- Include Navbar Admin -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <div class="container my-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Fasilitas Sekolah</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Fasilitas</li>
            </ol>
        </nav>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card card-custom bg-white p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-building-add text-primary me-2"></i>Tambah Fasilitas Sekolah
                    </h4>
                    <p class="text-muted small mb-0">Lengkapi formulir untuk menambahkan sarana atau prasarana baru</p>
                </div>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    <!-- Kolom Kiri: Input Data Utama -->
                    <div class="col-lg-7">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Fasilitas <span class="text-danger">*</span></label>
                            <input type="text" name="nama_fasilitas" class="form-control" placeholder="Contoh: Laboratorium Jaringan Fiber Optic" value="<?= htmlspecialchars($_POST['nama_fasilitas'] ?? ''); ?>" required autofocus>
                            <small class="text-muted">Nama ruang, laboratorium, sarana, atau prasarana sekolah.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori Fasilitas <span class="text-danger">*</span></label>
                            <input type="text" name="kategori" list="kategoriList" class="form-control" placeholder="Pilih atau ketik kategori..." value="<?= htmlspecialchars($_POST['kategori'] ?? ''); ?>" required>
                            <datalist id="kategoriList">
                                <option value="Laboratorium & Praktik">
                                <option value="Ruang Pembelajaran">
                                <option value="Sarana Olahraga">
                                <option value="Fasilitas Penunjang">
                                <option value="Ibadah & Kesehatan">
                            </datalist>
                            <small class="text-muted">Pilih dari rekomendasi atau ketik kategori baru yang sesuai.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi Fasilitas</label>
                            <textarea name="deskripsi" class="form-control" rows="5" placeholder="Tuliskan spesifikasi, kelengkapan alat, fungsi, dan kapasitas fasilitas ini..."><?= htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
                            <small class="text-muted">Deskripsi ini akan ditampilkan pada kartu informasi fasilitas di website profil.</small>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Upload Foto & Pratinjau -->
                    <div class="col-lg-5">
                        <div class="bg-light p-4 rounded-3 border">
                            <label class="form-label fw-semibold d-block">Foto Dokumentasi Fasilitas</label>
                            
                            <div class="d-flex flex-column align-items-center mb-3">
                                <div id="previewContainer" class="mb-2">
                                    <i id="previewPlaceholder" class="bi bi-image fs-1 text-secondary"></i>
                                    <img id="previewImg" alt="Pratinjau Foto Fasilitas">
                                </div>
                                <small class="text-muted text-center">Pratinjau Foto Fasilitas</small>
                            </div>

                            <input type="file" name="foto" id="fotoInput" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            <div class="form-text small mt-2">
                                <i class="bi bi-info-circle me-1"></i>Format: <strong>JPG, JPEG, PNG, WEBP</strong><br>
                                <i class="bi bi-shield-check me-1"></i>Maksimal ukuran: <strong>5 MB</strong><br>
                                <i class="bi bi-aspect-ratio me-1"></i>Rekomendasi rasio: <strong>16:9 atau 4:3 (Lansekap)</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="index.php" class="btn btn-light px-4">Batal</a>
                    <button type="submit" name="simpan" class="btn btn-primary px-4 fw-medium">
                        <i class="bi bi-save me-1"></i> Simpan Fasilitas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const fotoInput = document.getElementById('fotoInput');
        const previewImg = document.getElementById('previewImg');
        const placeholder = document.getElementById('previewPlaceholder');

        if (fotoInput) {
            fotoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                        placeholder.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewImg.src = '';
                    previewImg.style.display = 'none';
                    placeholder.style.display = 'block';
                }
            });
        }
    </script>
</body>
</html>

