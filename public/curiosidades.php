<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/_lib/content.php';
require_once __DIR__ . '/_inc/layout.php';

$categoryKey = 'curiosidades';
$categories = article_categories();
$category = $categories[$categoryKey];

$items = content_list('articles');
$items = array_filter($items, function ($item) use ($categoryKey) {
    return empty($item['data']['draft']) && ($item['data']['category'] ?? '') === $categoryKey;
});

usort($items, function ($a, $b) {
    $ad = strtotime((string) ($a['data']['publishDate'] ?? '')) ?: 0;
    $bd = strtotime((string) ($b['data']['publishDate'] ?? '')) ?: 0;
    return $bd - $ad;
});
$schemas = [
    site_breadcrumb_schema([
        ['name' => 'Inicio', 'item' => '/'],
        ['name' => $category['title']],
    ]),
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php render_head($category['title'], $category['description'], $category['path'], null, [], $schemas); ?>
</head>
<body>
<?php render_header(); ?>
<main id="conteudo">
  <header class="page-header">
    <div class="container page-header__inner">
      <p class="eyebrow"><?= site_e(site_setting('category_curiosidades_eyebrow')) ?></p>
      <h1><?= site_e($category['title']) ?></h1>
      <p><?= site_e($category['description']) ?></p>
    </div>
  </header>

  <section class="band">
    <div class="container post-grid">
      <?php if (!empty($items)): ?>
      <?php foreach ($items as $item): ?>
        <?php
        $data = $item['data'];
        $slug = $item['slug'];
        $href = '/artigos/' . $slug . '/';
        $cat = $categories[$data['category'] ?? ''] ?? ['title' => '', 'path' => '#'];
        ?>
      <article class="post-card">
        <a class="post-card__media" href="<?= site_e($href) ?>" aria-label="<?= site_e((string) ($data['title'] ?? '')) ?>">
          <?php if (!empty($data['image'])): ?>
          <img src="<?= site_e((string) $data['image']) ?>" alt="<?= site_e((string) ($data['title'] ?? 'Imagem do artigo')) ?>" loading="lazy">
          <?php else: ?>
          <span><?= site_e($cat['title']) ?></span>
          <?php endif; ?>
        </a>
        <div class="post-card__body">
          <div class="meta-row">
            <a href="<?= site_e($cat['path']) ?>"><?= site_e($cat['title']) ?></a>
            <span><?= site_e(format_date_ptbr((string) ($data['publishDate'] ?? ''))) ?></span>
          </div>
          <h3><a href="<?= site_e($href) ?>"><?= site_e((string) ($data['title'] ?? '')) ?></a></h3>
          <p><?= site_e((string) ($data['description'] ?? '')) ?></p>
        </div>
      </article>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="notice-card">
        <h3><?= site_e(site_setting('category_empty_title')) ?></h3>
        <p><?= site_e(site_setting('category_empty_text')) ?></p>
      </div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php render_footer(); ?>
</body>
</html>
