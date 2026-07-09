<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/_lib/content.php';
require_once __DIR__ . '/_inc/layout.php';

$slug = isset($_GET['slug']) ? content_sanitize_slug((string) $_GET['slug']) : '';
$product = $slug !== '' ? content_load('products', $slug) : null;

if ($product === null || !empty($product['data']['draft'])) {
    http_response_code(404);
    ?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php render_head('Pagina nao encontrada', 'O conteudo solicitado nao foi encontrado.', '/404/'); ?>
</head>
<body>
<?php render_header(); ?>
<main id="conteudo">
  <header class="page-header">
    <div class="container page-header__inner">
      <h1>Pagina nao encontrada</h1>
      <p>O produto que voce procura nao existe ou foi removido.</p>
    </div>
  </header>
</main>
<?php render_footer(); ?>
</body>
</html>
    <?php
    exit;
}

$data = $product['data'];
$canonicalPath = '/produtos-que-amamos/' . $slug . '/';
?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php render_head((string) ($data['title'] ?? ''), (string) ($data['description'] ?? ''), $canonicalPath, $data['image'] ?? null, content_seo_meta($data)); ?>
</head>
<body>
<?php render_header(); ?>
<main id="conteudo">
  <header class="page-header">
    <div class="container page-header__inner">
      <p class="eyebrow"><?= site_e((string) ($data['platform'] ?? '')) ?></p>
      <h1><?= site_e((string) ($data['title'] ?? '')) ?></h1>
      <p><?= site_e((string) ($data['description'] ?? '')) ?></p>
    </div>
  </header>

  <section class="band">
    <article class="container product-detail">
      <div>
        <?php if (!empty($data['image'])): ?>
        <img src="<?= site_e((string) $data['image']) ?>" alt="">
        <?php else: ?>
        <div class="pet-panel pet-panel--light">
          <strong><?= site_e((string) ($data['category'] ?? '')) ?></strong>
          <p><?= site_e(site_setting('product_detail_no_image_text')) ?></p>
        </div>
        <?php endif; ?>
      </div>

      <aside class="product-detail__panel">
        <?php if (!empty($data['badge'])): ?>
        <span class="badge"><?= site_e((string) $data['badge']) ?></span>
        <?php endif; ?>
        <p class="platform"><?= site_e((string) ($data['category'] ?? '')) ?></p>
        <div class="prose">
          <?= render_markdown_basic($product['body']) ?>
        </div>
        <a class="button button--primary" href="<?= site_e((string) ($data['affiliateUrl'] ?? '#')) ?>" target="_blank" rel="nofollow sponsored noopener"><?= site_e(site_setting('product_detail_button_label')) ?></a>
        <p class="affiliate-note"><?= site_e(site_setting('product_detail_affiliate_note')) ?></p>
      </aside>
    </article>
  </section>
</main>
<?php render_footer(); ?>
</body>
</html>
