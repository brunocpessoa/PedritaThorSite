<?php
declare(strict_types=1);

require_once __DIR__ . '/_lib/auth.php';
require_once __DIR__ . '/_lib/content.php';

if (!admin_credentials_exist()) {
    header('Location: setup.php');
    exit;
}

admin_require_login();

$allowedTypes = ['articles', 'products', 'pages'];
$type = isset($_GET['type']) ? (string) $_GET['type'] : '';
if (!in_array($type, $allowedTypes, true)) {
    $type = 'articles';
}

$typeLabels = [
    'articles' => 'Artigos',
    'products' => 'Produtos',
    'pages' => 'Paginas',
];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!admin_csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Sessao invalida, recarregue a pagina e tente novamente.';
    } else {
        $deleteType = (string) ($_POST['type'] ?? '');
        $deleteSlug = (string) ($_POST['slug'] ?? '');
        if (in_array($deleteType, $allowedTypes, true) && $deleteSlug !== '') {
            content_delete($deleteType, $deleteSlug);
            header('Location: content.php?type=' . urlencode($deleteType) . '&deleted=1');
            exit;
        }
        $error = 'Nao foi possivel excluir o item.';
    }
}

$deleted = isset($_GET['deleted']);
$saved = isset($_GET['saved']);
$items = content_list($type);

// Ordenacao amigavel na listagem: artigos por data desc, demais por titulo.
if ($type === 'articles') {
    usort($items, function ($a, $b) {
        $ad = strtotime((string) ($a['data']['publishDate'] ?? '')) ?: 0;
        $bd = strtotime((string) ($b['data']['publishDate'] ?? '')) ?: 0;
        return $bd - $ad;
    });
} else {
    usort($items, function ($a, $b) {
        $at = (string) ($a['data']['title'] ?? '');
        $bt = (string) ($b['data']['title'] ?? '');
        return strcasecmp($at, $bt);
    });
}

$csrf = admin_csrf_token();
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex" />
  <title>Conteudo - Pedrita & Thor Admin</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #f4f4f4; margin: 0; padding: 32px; }
    .card { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.1); max-width: 920px; margin: 0 auto; }
    h1 { font-size: 18px; margin: 0 0 16px; }
    a { color: #2563eb; text-decoration: none; }
    .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px; }
    .tabs { display: flex; gap: 12px; }
    .tabs a { padding: 6px 12px; border-radius: 4px; background: #f4f4f4; }
    .tabs a.active { background: #2563eb; color: #fff; }
    .btn-new { background: #15803d; color: #fff; padding: 8px 14px; border-radius: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; font-size: 14px; }
    th { color: #555; font-weight: 600; }
    .badge { display: inline-block; font-size: 11px; padding: 2px 6px; border-radius: 4px; background: #fee2e2; color: #b91c1c; }
    .badge--ok { background: #dcfce7; color: #15803d; }
    .actions { display: flex; gap: 10px; }
    .actions form { display: inline; }
    .actions button { background: none; border: none; color: #b91c1c; text-decoration: underline; cursor: pointer; font-size: 14px; padding: 0; font-family: inherit; }
    .ok { color: #15803d; font-size: 13px; margin-bottom: 12px; }
    .error { color: #b91c1c; font-size: 13px; margin-bottom: 12px; }
    .empty { color: #777; font-size: 14px; margin-top: 16px; }
    .back { display: block; margin-top: 24px; }
  </style>
  <link rel="stylesheet" href="admin.css" />
</head>
<body>
  <div class="card">
    <div class="top-bar">
      <h1>Conteudo</h1>
      <a class="btn-new" href="content-edit.php?type=<?= admin_e($type) ?>">+ Novo</a>
    </div>

    <div class="tabs">
      <?php foreach ($typeLabels as $key => $label): ?>
      <a class="<?= $key === $type ? 'active' : '' ?>" href="content.php?type=<?= admin_e($key) ?>"><?= admin_e($label) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if ($deleted): ?><p class="ok">Item excluido.</p><?php endif; ?>
    <?php if ($saved): ?><p class="ok">Item salvo.</p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= admin_e($error) ?></p><?php endif; ?>

    <?php if (empty($items)): ?>
      <p class="empty">Nenhum item cadastrado ainda.</p>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Titulo</th>
          <?php if ($type === 'articles'): ?><th>Data</th><?php endif; ?>
          <th>Status</th>
          <th>Acoes</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <?php
          $slug = $item['slug'];
          $data = $item['data'];
          $isDraft = !empty($data['draft']);
          ?>
        <tr>
          <td><?= admin_e((string) ($data['title'] ?? $slug)) ?></td>
          <?php if ($type === 'articles'): ?>
          <td><?= admin_e((string) ($data['publishDate'] ?? '')) ?></td>
          <?php endif; ?>
          <td>
            <?php if ($isDraft): ?>
            <span class="badge">Rascunho</span>
            <?php else: ?>
            <span class="badge badge--ok">Publicado</span>
            <?php endif; ?>
          </td>
          <td class="actions">
            <a href="content-edit.php?type=<?= admin_e($type) ?>&amp;slug=<?= admin_e($slug) ?>">Editar</a>
            <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir este item?');">
              <input type="hidden" name="action" value="delete" />
              <input type="hidden" name="type" value="<?= admin_e($type) ?>" />
              <input type="hidden" name="slug" value="<?= admin_e($slug) ?>" />
              <input type="hidden" name="csrf_token" value="<?= admin_e($csrf) ?>" />
              <button type="submit">Excluir</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <a class="back" href="index.php">&larr; Voltar ao painel</a>
  </div>
</body>
</html>
