<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/_lib/content.php';
require_once __DIR__ . '/_inc/layout.php';

$slug = isset($_GET['slug']) ? content_sanitize_slug((string) $_GET['slug']) : '';
$article = $slug !== '' ? content_load('articles', $slug) : null;

if ($article === null || !empty($article['data']['draft'])) {
    http_response_code(404);
    $title = 'Pagina nao encontrada';
    $description = 'O conteudo solicitado nao foi encontrado.';
    ?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php render_head($title, $description, '/404/'); ?>
</head>
<body>
<?php render_header(); ?>
<main id="conteudo">
  <header class="page-header">
    <div class="container page-header__inner">
      <h1>Pagina nao encontrada</h1>
      <p>O artigo que voce procura nao existe ou foi removido.</p>
    </div>
  </header>
</main>
<?php render_footer(); ?>
</body>
</html>
    <?php
    exit;
}

$data = $article['data'];
$categories = article_categories();
$categoryKey = $data['category'] ?? '';
$category = $categories[$categoryKey] ?? ['title' => '', 'path' => '#'];
$canonicalPath = '/artigos/' . $slug . '/';
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
      <p class="eyebrow"><?= site_e($category['title']) ?></p>
      <h1><?= site_e((string) ($data['title'] ?? '')) ?></h1>
      <p><?= site_e((string) ($data['description'] ?? '')) ?></p>
      <div class="meta-row">
        <span><?= site_e(format_date_ptbr((string) ($data['publishDate'] ?? ''))) ?></span>
        <a href="<?= site_e($category['path']) ?>"><?= site_e($category['title']) ?></a>
      </div>
    </div>
  </header>

  <section class="band">
    <article class="container narrow article-content">
      <?php if (!empty($data['image'])): ?>
      <img src="<?= site_e((string) $data['image']) ?>" alt="">
      <?php endif; ?>
      <div class="prose">
        <?= render_markdown_basic($article['body']) ?>
      </div>
    </article>
  </section>
</main>
<?php render_footer(); ?>
</body>
</html>
