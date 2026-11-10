
<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/koneksi.php';

$active_menu = 'galeri';
$base_admin = '../';

// Ambil semua data galeri
$query = mysqli_query($conn, "SELECT * FROM galeri ORDER BY tanggal_upload DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Galeri - Admin SMKS Prasetya Makassar</title>

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
                    <a href="../index.php" class="text-decoration-none">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    Kelola Galeri
                </li>
            </ol>
        </nav>

        <?php if (isset($_GET['status'])) : ?>

            <?php if ($_GET['status'] == 'sukses_tambah') : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>
                    Foto berhasil ditambahkan ke galeri!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php elseif ($_GET['status'] == 'sukses_edit') : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>
                    Data galeri berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php elseif ($_GET['status'] == 'sukses_hapus') : ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-trash me-1"></i>
                    Foto berhasil dihapus dari galeri!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <div class="card card-custom bg-white p-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-images text-primary me-2"></i>
                        Galeri Kegiatan Sekolah
                    </h4>
                    <p class="text-muted small mb-0">
                        Kelola dokumentasi kegiatan dan aktivitas SMKS Prasetya Makassar.
                    </p>
                </div>

                <a href="tambah.php" class="btn btn-primary fw-medium">
                    <i class="bi bi-plus-lg me-1"></i>
                    Tambah Foto
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 120px;">Foto</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th style="width: 150px;">Tanggal Upload</th>
                            <th class="text-center" style="width: 130px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $no = 1;

                        if ($query && mysqli_num_rows($query) > 0) :
                            while ($row = mysqli_fetch_assoc($query)) :
                        ?>

                            <tr>
                                <td class="text-center fw-semibold text-secondary">
                                    <?= $no++; ?>
                                </td>

                                <td>
                                    <img
                                        src="../../assets/uploads/<?= htmlspecialchars($row['nama_file']); ?>"
                                        alt="Foto Galeri"
                                        class="rounded object-fit-cover shadow-sm"
                                        width="100"
                                        height="65"
                                        onerror="this.src='https://placehold.co/100x80?text=No+Image'"
                                    >
                                </td>

                                <td>
                                    <span class="fw-bold text-dark">
                                        <?= htmlspecialchars($row['judul']); ?>
                                    </span>
                                </td>

                                <td>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($row['deskripsi'] ?? '-'); ?>
                                    </small>
                                </td>

                                <td class="small text-secondary text-nowrap">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= date('d M Y', strtotime($row['tanggal_upload'])); ?>
                                </td>

                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">

                                        <a
                                            href="edit.php?id=<?= (int) $row['id']; ?>"
                                            class="btn btn-outline-warning"
                                            title="Edit Galeri"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <a
                                            href="hapus.php?id=<?= (int) $row['id']; ?>"
                                            class="btn btn-outline-danger"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus foto ini?')"
                                            title="Hapus Galeri"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </a>

                                    </div>
                                </td>
                            </tr>

                        <?php
                            endwhile;
                        else :
                        ?>

                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-images fs-2 d-block mb-2"></i>
                                    Belum ada foto di galeri.
                                    Silakan klik tombol Tambah Foto.
                                </td>
                            </tr>

                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
