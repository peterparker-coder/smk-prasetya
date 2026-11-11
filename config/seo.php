<?php
/**
 * config/seo.php
 * Sentral Konfigurasi & Helper Optimasi SEO SMKS Prasetya Makassar
 * 
 * Mendukung:
 * - Dynamic Base URL & Canonical URL
 * - Meta Title, Meta Description, & Keywords per halaman
 * - Open Graph Tags (Facebook, WhatsApp, Telegram, LinkedIn)
 * - Twitter Card Tags
 * - JSON-LD Schema.org Structured Data (School, Breadcrumbs, NewsArticle)
 * - Resource Hints (preconnect, dns-prefetch)
 */

if (!function_exists('get_base_url')) {
    function get_base_url() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Cek jika berjalan via CLI / shell
        if (php_sapi_name() === 'cli' || empty($_SERVER['REQUEST_URI'])) {
            $doc_root = 'D:/laragon/www';
            $dir = str_replace('\\', '/', __DIR__ . '/..');
            $dir = realpath($dir);
            $dir = str_replace('\\', '/', $dir);
            $rel = str_ireplace($doc_root, '', $dir);
            return $protocol . $host . rtrim($rel, '/');
        }

        $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        // Hilangkan subdirektori admin jika dipanggil dari folder admin
        $project_dir = preg_replace('#/admin(/.*)?$#', '', $script_dir);
        $project_dir = rtrim($project_dir, '/');
        
        return $protocol . $host . $project_dir;
    }
}

if (!function_exists('get_canonical_url')) {
    function get_canonical_url($custom_path = null) {
        $base_url = get_base_url();
        if ($custom_path !== null) {
            return rtrim($base_url, '/') . '/' . ltrim($custom_path, '/');
        }
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = explode('?', $_SERVER['REQUEST_URI'] ?? '')[0];
        
        return $protocol . $host . $uri;
    }
}

if (!function_exists('render_seo_tags')) {
    function render_seo_tags(array $params = []) {
        $base_url = get_base_url();

        $title       = $params['title'] ?? 'SMKS Prasetya Makassar - Sekolah Menengah Kejuruan Unggul di Makassar';
        $description = $params['description'] ?? 'Website Resmi SMKS Prasetya Makassar. Sekolah Menengah Kejuruan di Tamalanrea, Makassar dengan program keahlian TKJ, RPL, TBSM, OTKP, dan AKL siap kerja.';
        $keywords    = $params['keywords'] ?? 'SMKS Prasetya Makassar, SMK Prasetya, SMK Makassar, SMK Tamalanrea, PPDB SMK Makassar, Sekolah Menengah Kejuruan Makassar, RPL, TKJ, TBSM, OTKP, AKL';
        $author      = $params['author'] ?? 'SMKS Prasetya Makassar';
        $type        = $params['type'] ?? 'website';
        $canonical   = $params['canonical'] ?? get_canonical_url();
        $image       = $params['image'] ?? ($base_url . '/assets/uploads/logo-smks-prasetya.png');
        $robots      = $params['robots'] ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        // Pastikan URL gambar absolut
        if (!preg_match('#^https?://#i', $image)) {
            $image = rtrim($base_url, '/') . '/' . ltrim($image, '/');
        }
        ?>
    <!-- 1. Primary Meta Tags (SEO On-Page) -->
    <title><?= htmlspecialchars($title); ?></title>
    <meta name="title" content="<?= htmlspecialchars($title); ?>">
    <meta name="description" content="<?= htmlspecialchars($description); ?>">
    <meta name="keywords" content="<?= htmlspecialchars($keywords); ?>">
    <meta name="author" content="<?= htmlspecialchars($author); ?>">
    <meta name="robots" content="<?= htmlspecialchars($robots); ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical); ?>">

    <!-- 2. Open Graph / Facebook / WhatsApp (Social Media Preview) -->
    <meta property="og:type" content="<?= htmlspecialchars($type); ?>">
    <meta property="og:site_name" content="SMKS Prasetya Makassar">
    <meta property="og:url" content="<?= htmlspecialchars($canonical); ?>">
    <meta property="og:title" content="<?= htmlspecialchars($title); ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description); ?>">
    <meta property="og:image" content="<?= htmlspecialchars($image); ?>">
    <meta property="og:image:secure_url" content="<?= htmlspecialchars($image); ?>">
    <meta property="og:image:alt" content="<?= htmlspecialchars($title); ?>">
    <meta property="og:locale" content="id_ID">

    <!-- 3. Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= htmlspecialchars($canonical); ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($title); ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($description); ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($image); ?>">
    <meta name="twitter:image:alt" content="<?= htmlspecialchars($title); ?>">

    <!-- 4. Performance & Resource Hints -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

    <!-- 5. Favicon & Web Application Icon -->
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($base_url); ?>/assets/uploads/logo-smks-prasetya.png">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($base_url); ?>/assets/uploads/logo-smks-prasetya.png">
        <?php
    }
}

