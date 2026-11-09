
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

if (isset($_POST['simpan'])) {
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $isi = trim($_POST['isi'] ?? '');

    if ($penulis === '') {
        $penulis = $_SESSION['admin_nama'] ?? 'Admin';
    }

    if ($judul === '' || $isi === '') {
        $error = "Judul berita dan isi berita wajib diisi!";
    } elseif (
        !isset($_FILES['gambar']) ||
        $_FILES['gambar']['error'] !== UPLOAD_ERR_OK
    ) {
        $error = "Silakan pilih gambar cover berita!";
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

            if (
                !is_dir($upload_dir) &&
                !mkdir($upload_dir, 0755, true)
            ) {
                $error = "Folder upload gagal dibuat!";
            } else {
                $new_filename = bin2hex(random_bytes(16)) . '.' . $file_ext;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($file_tmp, $destination)) {
                    $slug = strtolower($judul);
                    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
                    $slug = trim($slug, '-');
                    $slug .= '-' . time();

                    $tanggal = date('Y-m-d H:i:s');

                    $stmt = mysqli_prepare(
                        $conn,
                        "INSERT INTO berita
                        (judul, slug, isi, gambar, penulis, tanggal)
                        VALUES (?, ?, ?, ?, ?, ?)"
                    );

                    if ($stmt) {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "ssssss",
                            $judul,
                            $slug,
                            $isi,
                            $new_filename,
                            $penulis,
                            $tanggal
                        );

                        if (mysqli_stmt_execute($stmt)) {
                            mysqli_stmt_close($stmt);

                            header("Location: index.php?status=sukses_tambah");
                            exit;
                        } else {
                            $error = "Gagal menyimpan berita: " .
                                mysqli_stmt_error($stmt);

                            mysqli_stmt_close($stmt);
                            unlink($destination);
                        }
                    } else {
                        $error = "Gagal menyiapkan query database: " .
                            mysqli_error($conn);

                        unlink($destination);
                    }
                } else {
                    $error = "Gagal mengunggah gambar ke server!";
                }
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

    <title>Tambah Berita - Admin SMKS Prasetya Makassar</title>

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
                    Tambah Berita
                </li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-9">

                <div class="card card-custom bg-white p-4">

                    <div class="border-bottom pb-3 mb-4">
                        <h4 class="fw-bold mb-1">
                            <i class="bi bi-pencil-square text-primary me-2"></i>
                            Publikasikan Berita Baru
                        </h4>

                        <p class="text-muted small mb-0">
                            Tulis artikel, kegiatan, atau pengumuman terbaru
                            untuk ditampilkan di website sekolah.
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
                                placeholder="Contoh: Siswa SMKS Prasetya Meraih Juara 1 Lomba Web Design"
                                required
                                value="<?= htmlspecialchars($_POST['judul'] ?? ''); ?>"
                            >
                        </div>

                        <div class="row mb-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Penulis / Sumber
                                </label>

                                <input
                                    type="text"
                                    name="penulis"
                                    class="form-control"
                                    value="<?= htmlspecialchars($_POST['penulis'] ?? ($_SESSION['admin_nama'] ?? 'Admin')); ?>"
                                >
                            </div>

                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label fw-semibold">
                                    Gambar Utama / Cover
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="file"
                                    name="gambar"
                                    class="form-control"
                                    accept="image/jpeg,image/png,image/webp"
                                    required
                                >

                                <div class="form-text">
                                    Format: JPG, PNG, WEBP. Maksimal 5 MB.
                                </div>
                            </div>

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
                                placeholder="Tuliskan isi berita secara lengkap di sini..."
                                required
                            ><?= htmlspecialchars($_POST['isi'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">

                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Batal
                            </a>

                            <button
                                type="submit"
                                name="simpan"
                                class="btn btn-primary fw-semibold px-4"
                            >
                                <i class="bi bi-cloud-arrow-up me-1"></i>
                                Terbitkan Berita
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
