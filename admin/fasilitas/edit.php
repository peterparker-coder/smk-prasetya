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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data fasilitas yang akan diedit menggunakan prepared statement
$stmt_get = mysqli_prepare($conn, "SELECT id, $col_nama AS nama_fasilitas, kategori, deskripsi, foto FROM fasilitas WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);
$res_get = mysqli_stmt_get_result($stmt_get);

if (!$res_get || mysqli_num_rows($res_get) === 0) {
    mysqli_stmt_close($stmt_get);
    header("Location: index.php");
    exit;
}

$fasilitas = mysqli_fetch_assoc($res_get);
mysqli_stmt_close($stmt_get);

if (isset($_POST['update'])) {
    $nama_fasilitas = trim($_POST['nama_fasilitas'] ?? '');
    $kategori       = trim($_POST['kategori'] ?? '');
    $deskripsi      = trim($_POST['deskripsi'] ?? '');
    $hapus_foto     = isset($_POST['hapus_foto']) && $_POST['hapus_foto'] === '1';

    if ($nama_fasilitas === '' || $kategori === '') {
        $error = "Nama fasilitas dan kategori wajib diisi!";
    } else {
        $upload_dir   = __DIR__ . '/../../assets/uploads/';
        $current_foto = $fasilitas['foto'];
        $foto_baru    = null;
        $update_foto  = false;

        // Cek apakah ada upload foto baru
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $error = "Terjadi kesalahan saat mengunggah foto baru (Kode error: " . $_FILES['foto']['error'] . ")!";
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
                    $error = "Format foto tidak didukung! Harap gunakan format JPG, JPEG, PNG, atau WEBP.";
                } elseif (!empty($mime_type) && !in_array($mime_type, $allowed_mime, true)) {
                    $error = "File yang diunggah bukan gambar valid ($mime_type)!";
                } elseif ($file_size > 5 * 1024 * 1024) {
                    $error = "Ukuran file foto terlalu besar! Maksimal 5 MB.";
                } else {
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $new_filename = bin2hex(random_bytes(16)) . '.' . $file_ext;
                    $destination  = $upload_dir . $new_filename;

                    if (move_uploaded_file($file_tmp, $destination)) {
                        // Hapus file foto lama fisik jika ada
                        if (!empty($current_foto) && file_exists($upload_dir . $current_foto)) {
                            @unlink($upload_dir . $current_foto);
                        }
                        $foto_baru = $new_filename;
                        $update_foto = true;
                    } else {
                        $error = "Gagal memindahkan file foto baru ke folder assets/uploads/!";
                    }
                }
            }
        } elseif ($hapus_foto) {
            // Hapus foto lama tanpa mengganti dengan baru
            if (!empty($current_foto) && file_exists($upload_dir . $current_foto)) {
                @unlink($upload_dir . $current_foto);
            }
            $foto_baru = null;
            $update_foto = true;
        }

        // Jalankan update jika tidak ada error via prepared statement
        if (empty($error)) {
            if ($update_foto) {
                $stmt_upd = mysqli_prepare(
                    $conn,
                    "UPDATE fasilitas SET $col_nama = ?, kategori = ?, deskripsi = ?, foto = ? WHERE id = ?"
                );
                if ($stmt_upd) {
                    mysqli_stmt_bind_param($stmt_upd, "ssssi", $nama_fasilitas, $kategori, $deskripsi, $foto_baru, $id);
                    $success = mysqli_stmt_execute($stmt_upd);
                    mysqli_stmt_close($stmt_upd);
                }
            } else {
                $stmt_upd = mysqli_prepare(
                    $conn,
                    "UPDATE fasilitas SET $col_nama = ?, kategori = ?, deskripsi = ? WHERE id = ?"
                );
                if ($stmt_upd) {
                    mysqli_stmt_bind_param($stmt_upd, "sssi", $nama_fasilitas, $kategori, $deskripsi, $id);
                    $success = mysqli_stmt_execute($stmt_upd);
                    mysqli_stmt_close($stmt_upd);
                }
            }

            if (!empty($success)) {
                header("Location: index.php?status=sukses_edit");
                exit;
            } else {
                $error = "Gagal memperbarui data fasilitas: " . htmlspecialchars(mysqli_error($conn));
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
    <title>Edit Fasilitas Sekolah - Admin SMKS Prasetya Makassar</title>
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
                <li class="breadcrumb-item active" aria-current="page">Edit Fasilitas</li>
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
                        <i class="bi bi-pencil-square text-primary me-2"></i>Edit Fasilitas Sekolah
                    </h4>
                    <p class="text-muted small mb-0">Perbarui data spesifikasi, nama ruang, kategori, atau foto fasilitas</p>
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
                            <input type="text" name="nama_fasilitas" class="form-control" value="<?= htmlspecialchars($_POST['nama_fasilitas'] ?? $fasilitas['nama_fasilitas']); ?>" required autofocus>
                            <small class="text-muted">Nama sarana atau prasarana sekolah.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori Fasilitas <span class="text-danger">*</span></label>
                            <input type="text" name="kategori" list="kategoriList" class="form-control" value="<?= htmlspecialchars($_POST['kategori'] ?? $fasilitas['kategori']); ?>" required>
                            <datalist id="kategoriList">
                                <option value="Laboratorium & Praktik">
                                <option value="Ruang Pembelajaran">
                                <option value="Sarana Olahraga">
                                <option value="Fasilitas Penunjang">
                                <option value="Ibadah & Kesehatan">
                            </datalist>
                            <small class="text-muted">Kategori klasifikasi fungsi sarana.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi Fasilitas</label>
                            <textarea name="deskripsi" class="form-control" rows="5"><?= htmlspecialchars($_POST['deskripsi'] ?? $fasilitas['deskripsi'] ?? ''); ?></textarea>
                            <small class="text-muted">Deskripsi lengkap fungsi dan peralatan yang tersedia.</small>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Foto & Pratinjau -->
                    <div class="col-lg-5">
                        <div class="bg-light p-4 rounded-3 border">
                            <label class="form-label fw-semibold d-block">Foto Dokumentasi Saat Ini</label>
                            
                            <div class="d-flex flex-column align-items-center mb-3">
                                <div id="previewContainer" class="mb-2">
                                    <?php
                                    $current_path = "../../assets/uploads/" . ($fasilitas['foto'] ?? '');
                                    $has_photo = !empty($fasilitas['foto']) && file_exists($current_path);
                                    ?>
                                    <img id="previewImg"
                                         src="<?= $has_photo ? htmlspecialchars($current_path) : 'https://placehold.co/400x250?text=Tanpa+Foto'; ?>"
                                         alt="<?= htmlspecialchars($fasilitas['nama_fasilitas']); ?>">
                                </div>
                                <small class="text-muted text-center" id="photoStatusText">
                                    <?= $has_photo ? htmlspecialchars($fasilitas['foto']) : 'Belum ada foto dokumentasi'; ?>
                                </small>
                            </div>

                            <label class="form-label small fw-semibold mt-2">Ganti Foto Baru</label>
                            <input type="file" name="foto" id="fotoInput" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            
                            <?php if ($has_photo) : ?>
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="hapus_foto" value="1" id="hapusFotoCheck">
                                    <label class="form-check-label small text-danger" for="hapusFotoCheck">
                                        <i class="bi bi-trash me-1"></i>Hapus foto ini (gunakan placeholder)
                                    </label>
                                </div>
                            <?php endif; ?>

                            <div class="form-text small mt-3 pt-2 border-top">
                                <i class="bi bi-info-circle me-1"></i>Format: <strong>JPG, JPEG, PNG, WEBP</strong><br>
                                <i class="bi bi-shield-check me-1"></i>Maksimal ukuran: <strong>5 MB</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="index.php" class="btn btn-light px-4">Batal</a>
                    <button type="submit" name="update" class="btn btn-primary px-4 fw-medium">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
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
        const photoStatusText = document.getElementById('photoStatusText');
        const hapusCheck = document.getElementById('hapusFotoCheck');

        if (fotoInput) {
            fotoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                        if (photoStatusText) photoStatusText.textContent = 'Foto baru terpilih: ' + file.name;
                    };
                    reader.readAsDataURL(file);
                    if (hapusCheck) hapusCheck.checked = false;
                }
            });
        }

        if (hapusCheck) {
            hapusCheck.addEventListener('change', function () {
                if (this.checked) {
                    previewImg.style.opacity = '0.3';
                    if (photoStatusText) photoStatusText.textContent = '(Foto akan dihapus saat disimpan)';
                    if (fotoInput) fotoInput.value = '';
                } else {
                    previewImg.style.opacity = '1';
                    if (photoStatusText) photoStatusText.textContent = '<?= $has_photo ? htmlspecialchars($fasilitas['foto']) : 'Belum ada foto'; ?>';
                }
            });
        }
    </script>
</body>
</html>

