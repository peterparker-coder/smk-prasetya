<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Proteksi akses hanya untuk admin yang sedang login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'status'  => 'unauthorized',
        'message' => 'Sesi admin tidak valid atau telah berakhir.'
    ]);
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';

// 1. Ambil jumlah pesan belum dibaca
$q_unread = mysqli_query($conn, "SELECT COUNT(*) as total FROM pesan WHERE status = 'belum dibaca'");
$d_unread = mysqli_fetch_assoc($q_unread);
$unread_count = (int)($d_unread['total'] ?? 0);

// 2. Ambil total seluruh pesan dan ID pesan tertinggi
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total, COALESCE(MAX(id), 0) as max_id FROM pesan");
$d_total = mysqli_fetch_assoc($q_total);
$total_count = (int)($d_total['total'] ?? 0);
$max_id      = (int)($d_total['max_id'] ?? 0);

// 3. Ambil rincian pesan belum dibaca paling baru
$latest = null;
if ($unread_count > 0) {
    $q_latest = mysqli_query(
        $conn, 
        "SELECT id, nama, email, subjek, pesan, tanggal_kirim 
         FROM pesan 
         WHERE status = 'belum dibaca' 
         ORDER BY id DESC 
         LIMIT 1"
    );
    if ($row = mysqli_fetch_assoc($q_latest)) {
        $waktu_raw = $row['tanggal_kirim'] ?? '';
        $waktu_fmt = !empty($waktu_raw) ? date('d/m/Y H:i', strtotime($waktu_raw)) . ' WITA' : 'Baru saja';

        $latest = [
            'id'     => (int)$row['id'],
            'nama'   => htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'),
            'email'  => htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'),
            'subjek' => htmlspecialchars($row['subjek'], ENT_QUOTES, 'UTF-8'),
            'pesan'  => htmlspecialchars($row['pesan'], ENT_QUOTES, 'UTF-8'),
            'waktu'  => $waktu_fmt
        ];
    }
}

// 4. Keluarkan respon JSON
echo json_encode([
    'status'        => 'success',
    'unread_count'  => $unread_count,
    'total_count'   => $total_count,
    'max_id'        => $max_id,
    'latest_unread' => $latest,
    'server_time'   => date('H:i:s')
]);
exit;

