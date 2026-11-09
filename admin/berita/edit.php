
<?php
session_start();

if (
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/koneksi.php';

$active_menu = 'berita';
$base_admin = '../';
$error = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data berita
$stmt = mysqli_prepare($conn, "SELECT * FROM berita WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$row) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['update'])) {
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $isi = trim($_POST['isi'] ?? '');

    if ($penulis === '') {
        $penulis = $_SESSION['admin_nama'] ?? 'Admin';
    }

    if ($judul === '' || $isi === '') {
        $error = "Judul berita dan isi berita wajib diisi!";
    } else {
        $gambar_nama = $row['gambar'];
        $gambar_lama = $row['gambar'];
        $gambar_baru = false;

        // Proses gambar jika pengguna memilih file baru
        if (
            isset($_FILES['gambar']) &&
            $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
                $error = "Terjadi kesalahan saat mengunggah gambar!";
            } else {
                $file_tmp = $_FILES['gambar']['tmp_name'];
                $file_size = $_FILES['gambar']['size'];

                $file_ext = strtolower(
                    pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION)
                );

                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);

                $allowed_mime = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (!in_array($file_ext, $allowed_ext, true)) {
                    $error = "Format gambar harus JPG, JPEG, PNG, atau WEBP.";
                } elseif (!in_array($mime_type, $allowed_mime, true)) {
                    $error = "File yang diunggah bukan gambar yang valid!";
                } elseif ($file_size > 5 * 1024 * 1024) {
                    $error = "Ukuran gambar maksimal 5 MB!";
                } else {
                    $upload_dir = "../../assets/uploads/";

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    if (!is_dir($upload_dir)) {
                        $error = "Folder upload tidak tersedia!";
                    } else {
                        $new_filename = bin2hex(random_bytes(16)) . '.' . $file_ext;
                        $destination = $upload_dir . $new_filename;

                        if (move_uploaded_file($file_tmp, $destination)) {
                            $gambar_nama = $new_filename;
                            $gambar_baru = true;
                        } else {
                            $error = "Gagal mengunggah gambar baru!";
                        }
                    }
                }
            }
        }

        if ($error === '') {
            // Buat slug baru dari judul
            $slug = strtolower($judul);
            $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
            $slug = trim($slug, '-');
            $slug .= '-' . time();

            $update = mysqli_prepare(
                $conn,
                "UPDATE berita
                 SET judul = ?, slug = ?, penulis = ?, isi = ?, gambar = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "sssssi",
                $judul,
                $slug,
                $penulis,
                $isi,
                $gambar_nama,
                $id
            );

            if (mysqli_stmt_execute($update)) {
                mysqli_stmt_close($update);

                // Hapus gambar lama setelah database berhasil diperbarui
                if (
                    $gambar_baru &&
                    !empty($gambar_lama)
                ) {
                    $old_file = "../../assets/uploads/" . basename($gambar_lama);

                    if (is_file($old_file)) {
                        unlink($old_file);
                    }
                }

                header("Location: index.php?status=sukses_edit");
                exit;
            } else {
                $error = "Gagal memperbarui berita: " .
                    mysqli_stmt_error($update);

                mysqli_stmt_close($update);

                // Hapus gambar baru jika database gagal diperbarui
                if ($gambar_baru && is_file($destination)) {
                    unlink($destination);
                }
            }
        }
    }
}

// Ambil kembali nilai terbaru jika form gagal disimpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $row['judul'] = $_POST['judul'] ?? $row['judul'];
    $row['penulis'] = $_POST['penulis'] ?? $row['penulis'];
    $row['isi'] = $_POST['isi'] ?? $row['isi'];
    $row['gambar'] = $gambar_nama ?? $row['gambar'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Berita - Admin SMKS Prasetya Makassar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }
    </style>
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container my-4">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="../index.php" class="text-decoration-none">
                        Dashboard
                    </a>
                </li>

                <li class="breadcrumb-item">
                    <a href="index.php" class="text-decoration-none">
                        Kelola Berita
                    </a>
                </li>

                <li class="breadcrumb-item active" aria-current="page">
                    Edit Berita
                </li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-9">

                <div class="card card-custom bg-white p-4">

                    <div class="border-bottom pb-3 mb-4">
                        <h4 class="fw-bold mb-1">
                            <i class="bi bi-pencil-square text-warning me-2"></i>
                            Edit Berita
                        </h4>

                        <p class="text-muted small mb-0">
                            Perbarui konten berita atau ganti foto sampul berita.
                        </p>
                    </div>

                    <?php if (!empty($error)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <?= htmlspecialchars($error); ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                            ></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Judul Berita
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="judul"
                                class="form-control"
                                value="<?= htmlspecialchars($row['judul'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="row mb-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Penulis
                                </label>

                                <input
                                    type="text"
                                    name="penulis"
                                    class="form-control"
                                    value="<?= htmlspecialchars($row['penulis'] ?? ''); ?>"
                                >
                            </div>

                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label fw-semibold">
                                    Ganti Gambar (Opsional)
                                </label>

                                <input
                                    type="file"
                                    name="gambar"
                                    class="form-control"
                                    accept="image/jpeg,image/png,image/webp"
                                >

                                <div class="form-text">
                                    Biarkan kosong jika tidak ingin mengubah gambar.
                                    Format JPG, PNG, WEBP. Maksimal 5 MB.
                                </div>
                            </div>

                        </div>

                        <div class="mb-3 p-3 bg-light rounded border">
                            <label class="form-label small fw-semibold text-secondary d-block">
                                Gambar Saat Ini:
                            </label>

                            <?php if (!empty($row['gambar'])) : ?>
                                <img
                                    src="../../assets/uploads/<?= rawurlencode(basename($row['gambar'])); ?>"
                                    alt="Cover Saat Ini"
                                    class="rounded shadow-sm"
                                    style="max-height: 140px; max-width: 250px; object-fit: cover;"
                                    onerror="this.src='https://placehold.co/200x120?text=Gambar+Kosong'"
                                >

                                <span class="ms-2 small text-muted">
                                    <?= htmlspecialchars($row['gambar']); ?>
                                </span>
                            <?php else : ?>
                                <p class="text-muted small mb-0">
                                    Belum ada gambar.
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Isi Berita Lengkap
                                <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="isi"
                                class="form-control"
                                rows="8"
                                required
                            ><?= htmlspecialchars($row['isi'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">

                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Batal
                            </a>

                            <button
                                type="submit"
                                name="update"
                                class="btn btn-warning fw-semibold px-4 text-dark"
                            >
                                <i class="bi bi-check2-circle me-1"></i>
                                Simpan Perubahan
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