if (!function_exists('render_school_schema')) {
    function render_school_schema() {
        $base_url = get_base_url();
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'EducationalOrganization',
            '@id' => $base_url . '/#school',
            'name' => 'SMKS Prasetya Makassar',
            'alternateName' => ['SMK Prasetya Makassar', 'Prasetya Vocational High School'],
            'url' => $base_url . '/',
            'logo' => $base_url . '/assets/uploads/logo-smks-prasetya.png',
            'image' => $base_url . '/assets/uploads/logo-smks-prasetya.png',
            'description' => 'SMKS Prasetya Makassar adalah Sekolah Menengah Kejuruan di Tamalanrea Kota Makassar yang berdedikasi mencetak lulusan berakhlak mulia dan berdaya saing global.',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Jl. Buntusu NHP / BTP No. 17',
                'addressLocality' => 'Makassar',
                'addressRegion' => 'Sulawesi Selatan',
                'postalCode' => '90245',
                'addressCountry' => 'ID'
            ],
            'telephone' => '0411 4770832',
            'email' => 'info@smksprasetyamakassar.sch.id',
            'openingHours' => 'Mo-Fr 07:00-16:00'
        ];

        echo "\n    <!-- JSON-LD Structured Data: School / Educational Organization -->\n";
        echo "    <script type=\"application/ld+json\">\n";
        echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n    </script>\n";
    }
}

if (!function_exists('render_breadcrumb_schema')) {
    function render_breadcrumb_schema(array $items) {
        $base_url = get_base_url();
        $itemList = [];
        $pos = 1;

        foreach ($items as $name => $path) {
            $url = preg_match('#^https?://#i', $path) ? $path : rtrim($base_url, '/') . '/' . ltrim($path, '/');
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'name' => $name,
                'item' => $url
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemList
        ];

        echo "\n    <!-- JSON-LD Structured Data: BreadcrumbList -->\n";
        echo "    <script type=\"application/ld+json\">\n";
        echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n    </script>\n";
    }
}

if (!function_exists('render_article_schema')) {
    function render_article_schema(array $berita) {
        $base_url = get_base_url();
        $article_url = $base_url . '/detail-berita.php?id=' . intval($berita['id'] ?? 0);
        $image_name = $berita['gambar'] ?? '';
        $image_url = (!empty($image_name) && file_exists(__DIR__ . '/../assets/uploads/' . $image_name))
            ? $base_url . '/assets/uploads/' . $image_name
            : $base_url . '/assets/uploads/logo-smks-prasetya.png';

        $raw_isi = strip_tags($berita['isi'] ?? '');
        $description = mb_substr($raw_isi, 0, 160);
        $date_pub = !empty($berita['tanggal']) ? date('c', strtotime($berita['tanggal'])) : date('c');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $article_url
            ],
            'headline' => $berita['judul'] ?? 'Berita SMKS Prasetya Makassar',
            'image' => [ $image_url ],
            'datePublished' => $date_pub,
            'dateModified' => $date_pub,
            'author' => [
                '@type' => 'Person',
                'name' => !empty($berita['penulis']) ? $berita['penulis'] : 'Humas SMKS Prasetya Makassar'
            ],
            'publisher' => [
                '@type' => 'EducationalOrganization',
                'name' => 'SMKS Prasetya Makassar',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $base_url . '/assets/uploads/logo-smks-prasetya.png'
                ]
            ],
            'description' => $description
        ];

        echo "\n    <!-- JSON-LD Structured Data: NewsArticle -->\n";
        echo "    <script type=\"application/ld+json\">\n";
        echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n    </script>\n";
    }
}
