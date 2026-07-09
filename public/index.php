<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/_lib/content.php';
require_once __DIR__ . '/_inc/layout.php';

$products = content_list('products');
$products = array_filter($products, function ($item) {
    return empty($item['data']['draft']) && !empty($item['data']['featured']);
});

usort($products, function ($a, $b) {
    $af = !empty($a['data']['featured']) ? 1 : 0;
    $bf = !empty($b['data']['featured']) ? 1 : 0;
    return $bf - $af;
});

$products = array_slice($products, 0, 4);

$articles = content_list('articles');
$articles = array_filter($articles, function ($item) {
    return empty($item['data']['draft']);
});

usort($articles, function ($a, $b) {
    $ad = strtotime((string) ($a['data']['publishDate'] ?? '')) ?: 0;
    $bd = strtotime((string) ($b['data']['publishDate'] ?? '')) ?: 0;
    return $bd - $ad;
});

$articles = array_slice($articles, 0, 3);
$categories = article_categories();
?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php render_head(SITE_NAME, SITE_DESCRIPTION, '/'); ?>
</head>
<body>
<?php render_header(); ?>
<main id="conteudo">
  <section class="hero">
    <div class="container hero__grid">
      <div class="hero__copy">
        <p class="eyebrow"><?= site_e(site_setting('home_hero_eyebrow')) ?></p>
        <h1><?= site_e(site_setting('home_hero_title')) ?></h1>
        <p class="hero__text">
          <?= site_e(site_setting('home_hero_text')) ?>
        </p>
        <div class="button-row">
          <a class="button button--primary" href="<?= site_e(site_setting('home_hero_primary_url')) ?>"><?= site_e(site_setting('home_hero_primary_label')) ?></a>
          <a class="button button--ghost" href="<?= site_e(site_setting('home_hero_secondary_url')) ?>"><?= site_e(site_setting('home_hero_secondary_label')) ?></a>
        </div>
      </div>

      <div class="hero__visual">
        <img
          class="hero__image"
          src="<?= site_e(site_setting('home_hero_image')) ?>"
          alt="<?= site_e(site_setting('home_hero_image_alt')) ?>"
          width="1536"
          height="1152"
        >
      </div>
    </div>
  </section>

  <section class="band">
    <div class="section-heading">
      <div>
        <p class="eyebrow"><?= site_e(site_setting('home_products_eyebrow')) ?></p>
        <h2><?= site_e(site_setting('home_products_title')) ?></h2>
        <p><?= site_e(site_setting('home_products_text')) ?></p>
      </div>
      <a href="/produtos-que-amamos/"><?= site_e(site_setting('home_products_all_label')) ?></a>
    </div>
    <div class="container product-grid">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $item): ?>
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
            <span><?= site_e((string) ($data['category'] ?? 'Produto')) ?></span>
            <?php endif; ?>
          </a>
          <div class="product-card__body">
            <?php if (!empty($data['badge'])): ?>
            <span class="badge"><?= site_e((string) $data['badge']) ?></span>
            <?php endif; ?>
            <p class="platform"><?= site_e((string) ($data['platform'] ?? '')) ?></p>
            <h3><a href="<?= site_e($href) ?>"><?= site_e((string) ($data['title'] ?? $slug)) ?></a></h3>
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
          <h3><?= site_e(site_setting('home_products_empty_title')) ?></h3>
          <p><?= site_e(site_setting('home_products_empty_text')) ?></p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="band band--soft">
    <div class="section-heading">
      <div>
        <p class="eyebrow"><?= site_e(site_setting('home_articles_eyebrow')) ?></p>
        <h2><?= site_e(site_setting('home_articles_title')) ?></h2>
        <p><?= site_e(site_setting('home_articles_text')) ?></p>
      </div>
    </div>
    <div class="container post-grid">
      <?php if (!empty($articles)): ?>
      <?php foreach ($articles as $item): ?>
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
          <h3><a href="<?= site_e($href) ?>"><?= site_e((string) ($data['title'] ?? $slug)) ?></a></h3>
          <p><?= site_e((string) ($data['description'] ?? '')) ?></p>
        </div>
      </article>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="notice-card">
        <h3><?= site_e(site_setting('home_articles_empty_title')) ?></h3>
        <p><?= site_e(site_setting('home_articles_empty_text')) ?></p>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="social-cta">
    <div class="container social-cta__inner">
      <div>
        <p class="eyebrow"><?= site_e(site_setting('home_social_eyebrow')) ?></p>
        <h2><?= site_e(site_setting('home_social_title')) ?></h2>
        <p><?= site_e(site_setting('home_social_text')) ?></p>
      </div>
      <div class="button-row">
        <a class="button button--primary" href="<?= site_e(SITE_INSTAGRAM_URL) ?>" target="_blank" rel="nofollow noopener">Instagram</a>
        <a class="button button--ghost" href="<?= site_e(SITE_TIKTOK_URL) ?>" target="_blank" rel="nofollow noopener">TikTok</a>
        <a class="button button--ghost" href="<?= site_e(SITE_YOUTUBE_URL) ?>" target="_blank" rel="nofollow noopener">YouTube</a>
        <a class="button button--ghost" href="<?= site_e(SITE_SHOPEE_STORE_URL) ?>" target="_blank" rel="nofollow sponsored noopener">Shopee</a>
      </div>
    </div>
  </section>
</main>
<?php render_footer(); ?>
</body>
</html>
