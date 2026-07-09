<?php
declare(strict_types=1);

require_once __DIR__ . '/../admin/_lib/content.php';
require_once __DIR__ . '/../_inc/layout.php';

$items = content_list('products');
$items = array_filter($items, function ($item) {
    return empty($item['data']['draft']);
});

// Ordenacao igual a getPublishedProducts(): featured desc.
usort($items, function ($a, $b) {
    $af = !empty($a['data']['featured']) ? 1 : 0;
    $bf = !empty($b['data']['featured']) ? 1 : 0;
    return $bf - $af;
});

$title = site_setting('products_page_title');
$description = site_setting('products_page_meta_description');
$schemas = [
    site_breadcrumb_schema([
        ['name' => 'Inicio', 'item' => '/'],
        ['name' => $title],
    ]),
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php render_head($title, $description, '/produtos-que-amamos/', null, [], $schemas); ?>
</head>
<body>
<?php render_header(); ?>
<main id="conteudo">
  <header class="page-header">
    <div class="container page-header__inner">
      <p class="eyebrow"><?= site_e(site_setting('products_page_eyebrow')) ?></p>
      <h1><?= site_e(site_setting('products_page_title')) ?></h1>
      <p><?= site_e(site_setting('products_page_description')) ?></p>
    </div>
  </header>

  <section class="band">
    <div class="container product-grid">
      <?php if (!empty($items)): ?>
      <?php foreach ($items as $item): ?>
        <?php
        $data = $item['data'];
        $slug = $item['slug'];
        $href = '/produtos-que-amamos/' . $slug . '/';
        ?>
      <article class="product-card">
        <a class="product-card__media" href="<?= site_e($href) ?>" aria-label="<?= site_e((string) ($data['title'] ?? '')) ?>">
          <?php if (!empty($data['image'])): ?>
          <img src="<?= site_e((string) $data['image']) ?>" alt="<?= site_e((string) ($data['title'] ?? 'Imagem do produto')) ?>" loading="lazy">
          <?php else: ?>
          <span><?= site_e((string) ($data['category'] ?? '')) ?></span>
          <?php endif; ?>
        </a>
        <div class="product-card__body">
          <?php if (!empty($data['badge'])): ?>
          <span class="badge"><?= site_e((string) $data['badge']) ?></span>
          <?php endif; ?>
          <p class="platform"><?= site_e((string) ($data['platform'] ?? '')) ?></p>
          <h3><a href="<?= site_e($href) ?>"><?= site_e((string) ($data['title'] ?? '')) ?></a></h3>
          <p><?= site_e((string) ($data['description'] ?? '')) ?></p>
          <div class="product-card__actions">
            <a class="button button--ghost" href="<?= site_e($href) ?>"><?= site_e(site_setting('product_card_details_label')) ?></a>
            <a class="button button--primary" href="<?= site_e((string) ($data['affiliateUrl'] ?? '#')) ?>" target="_blank" rel="nofollow sponsored noopener"><?= site_e(site_setting('product_card_offer_label')) ?></a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="notice-card">
        <h3><?= site_e(site_setting('products_page_empty_title')) ?></h3>
        <p><?= site_e(site_setting('products_page_empty_text')) ?></p>
      </div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php render_footer(); ?>
</body>
</html>
