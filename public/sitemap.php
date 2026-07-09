<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/_lib/content.php';
require_once __DIR__ . '/_inc/layout.php';

function sitemap_e(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function sitemap_lastmod(?string $path = null): string
{
    if ($path !== null && is_file($path)) {
        return date('Y-m-d', filemtime($path));
    }

    return date('Y-m-d');
}

function sitemap_add_url(array &$urls, string $path, string $priority = '0.7', string $changefreq = 'weekly', ?string $lastmodPath = null): void
{
    $urls[] = [
        'loc' => site_url($path),
        'lastmod' => sitemap_lastmod($lastmodPath),
        'changefreq' => $changefreq,
        'priority' => $priority,
    ];
}

$urls = [];

sitemap_add_url($urls, '/', '1.0', 'daily', __DIR__ . '/index.php');
sitemap_add_url($urls, '/produtos-que-amamos/', '0.8', 'weekly', __DIR__ . '/produtos-que-amamos/index.php');
sitemap_add_url($urls, '/cuidados-com-seu-animal/', '0.8', 'weekly', __DIR__ . '/cuidados-com-seu-animal.php');
sitemap_add_url($urls, '/curiosidades/', '0.8', 'weekly', __DIR__ . '/curiosidades.php');
sitemap_add_url($urls, '/noticias/', '0.8', 'weekly', __DIR__ . '/noticias.php');

foreach (content_list('pages') as $item) {
    if (!empty($item['data']['draft'])) {
        continue;
    }
    $slug = (string) ($item['slug'] ?? '');
    if ($slug === '') {
        continue;
    }
    sitemap_add_url($urls, '/' . $slug . '/', '0.7', 'monthly', content_dir('pages') . '/' . $slug . '.md');
}

foreach (content_list('articles') as $item) {
    if (!empty($item['data']['draft'])) {
        continue;
    }
    $slug = (string) ($item['slug'] ?? '');
    if ($slug === '') {
        continue;
    }
    sitemap_add_url($urls, '/artigos/' . $slug . '/', '0.75', 'weekly', content_dir('articles') . '/' . $slug . '.md');
}

foreach (content_list('products') as $item) {
    if (!empty($item['data']['draft'])) {
        continue;
    }
    $slug = (string) ($item['slug'] ?? '');
    if ($slug === '') {
        continue;
    }
    sitemap_add_url($urls, '/produtos-que-amamos/' . $slug . '/', '0.7', 'weekly', content_dir('products') . '/' . $slug . '.md');
}

header('Content-Type: application/xml; charset=UTF-8');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
  <url>
    <loc><?= sitemap_e($url['loc']) ?></loc>
    <lastmod><?= sitemap_e($url['lastmod']) ?></lastmod>
    <changefreq><?= sitemap_e($url['changefreq']) ?></changefreq>
    <priority><?= sitemap_e($url['priority']) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
