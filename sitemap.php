<?php
/**
 * sitemap.php
 * Dynamic XML Sitemap Generator untuk SMKS Prasetya Makassar
 * Menghasilkan peta situs XML standar http://www.sitemaps.org/schemas/sitemap/0.9
 */

header('Content-Type: application/xml; charset=utf-8');

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/seo.php';

$base_url = rtrim(get_base_url(), '/');
$today = date('Y-m-d');

// Daftar halaman statis utama
$static_pages = [
    [
        'loc'        => $base_url . '/index.php',
        'lastmod'    => $today,
        'changefreq' => 'weekly',
        'priority'   => '1.0'
    ],
    [
        'loc'        => $base_url . '/guru.php',
        'lastmod'    => $today,
        'changefreq' => 'monthly',
        'priority'   => '0.8'
    ],
    [
        'loc'        => $base_url . '/fasilitas.php',
        'lastmod'    => $today,
        'changefreq' => 'monthly',
        'priority'   => '0.8'
    ],
    [
        'loc'        => $base_url . '/alumni.php',
        'lastmod'    => $today,
        'changefreq' => 'monthly',
        'priority'   => '0.8'
    ],
];

// Ambil artikel berita publik dari database
$news_pages = [];
$q_news = mysqli_query($conn, "SELECT id, judul, tanggal FROM berita ORDER BY tanggal DESC");
if ($q_news) {
    while ($row = mysqli_fetch_assoc($q_news)) {
        $news_id = intval($row['id']);
        $lastmod = !empty($row['tanggal']) ? date('Y-m-d', strtotime($row['tanggal'])) : $today;
        
        $news_pages[] = [
            'loc'        => $base_url . '/detail-berita.php?id=' . $news_id,
            'lastmod'    => $lastmod,
            'changefreq' => 'monthly',
            'priority'   => '0.7'
        ];
    }
}

if (ob_get_length()) {
    ob_clean();
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($static_pages as $page) : ?>
    <url>
        <loc><?= htmlspecialchars($page['loc']); ?></loc>
        <lastmod><?= htmlspecialchars($page['lastmod']); ?></lastmod>
        <changefreq><?= htmlspecialchars($page['changefreq']); ?></changefreq>
        <priority><?= htmlspecialchars($page['priority']); ?></priority>
    </url>
<?php endforeach; ?>
<?php foreach ($news_pages as $page) : ?>
    <url>
        <loc><?= htmlspecialchars($page['loc']); ?></loc>
        <lastmod><?= htmlspecialchars($page['lastmod']); ?></lastmod>
        <changefreq><?= htmlspecialchars($page['changefreq']); ?></changefreq>
        <priority><?= htmlspecialchars($page['priority']); ?></priority>
    </url>
<?php endforeach; ?>
</urlset>

