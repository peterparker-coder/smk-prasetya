<?php
/**
 * Sistem Pencatatan & Statistik Pengunjung
 * SMKS Prasetya Makassar
 */

if (!function_exists('catat_pengunjung')) {
    function catat_pengunjung($conn, $halaman = 'Beranda') {
        if (!$conn) {
            return;
        }

        // 1. Ambil alamat IP pengunjung secara akurat
        $ip = '127.0.0.1';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipList[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Bersihkan dan batasi panjang string
        $ip         = substr(trim($ip), 0, 50);
        $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);
        $halaman    = substr(trim($halaman), 0, 255);
        $tanggal    = date('Y-m-d');

        // 2. Pastikan tabel pengunjung sudah tersedia
        mysqli_query($conn, "
            CREATE TABLE IF NOT EXISTS `pengunjung` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `ip_address` VARCHAR(50) NOT NULL,
                `user_agent` VARCHAR(255) DEFAULT NULL,
                `halaman` VARCHAR(255) DEFAULT 'Beranda',
                `tanggal` DATE NOT NULL,
                `hits` INT DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_tanggal` (`tanggal`),
                INDEX `idx_ip_tanggal` (`ip_address`, `tanggal`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 3. Periksa apakah pengunjung dengan IP yang sama sudah tercatat hari ini
        $stmt = mysqli_prepare($conn, "SELECT id, hits FROM pengunjung WHERE ip_address = ? AND tanggal = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $ip, $tanggal);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($res)) {
                // Pengunjung sudah tercatat hari ini, tambahkan jumlah tayangan (hits)
                mysqli_stmt_close($stmt);

                $update_stmt = mysqli_prepare(
                    $conn, 
                    "UPDATE pengunjung SET hits = hits + 1, user_agent = ?, halaman = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
                );
                if ($update_stmt) {
                    mysqli_stmt_bind_param($update_stmt, "ssi", $user_agent, $halaman, $row['id']);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                }
            } else {
                // Pengunjung unik baru pada hari ini
                mysqli_stmt_close($stmt);

                $insert_stmt = mysqli_prepare(
                    $conn, 
                    "INSERT INTO pengunjung (ip_address, user_agent, halaman, tanggal, hits) VALUES (?, ?, ?, ?, 1)"
                );
                if ($insert_stmt) {
                    mysqli_stmt_bind_param($insert_stmt, "ssss", $ip, $user_agent, $halaman, $tanggal);
                    mysqli_stmt_execute($insert_stmt);
                    mysqli_stmt_close($insert_stmt);
                }
            }
        }
    }
}
