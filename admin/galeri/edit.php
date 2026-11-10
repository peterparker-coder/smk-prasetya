
<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

$error = '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM galeri WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $nama_file = $data['nama_file'];
    $file_baru = '';

    if ($judul === '') {
        $error = 'Judul foto wajib diisi.';
    } elseif (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {

        $file = $_FILES['gambar'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Terjadi kesalahan saat mengunggah foto.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $extensions = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (!in_array($mime, $allowed_types, true)) {
                $error = 'Format foto harus JPG, PNG, atau WEBP.';
            } elseif ($file['size'] > $max_size) {
                $error = 'Ukuran foto maksimal 5 MB.';
            } else {
                $file_baru = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                $tujuan = '../../assets/uploads/' . $file_baru;

                if (move_uploaded_file($file['tmp_name'], $tujuan)) {
                    $nama_file = $file_baru;
                } else {
                    $error = 'Gagal mengunggah foto baru.';
                }
            }
        }
    }

    if ($error === '') {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE galeri SET judul = ?, deskripsi = ?, nama_file = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "sssi", $judul, $deskripsi, $nama_file, $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            // Hapus foto lama setelah data berhasil diperbarui
            if ($file_baru !== '' && !empty($data['nama_file'])) {
                $file_lama = basename($data['nama_file']);
                $path_lama = '../../assets/uploads/' . $file_lama;

                if (is_file($path_lama)) {
                    unlink($path_lama);
                }
            }

            header("Location: index.php?status=sukses_edit");
            exit;
        } else {
            mysqli_stmt_close($stmt);

            // Hapus foto baru jika update database gagal
            if ($file_baru !== '') {
                $path_baru = '../../assets/uploads/' . $file_baru;

                if (is_file($path_baru)) {
                    unlink($path_baru);
                }
            }

            $error = 'Gagal memperbarui data galeri.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Foto Galeri - Admin SMKS Prasetya Makassar</title>

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

        .preview-image {
            width: 180px;
            height: 130px;
            object-fit: cover;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<?php
$active_menu = 'galeri';
$base_admin = '../';
include '../navbar.php';
?>

<div class="container my-4">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="../index.php" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="index.php" class="text-decoration-none">Galeri</a>
            </li>
            <li class="breadcrumb-item active">Edit Foto</li>
        </ol>
    </nav>

    <div class="card card-custom bg-white p-4">

        <div class="mb-4">
            <h4 class="fw-bold mb-1">
                <i class="bi bi-pencil-square text-primary me-2"></i>
                Edit Foto Galeri
            </h4>
            <p class="text-muted small mb-0">
                Perbarui informasi dan dokumentasi foto sekolah.
            </p>
        </div>

        <?php if ($error !== '') : ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label fw-medium">Judul Foto</label>
                <input
                    type="text"
                    name="judul"
                    class="form-control"
                    maxlength="100"
                    value="<?= htmlspecialchars($_POST['judul'] ?? $data['judul']); ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Deskripsi</label>
                <textarea
                    name="deskripsi"
                    class="form-control"
                    rows="4"
                ><?= htmlspecialchars($_POST['deskripsi'] ?? $data['deskripsi'] ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium d-block">Foto Saat Ini</label>

                <img
                    src="../../assets/uploads/<?= htmlspecialchars($data['nama_file']); ?>"
                    alt="Foto Galeri"
                    class="preview-image border mb-2"
                >
            </div>

            <div class="mb-4">
                <label class="form-label fw-medium">Ganti Foto</label>
                <input
                    type="file"
                    name="gambar"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                >
                <small class="text-muted">
                    Kosongkan jika tidak ingin mengganti foto. Maksimal 5 MB.
                </small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="simpan" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Kembali
                </a>
            </div>

        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
