<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$active_menu = 'alumni';
$base_admin  = '../';
$error       = '';

// Periksa nama kolom database sebelum menggunakannya (Ketentuan)
$cols_res = mysqli_query($conn, "SHOW COLUMNS FROM alumni");
$alumni_cols = [];
while ($c = mysqli_fetch_assoc($cols_res)) {
    $alumni_cols[] = $c['Field'];
}

$col_pekerjaan = in_array('pekerjaan', $alumni_cols) ? 'pekerjaan' : 'aktivitas';
$col_jurusan   = in_array('jurusan', $alumni_cols) ? 'jurusan' : 'nama_jurusan';
$col_tahun     = in_array('tahun_lulus', $alumni_cols) ? 'tahun_lulus' : 'tahun';
$has_aktivitas = in_array('aktivitas', $alumni_cols);
$has_testimoni = in_array('testimoni', $alumni_cols);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data alumni via prepared statement
$select_fields = "id, nama, $col_tahun AS tahun_lulus, $col_jurusan AS jurusan, $col_pekerjaan AS pekerjaan, foto" . ($has_testimoni ? ", testimoni" : "");
$stmt_get = mysqli_prepare($conn, "SELECT $select_fields FROM alumni WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);
$res_get = mysqli_stmt_get_result($stmt_get);

if (!$res_get || mysqli_num_rows($res_get) === 0) {
    mysqli_stmt_close($stmt_get);
    header("Location: index.php");
    exit;
}

$alumni = mysqli_fetch_assoc($res_get);
mysqli_stmt_close($stmt_get);

if (isset($_POST['update'])) {
    $nama        = trim($_POST['nama'] ?? '');
    $tahun_lulus = trim($_POST['tahun_lulus'] ?? '');
    $jurusan     = trim($_POST['jurusan'] ?? '');
    $pekerjaan   = trim($_POST['pekerjaan'] ?? '');
    $aktivitas   = trim($_POST['aktivitas'] ?? '');
    $testimoni   = trim($_POST['testimoni'] ?? '');
    $hapus_foto  = isset($_POST['hapus_foto']) && $_POST['hapus_foto'] === '1';

    if ($aktivitas === '') {
        $aktivitas = $pekerjaan;
    }

    if ($nama === '' || $tahun_lulus === '' || $jurusan === '' || $pekerjaan === '') {
        $error = "Nama, tahun lulus, jurusan, dan pekerjaan/aktivitas wajib diisi!";
    } else {
        $upload_dir   = __DIR__ . '/../../assets/uploads/';
        $current_foto = $alumni['foto'];
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
                    $error = "Ukuran foto terlalu besar! Maksimal ukuran file adalah 5 MB.";
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
                        $foto_baru   = $new_filename;
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
            $foto_baru   = null;
            $update_foto = true;
        }

        // Jalankan update via prepared statement jika tidak ada error
        if (empty($error)) {
            if ($update_foto) {
                if ($has_aktivitas && $has_testimoni) {
                    $stmt_upd = mysqli_prepare($conn, "UPDATE alumni SET nama = ?, $col_tahun = ?, $col_jurusan = ?, $col_pekerjaan = ?, aktivitas = ?, testimoni = ?, foto = ? WHERE id = ?");
                    if ($stmt_upd) {
                        mysqli_stmt_bind_param($stmt_upd, "sssssssi", $nama, $tahun_lulus, $jurusan, $pekerjaan, $aktivitas, $testimoni, $foto_baru, $id);
                        $success = mysqli_stmt_execute($stmt_upd);
                        mysqli_stmt_close($stmt_upd);
                    }
                } elseif ($has_testimoni) {
                    $stmt_upd = mysqli_prepare($conn, "UPDATE alumni SET nama = ?, $col_tahun = ?, $col_jurusan = ?, $col_pekerjaan = ?, testimoni = ?, foto = ? WHERE id = ?");
                    if ($stmt_upd) {
                        mysqli_stmt_bind_param($stmt_upd, "ssssssi", $nama, $tahun_lulus, $jurusan, $pekerjaan, $testimoni, $foto_baru, $id);
                        $success = mysqli_stmt_execute($stmt_upd);
                        mysqli_stmt_close($stmt_upd);
                    }
                } else {
                    $stmt_upd = mysqli_prepare($conn, "UPDATE alumni SET nama = ?, $col_tahun = ?, $col_jurusan = ?, $col_pekerjaan = ?, foto = ? WHERE id = ?");
                    if ($stmt_upd) {
                        mysqli_stmt_bind_param($stmt_upd, "sssssi", $nama, $tahun_lulus, $jurusan, $pekerjaan, $foto_baru, $id);
                        $success = mysqli_stmt_execute($stmt_upd);
                        mysqli_stmt_close($stmt_upd);
                    }
                }
            } else {
                if ($has_aktivitas && $has_testimoni) {
                    $stmt_upd = mysqli_prepare($conn, "UPDATE alumni SET nama = ?, $col_tahun = ?, $col_jurusan = ?, $col_pekerjaan = ?, aktivitas = ?, testimoni = ? WHERE id = ?");
                    if ($stmt_upd) {
                        mysqli_stmt_bind_param($stmt_upd, "ssssssi", $nama, $tahun_lulus, $jurusan, $pekerjaan, $aktivitas, $testimoni, $id);
                        $success = mysqli_stmt_execute($stmt_upd);
                        mysqli_stmt_close($stmt_upd);
                    }
                } elseif ($has_testimoni) {
                    $stmt_upd = mysqli_prepare($conn, "UPDATE alumni SET nama = ?, $col_tahun = ?, $col_jurusan = ?, $col_pekerjaan = ?, testimoni = ? WHERE id = ?");
                    if ($stmt_upd) {
                        mysqli_stmt_bind_param($stmt_upd, "sssssi", $nama, $tahun_lulus, $jurusan, $pekerjaan, $testimoni, $id);
                        $success = mysqli_stmt_execute($stmt_upd);
                        mysqli_stmt_close($stmt_upd);
                    }
                } else {
                    $stmt_upd = mysqli_prepare($conn, "UPDATE alumni SET nama = ?, $col_tahun = ?, $col_jurusan = ?, $col_pekerjaan = ? WHERE id = ?");
                    if ($stmt_upd) {
                        mysqli_stmt_bind_param($stmt_upd, "ssssi", $nama, $tahun_lulus, $jurusan, $pekerjaan, $id);
                        $success = mysqli_stmt_execute($stmt_upd);
                        mysqli_stmt_close($stmt_upd);
                    }
                }
            }

            if (!empty($success)) {
                header("Location: index.php?status=sukses_edit");
                exit;
            } else {
                $error = "Gagal memperbarui data alumni: " . htmlspecialchars(mysqli_error($conn));
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
    <title>Edit Data Alumni - Admin SMKS Prasetya Makassar</title>
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
            width: 140px;
            height: 160px;
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
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Data Alumni</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Alumni</li>
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
                        <i class="bi bi-pencil-square text-primary me-2"></i>Edit Data Alumni
                    </h4>
                    <p class="text-muted small mb-0">Perbarui profil kelulusan, karier saat ini, atau foto alumni</p>
                </div>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    <!-- Kolom Kiri: Input Utama -->
                    <div class="col-lg-8">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap Alumni <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($_POST['nama'] ?? $alumni['nama']); ?>" required autofocus>
                            <small class="text-muted">Nama lengkap alumni.</small>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Tahun Lulus <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_lulus" class="form-control" min="1990" max="<?= date('Y'); ?>" value="<?= htmlspecialchars($_POST['tahun_lulus'] ?? $alumni['tahun_lulus']); ?>" required>
                                <small class="text-muted">Tahun kelulusan sekolah.</small>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Program Keahlian / Jurusan <span class="text-danger">*</span></label>
                                <?php $selected_j = $_POST['jurusan'] ?? $alumni['jurusan']; ?>
                                <select name="jurusan" class="form-select" required>
                                    <option value="Teknik Komputer dan Jaringan (TKJ)" <?= ($selected_j === 'Teknik Komputer dan Jaringan (TKJ)') ? 'selected' : ''; ?>>Teknik Komputer dan Jaringan (TKJ)</option>
                                    <option value="Rekayasa Perangkat Lunak (RPL)" <?= ($selected_j === 'Rekayasa Perangkat Lunak (RPL)') ? 'selected' : ''; ?>>Rekayasa Perangkat Lunak (RPL)</option>
                                    <option value="Otomatisasi & Tata Kelola Perkantoran (OTKP)" <?= ($selected_j === 'Otomatisasi & Tata Kelola Perkantoran (OTKP)') ? 'selected' : ''; ?>>Otomatisasi & Tata Kelola Perkantoran (OTKP)</option>
                                    <option value="Akuntansi dan Keuangan Lembaga (AKL)" <?= ($selected_j === 'Akuntansi dan Keuangan Lembaga (AKL)') ? 'selected' : ''; ?>>Akuntansi dan Keuangan Lembaga (AKL)</option>
                                    <option value="Teknik & Bisnis Sepeda Motor (TBSM)" <?= ($selected_j === 'Teknik & Bisnis Sepeda Motor (TBSM)') ? 'selected' : ''; ?>>Teknik & Bisnis Sepeda Motor (TBSM)</option>
                                </select>
                                <small class="text-muted">Jurusan kelulusan.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pekerjaan / Aktivitas Saat Ini <span class="text-danger">*</span></label>
                            <input type="text" name="pekerjaan" class="form-control" value="<?= htmlspecialchars($_POST['pekerjaan'] ?? $alumni['pekerjaan']); ?>" required>
                            <small class="text-muted">Profesi, instansi kerja, atau aktivitas pendidikan tinggi saat ini.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Testimoni / Kesan Selama Bersekolah (Opsional)</label>
                            <textarea name="testimoni" class="form-control" rows="4"><?= htmlspecialchars($_POST['testimoni'] ?? $alumni['testimoni'] ?? ''); ?></textarea>
                            <small class="text-muted">Kutipan kesan dan pesan alumni.</small>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Foto & Pratinjau -->
                    <div class="col-lg-4">
                        <div class="bg-light p-4 rounded-3 border">
                            <label class="form-label fw-semibold d-block">Foto Alumni Saat Ini</label>
                            
                            <div class="d-flex flex-column align-items-center mb-3">
                                <div id="previewContainer" class="mb-2">
                                    <?php
                                    $current_path = "../../assets/uploads/" . ($alumni['foto'] ?? '');
                                    $has_photo = !empty($alumni['foto']) && file_exists($current_path);
                                    ?>
                                    <img id="previewImg"
                                         src="<?= $has_photo ? htmlspecialchars($current_path) : 'https://placehold.co/140x160?text=Tanpa+Foto'; ?>"
                                         alt="<?= htmlspecialchars($alumni['nama']); ?>">
                                </div>
                                <small class="text-muted text-center" id="photoStatusText">
                                    <?= $has_photo ? htmlspecialchars($alumni['foto']) : 'Belum ada foto alumni'; ?>
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
                    if (photoStatusText) photoStatusText.textContent = '<?= $has_photo ? htmlspecialchars($alumni['foto']) : 'Belum ada foto'; ?>';
                }
            });
        }
    </script>
</body>
</html>

