<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/_lib/content.php';
require_once __DIR__ . '/_inc/layout.php';

$slug = isset($_GET['slug']) ? content_sanitize_slug((string) $_GET['slug']) : '';

$page = $slug !== '' ? content_load('pages', $slug) : null;

if ($page === null || !empty($page['data']['draft'])) {
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
      <p>A pagina que voce procura nao existe ou foi removida.</p>
    </div>
  </header>
</main>
<?php render_footer(); ?>
</body>
</html>
    <?php
    exit;
}

$data = $page['data'];
$canonicalPath = '/' . $slug . '/';
$pageSchemas = [
    site_breadcrumb_schema([
        ['name' => 'Inicio', 'item' => '/'],
        ['name' => (string) ($data['title'] ?? '')],
    ]),
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php render_head((string) ($data['title'] ?? ''), (string) ($data['description'] ?? ''), $canonicalPath, $data['image'] ?? null, content_seo_meta($data), $pageSchemas); ?>
</head>
<body>
<?php render_header(); ?>
<main id="conteudo">
  <header class="page-header">
    <div class="container page-header__inner">
      <h1><?= site_e((string) ($data['title'] ?? '')) ?></h1>
      <p><?= site_e((string) ($data['description'] ?? '')) ?></p>
    </div>
  </header>

  <section class="band">
    <article class="container narrow page-content">
      <?php if (!empty($data['image'])): ?>
      <img src="<?= site_e((string) $data['image']) ?>" alt="<?= site_e((string) ($data['title'] ?? 'Imagem da pagina')) ?>">
      <?php endif; ?>
      <div class="prose">
        <?= render_markdown_basic($page['body']) ?>
      </div>
    </article>
  </section>
</main>
<?php render_footer(); ?>
</body>
</html>
