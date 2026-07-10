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

$slug = isset($_GET['slug']) ? content_sanitize_slug((string) $_GET['slug']) : '';
$isNew = ($slug === '');

$existing = null;
if (!$isNew) {
    $existing = content_load($type, $slug);
    if ($existing === null) {
        http_response_code(404);
        echo 'Item nao encontrado.';
        exit;
    }
}

$errors = [];

// Valores padrao do formulario: do POST (em caso de erro de validacao),
// depois do item existente, depois vazio.
function field_value(string $key, array $postData, ?array $existing, $default = '')
{
    if (!empty($postData)) {
        return $postData[$key] ?? $default;
    }
    if ($existing !== null) {
        return $existing['data'][$key] ?? $default;
    }
    return $default;
}

function admin_parse_csv_list(string $value): array
{
    if (trim($value) === '') {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode(',', $value)), function ($item) {
        return $item !== '';
    }));
}

function admin_body_to_editor_html(string $body): string
{
    $body = trim($body);
    if ($body === '') {
        return '';
    }

    if (preg_match('/<([a-z][a-z0-9]*)\b/i', $body)) {
        $body = preg_replace('/<\s*(script|style|iframe|object|embed|form|input|button|textarea|select)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $body) ?? '';
        $body = preg_replace('/<\s*(script|style|iframe|object|embed|form|input|button|textarea|select)[^>]*\/?\s*>/is', '', $body) ?? '';
        $body = strip_tags($body, '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><blockquote><hr>');
        $body = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $body) ?? '';
        $body = preg_replace('/\s+(style|class|id)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $body) ?? '';
        $body = preg_replace('/href\s*=\s*([\'"])\s*javascript:[^\'"]*\1/i', 'href="#"', $body) ?? '';
        return $body;
    }

    $blocks = preg_split('/\n\s*\n/', str_replace("\r\n", "\n", $body));
    $html = '';
    foreach ($blocks as $block) {
        $block = trim((string) $block);
        if ($block === '') {
            continue;
        }
        $html .= '<p>' . nl2br(admin_e($block)) . '</p>';
    }

    return $html;
}

function admin_handle_image_upload(array &$errors): ?string
{
    if (empty($_FILES['image_upload']) || !is_array($_FILES['image_upload'])) {
        return null;
    }

    $file = $_FILES['image_upload'];
    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($errorCode === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($errorCode !== UPLOAD_ERR_OK) {
        $errors[] = 'Nao foi possivel enviar a imagem. Tente novamente.';
        return null;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        $errors[] = 'Upload de imagem invalido.';
        return null;
    }

    if ($size <= 0 || $size > 5 * 1024 * 1024) {
        $errors[] = 'A imagem deve ter no maximo 5 MB.';
        return null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $tmpName);
            finfo_close($finfo);
            $mime = is_string($detected) ? $detected : '';
        }
    }

    if ($mime === '' && function_exists('getimagesize')) {
        $info = getimagesize($tmpName);
        $mime = is_array($info) && isset($info['mime']) ? (string) $info['mime'] : '';
    }

    if (!isset($allowed[$mime])) {
        $errors[] = 'Use uma imagem JPG, PNG, WebP ou GIF.';
        return null;
    }

    $uploadsDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    $baseName = pathinfo((string) ($file['name'] ?? 'imagem'), PATHINFO_FILENAME);
    $baseSlug = slugify($baseName);
    $fileName = $baseSlug . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = $uploadsDir . '/' . $fileName;

    if (!move_uploaded_file($tmpName, $target)) {
        $errors[] = 'Nao foi possivel salvar a imagem enviada.';
        return null;
    }

    chmod($target, 0644);
    return '/uploads/' . $fileName;
}

$postData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sessao invalida, recarregue a pagina e tente novamente.';
    } else {
        $uploadedImage = admin_handle_image_upload($errors);
        if ($uploadedImage !== null) {
            $_POST['image'] = $uploadedImage;
            $postData['image'] = $uploadedImage;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $seoTitle = trim((string) ($_POST['seoTitle'] ?? ''));
        $seoDescription = trim((string) ($_POST['seoDescription'] ?? ''));
        $seoKeywords = admin_parse_csv_list(trim((string) ($_POST['seoKeywords'] ?? '')));
        $tags = admin_parse_csv_list(trim((string) ($_POST['tags'] ?? '')));
        $body = (string) ($_POST['body'] ?? '');
        $draft = isset($_POST['draft']);

        if ($title === '') {
            $errors[] = 'Titulo e obrigatorio.';
        }
        if ($description === '') {
            $errors[] = 'Descricao e obrigatoria.';
        }

        $data = [
            'title' => $title,
            'description' => $description,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
            'seoKeywords' => $seoKeywords,
            'tags' => $tags,
            'draft' => $draft,
        ];

        if ($type === 'articles') {
            $publishDate = trim((string) ($_POST['publishDate'] ?? ''));
            $category = trim((string) ($_POST['category'] ?? ''));
            $image = trim((string) ($_POST['image'] ?? ''));
            $imageFit = trim((string) ($_POST['imageFit'] ?? 'cover'));
            $imagePosition = trim((string) ($_POST['imagePosition'] ?? 'center center'));

            if ($publishDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishDate)) {
                $errors[] = 'Data de publicacao invalida (use o formato AAAA-MM-DD).';
            }
            if (!in_array($category, ['cuidados', 'curiosidades', 'noticias'], true)) {
                $errors[] = 'Categoria invalida.';
            }
            if (!in_array($imageFit, ['cover', 'contain'], true)) {
                $errors[] = 'Ajuste de imagem invalido.';
            }
            if (!in_array($imagePosition, ['center center', 'center top', 'center bottom', 'left center', 'right center'], true)) {
                $errors[] = 'Posicao de imagem invalida.';
            }

            $data['publishDate'] = $publishDate;
            $data['category'] = $category;
            $data['image'] = $image;
            $data['imageFit'] = $imageFit;
            $data['imagePosition'] = $imagePosition;
        } elseif ($type === 'products') {
            $category = trim((string) ($_POST['category'] ?? ''));
            $platform = trim((string) ($_POST['platform'] ?? ''));
            $affiliateUrl = trim((string) ($_POST['affiliateUrl'] ?? ''));
            $image = trim((string) ($_POST['image'] ?? ''));
            $badge = trim((string) ($_POST['badge'] ?? ''));
            $featured = isset($_POST['featured']);

            if ($category === '') {
                $errors[] = 'Categoria e obrigatoria.';
            }
            $allowedPlatforms = ['Shopee', 'TikTok Shop', 'Mercado Livre', 'Amazon', 'Outro'];
            if (!in_array($platform, $allowedPlatforms, true)) {
                $errors[] = 'Plataforma invalida.';
            }
            if ($affiliateUrl === '' || !filter_var($affiliateUrl, FILTER_VALIDATE_URL)) {
                $errors[] = 'URL de afiliado invalida.';
            }

            $data['category'] = $category;
            $data['platform'] = $platform;
            $data['affiliateUrl'] = $affiliateUrl;
            $data['image'] = $image;
            $data['badge'] = $badge;
            $data['featured'] = $featured;
        } elseif ($type === 'pages') {
            $linkLabel = trim((string) ($_POST['linkLabel'] ?? ''));
            $image = trim((string) ($_POST['image'] ?? ''));
            $data['linkLabel'] = $linkLabel;
            $data['image'] = $image;
        }

        if (empty($errors)) {
            $saveSlug = $slug;
            if ($isNew) {
                $base = slugify($title);
                $saveSlug = content_unique_slug($type, $base);
            }

            content_save($type, $saveSlug, $data, $body);
            header('Location: content.php?type=' . urlencode($type) . '&saved=1');
            exit;
        }
    }
}

$titleVal = field_value('title', $postData, $existing);
$linkLabelVal = field_value('linkLabel', $postData, $existing);
if ($type === 'pages' && trim((string) $linkLabelVal) === '') {
    $defaultLinkLabels = [
        'sobre' => 'Sobre',
        'aviso-de-afiliados' => 'Afiliados',
        'politica-de-privacidade' => 'Privacidade',
    ];
    $linkLabelVal = $defaultLinkLabels[$slug] ?? (string) $titleVal;
}
$descriptionVal = field_value('description', $postData, $existing);
$seoTitleVal = field_value('seoTitle', $postData, $existing);
$seoDescriptionVal = field_value('seoDescription', $postData, $existing);
$seoKeywordsVal = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? (string) ($postData['seoKeywords'] ?? '')
    : implode(', ', $existing['data']['seoKeywords'] ?? []);
$bodyVal = $postData['body'] ?? ($existing['body'] ?? '');
$draftVal = $_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['draft']) : !empty($existing['data']['draft'] ?? false);

$publishDateVal = field_value('publishDate', $postData, $existing);
$categoryVal = field_value('category', $postData, $existing);
$tagsVal = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? (string) ($postData['tags'] ?? '')
    : implode(', ', $existing['data']['tags'] ?? []);
$imageVal = field_value('image', $postData, $existing);
$imageFitVal = field_value('imageFit', $postData, $existing, 'cover');
$imagePositionVal = field_value('imagePosition', $postData, $existing, 'center center');
$platformVal = field_value('platform', $postData, $existing);
$affiliateUrlVal = field_value('affiliateUrl', $postData, $existing);
$badgeVal = field_value('badge', $postData, $existing);
$featuredVal = $_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['featured']) : !empty($existing['data']['featured'] ?? false);

$csrf = admin_csrf_token();
$pageTitle = $isNew ? 'Novo item' : 'Editar item';
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex" />
  <title><?= admin_e($pageTitle) ?> - Pedrita & Thor Admin</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #f4f4f4; margin: 0; padding: 32px; }
    .card { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.1); max-width: 720px; margin: 0 auto; }
    h1 { font-size: 18px; margin: 0 0 16px; }
    a { color: #2563eb; text-decoration: none; }
    label { display: block; font-size: 13px; margin-bottom: 4px; color: #333; font-weight: 600; }
    input[type=text], input[type=date], input[type=url], textarea, select {
      width: 100%; padding: 8px; margin-bottom: 16px; box-sizing: border-box;
      border: 1px solid #ccc; border-radius: 4px; font-family: inherit; font-size: 14px;
    }
    textarea { min-height: 220px; resize: vertical; }
    .textarea-short { min-height: 70px; }
    .textarea-product { min-height: 128px; }
    .checkbox-row { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
    .checkbox-row input { width: auto; margin: 0; }
    .checkbox-row label { margin: 0; font-weight: normal; }
    button { padding: 10px 18px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
    .error { color: #b91c1c; font-size: 13px; margin-bottom: 12px; }
    .error ul { margin: 0; padding-left: 18px; }
    .back { display: block; margin-top: 16px; }
    .hint { font-size: 12px; color: #777; margin: -12px 0 16px; }
    .image-uploader { display: grid; gap: 10px; margin-bottom: 16px; }
    .image-uploader input[type=text] { margin-bottom: 0; }
    .image-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .image-options select { margin-bottom: 0; }
    .dropzone {
      display: grid; gap: 4px; place-items: center; padding: 18px; text-align: center;
      border: 1px dashed #9ca3af; border-radius: 8px; background: #f8fafc; cursor: pointer;
    }
    .dropzone.is-dragover { border-color: #2563eb; background: #eff6ff; }
    .dropzone input { position: absolute; inline-size: 1px; block-size: 1px; opacity: 0; pointer-events: none; }
    .dropzone span { font-size: 13px; font-weight: 700; color: #333; }
    .dropzone small { color: #777; }
    .image-preview { width: 100%; max-height: 240px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb; }
    .form-section { margin: 22px 0; padding: 18px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fbfdfb; }
    .form-section h2 { margin: 0 0 14px; font-size: 15px; }
    .rich-toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; }
    .rich-toolbar button {
      min-width: 36px; padding: 7px 10px; color: #15201b; background: #eef4ef;
      border: 1px solid #dce5df; box-shadow: none; font-weight: 800;
    }
    .rich-editor {
      min-height: 280px; padding: 14px; margin-bottom: 16px; border: 1px solid #ccc;
      border-radius: 8px; background: #fff; line-height: 1.65; outline: none;
    }
    .rich-editor:focus { border-color: #15803d; box-shadow: 0 0 0 3px rgba(47, 125, 89, 0.14); }
    .rich-editor h2, .rich-editor h3, .rich-editor h4 { margin: 18px 0 8px; }
    .rich-editor p { margin: 0 0 12px; color: #15201b; }
    .rich-editor ul, .rich-editor ol { margin: 0 0 12px; padding-left: 24px; }
    .rich-editor blockquote { margin: 0 0 12px; padding-left: 14px; border-left: 3px solid #2f7d59; color: #647067; }
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
    .seo-actions { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
    .seo-actions button { padding: 8px 12px; font-size: 13px; }
    .seo-status { color: #647067; font-size: 12px; }
    @media (max-width: 680px) { .image-options { grid-template-columns: 1fr; } }
  </style>
  <link rel="stylesheet" href="admin.css" />
</head>
<body>
  <div class="card">
    <h1><?= admin_e($pageTitle) ?> &mdash; <?= admin_e($typeLabels[$type]) ?></h1>

    <?php if (!empty($errors)): ?>
    <div class="error">
      <ul>
        <?php foreach ($errors as $err): ?>
        <li><?= admin_e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= admin_e($csrf) ?>" />

      <label for="title">Titulo</label>
      <input type="text" id="title" name="title" value="<?= admin_e((string) $titleVal) ?>" required />

      <?php if ($type === 'pages'): ?>
      <label for="linkLabel">Nome do link/menu</label>
      <input type="text" id="linkLabel" name="linkLabel" value="<?= admin_e((string) $linkLabelVal) ?>" placeholder="Ex.: Afiliados, Sobre, Privacidade" />
      <p class="hint">Esse texto aparece no menu do site. O titulo acima aparece dentro da pagina.</p>
      <?php endif; ?>

      <label for="description">Descricao</label>
      <textarea id="description" name="description" class="<?= $type === 'products' ? 'textarea-product' : 'textarea-short' ?>" required><?= admin_e((string) $descriptionVal) ?></textarea>
      <?php if ($type === 'products'): ?>
      <p class="hint">Use este campo para a chamada/resumo do produto. A descricao completa pode ser escrita no campo Conteudo abaixo.</p>
      <?php endif; ?>

      <?php if ($type === 'articles'): ?>
      <label for="publishDate">Data de publicacao</label>
      <input type="date" id="publishDate" name="publishDate" value="<?= admin_e((string) $publishDateVal) ?>" required />

      <label for="category">Categoria</label>
      <select id="category" name="category" required>
        <?php
        $categoryOptions = [
            'cuidados' => 'Cuidados com seu animal',
            'curiosidades' => 'Curiosidades',
            'noticias' => 'Noticias',
        ];
        foreach ($categoryOptions as $value => $label):
        ?>
        <option value="<?= admin_e($value) ?>" <?= ($categoryVal === $value) ? 'selected' : '' ?>><?= admin_e($label) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="image">Imagem</label>
      <div class="image-uploader" data-image-uploader>
        <input type="text" id="image" name="image" value="<?= admin_e((string) $imageVal) ?>" placeholder="/uploads/imagem.webp ou https://..." />
        <label class="dropzone" for="image_upload">
          <input type="file" id="image_upload" name="image_upload" accept="image/jpeg,image/png,image/webp,image/gif" />
          <span>Arraste uma imagem aqui ou clique para escolher do computador</span>
          <small>JPG, PNG, WebP ou GIF ate 5 MB</small>
        </label>
        <img class="image-preview" src="<?= admin_e((string) $imageVal) ?>" alt="" <?= $imageVal ? '' : 'hidden' ?> />
      </div>
      <div class="image-options">
        <div>
          <label for="imageFit">Ajuste da imagem no artigo</label>
          <select id="imageFit" name="imageFit">
            <option value="cover" <?= $imageFitVal === 'cover' ? 'selected' : '' ?>>Preencher espaco (pode cortar)</option>
            <option value="contain" <?= $imageFitVal === 'contain' ? 'selected' : '' ?>>Mostrar imagem inteira</option>
          </select>
        </div>
        <div>
          <label for="imagePosition">Posicao da imagem</label>
          <select id="imagePosition" name="imagePosition">
            <?php
            $imagePositionOptions = [
                'center center' => 'Centro',
                'center top' => 'Topo',
                'center bottom' => 'Baixo',
                'left center' => 'Esquerda',
                'right center' => 'Direita',
            ];
            foreach ($imagePositionOptions as $value => $label):
            ?>
            <option value="<?= admin_e($value) ?>" <?= $imagePositionVal === $value ? 'selected' : '' ?>><?= admin_e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <p class="hint">Use "Mostrar imagem inteira" quando a foto estiver cortando demais.</p>
      <?php elseif ($type === 'products'): ?>
      <label for="category">Categoria</label>
      <input type="text" id="category" name="category" value="<?= admin_e((string) $categoryVal) ?>" required />

      <label for="platform">Plataforma</label>
      <select id="platform" name="platform" required>
        <?php foreach (['Shopee', 'TikTok Shop', 'Mercado Livre', 'Amazon', 'Outro'] as $platformOption): ?>
        <option value="<?= admin_e($platformOption) ?>" <?= ($platformVal === $platformOption) ? 'selected' : '' ?>><?= admin_e($platformOption) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="affiliateUrl">URL de afiliado</label>
      <input type="url" id="affiliateUrl" name="affiliateUrl" value="<?= admin_e((string) $affiliateUrlVal) ?>" required />

      <label for="image">Imagem</label>
      <div class="image-uploader" data-image-uploader>
        <input type="text" id="image" name="image" value="<?= admin_e((string) $imageVal) ?>" placeholder="/uploads/imagem.webp ou https://..." />
        <label class="dropzone" for="image_upload">
          <input type="file" id="image_upload" name="image_upload" accept="image/jpeg,image/png,image/webp,image/gif" />
          <span>Arraste uma imagem aqui ou clique para escolher do computador</span>
          <small>JPG, PNG, WebP ou GIF ate 5 MB</small>
        </label>
        <img class="image-preview" src="<?= admin_e((string) $imageVal) ?>" alt="" <?= $imageVal ? '' : 'hidden' ?> />
      </div>

      <label for="badge">Selo / badge (opcional)</label>
      <input type="text" id="badge" name="badge" value="<?= admin_e((string) $badgeVal) ?>" />

      <div class="checkbox-row">
        <input type="checkbox" id="featured" name="featured" <?= $featuredVal ? 'checked' : '' ?> />
        <label for="featured">Produto em destaque</label>
      </div>
      <?php elseif ($type === 'pages'): ?>
      <label for="image">Imagem da pagina (opcional)</label>
      <div class="image-uploader" data-image-uploader>
        <input type="text" id="image" name="image" value="<?= admin_e((string) $imageVal) ?>" placeholder="/uploads/imagem.webp ou https://..." />
        <label class="dropzone" for="image_upload">
          <input type="file" id="image_upload" name="image_upload" accept="image/jpeg,image/png,image/webp,image/gif" />
          <span>Arraste uma imagem aqui ou clique para escolher do computador</span>
          <small>JPG, PNG, WebP ou GIF ate 5 MB</small>
        </label>
        <img class="image-preview" src="<?= admin_e((string) $imageVal) ?>" alt="" <?= $imageVal ? '' : 'hidden' ?> />
      </div>
      <?php endif; ?>

      <div class="form-section">
        <h2>Tags e SEO</h2>
        <div class="seo-actions">
          <button type="button" data-generate-seo>Gerar SEO automaticamente</button>
          <span class="seo-status" data-seo-status>Os campos vazios serao preenchidos com base no titulo, descricao e conteudo.</span>
        </div>

        <label for="tags">Tags do conteudo</label>
        <input type="text" id="tags" name="tags" value="<?= admin_e((string) $tagsVal) ?>" placeholder="Ex.: cachorro, cuidados, shopee" />
        <p class="hint">Separe por virgulas. Ajuda na organizacao e tambem pode apoiar o SEO.</p>

        <label for="seoTitle">Titulo SEO (opcional)</label>
        <input type="text" id="seoTitle" name="seoTitle" value="<?= admin_e((string) $seoTitleVal) ?>" placeholder="Se vazio, usa o Titulo" />

        <label for="seoDescription">Descricao SEO (opcional)</label>
        <textarea id="seoDescription" name="seoDescription" class="textarea-short" placeholder="Se vazio, usa a Descricao"><?= admin_e((string) $seoDescriptionVal) ?></textarea>

        <label for="seoKeywords">Palavras-chave SEO</label>
        <input type="text" id="seoKeywords" name="seoKeywords" value="<?= admin_e((string) $seoKeywordsVal) ?>" placeholder="Ex.: pet shop, cachorro, produto para cachorro" />
        <p class="hint">Separe por virgulas. Se deixar vazio, o site usa as Tags como palavras-chave.</p>
      </div>

      <label for="body-editor"><?= $type === 'products' ? 'Descricao completa do produto' : 'Conteudo' ?></label>
      <div class="rich-toolbar" aria-label="Formatacao do conteudo">
        <button type="button" data-command="bold">B</button>
        <button type="button" data-command="italic">I</button>
        <button type="button" data-command="underline">U</button>
        <button type="button" data-block="p">Normal</button>
        <button type="button" data-block="h2">Texto maior</button>
        <button type="button" data-block="h3">Subtitulo</button>
        <button type="button" data-block="blockquote">Citacao</button>
        <button type="button" data-command="insertUnorderedList">Lista</button>
        <button type="button" data-command="insertOrderedList">1. Lista</button>
        <button type="button" data-link>Link</button>
        <button type="button" data-hr>Linha</button>
        <button type="button" data-command="removeFormat">Limpar</button>
      </div>
      <div id="body-editor" class="rich-editor" contenteditable="true"><?= admin_body_to_editor_html((string) $bodyVal) ?></div>
      <textarea id="body" class="sr-only" name="body" tabindex="-1"><?= admin_e((string) $bodyVal) ?></textarea>
      <p class="hint">Pode colar texto formatado de outros locais. Antes de publicar, revise links e espacos.</p>

      <div class="checkbox-row">
        <input type="checkbox" id="draft" name="draft" <?= $draftVal ? 'checked' : '' ?> />
        <label for="draft">Rascunho (nao publicar no site)</label>
      </div>

      <?php if (!$isNew): ?>
      <p class="hint">Slug: <code><?= admin_e($slug) ?></code> (nao pode ser alterado por aqui)</p>
      <?php else: ?>
      <p class="hint">O slug sera gerado automaticamente a partir do titulo.</p>
      <?php endif; ?>

      <button type="submit">Salvar</button>
    </form>

    <a class="back" href="content.php?type=<?= admin_e($type) ?>">&larr; Voltar para a lista</a>
  </div>
  <script>
    const form = document.querySelector('form');
    const titleInput = document.querySelector('#title');
    const descriptionInput = document.querySelector('#description');
    const tagsInput = document.querySelector('#tags');
    const seoTitleInput = document.querySelector('#seoTitle');
    const seoDescriptionInput = document.querySelector('#seoDescription');
    const seoKeywordsInput = document.querySelector('#seoKeywords');
    const seoStatus = document.querySelector('[data-seo-status]');
    const bodyEditor = document.querySelector('#body-editor');
    const bodyInput = document.querySelector('#body');
    const seoFields = [tagsInput, seoTitleInput, seoDescriptionInput, seoKeywordsInput].filter(Boolean);
    const touchedSeoFields = new WeakSet();

    const syncBody = () => {
      if (!bodyEditor || !bodyInput) return;
      bodyInput.value = bodyEditor.innerHTML.trim();
    };

    const limitText = (text, maxLength) => {
      const normalized = text.replace(/\s+/g, ' ').trim();
      if (normalized.length <= maxLength) return normalized;
      const sliced = normalized.slice(0, maxLength + 1);
      const cut = sliced.lastIndexOf(' ');
      return `${sliced.slice(0, cut > 80 ? cut : maxLength).trim()}...`;
    };

    const stripAccents = (text) => text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    const extractKeywords = (text) => {
      const stopwords = new Set([
        'para', 'pela', 'pelo', 'pelos', 'pelas', 'como', 'mais', 'muito', 'essa', 'esse', 'isso', 'esta',
        'este', 'quando', 'onde', 'sobre', 'tambem', 'porque', 'entre', 'todos', 'todas', 'voce', 'seus',
        'suas', 'nosso', 'nossa', 'nossos', 'nossas', 'com', 'uma', 'uns', 'das', 'dos', 'que', 'por',
        'sem', 'aos', 'nas', 'nos', 'ser', 'ter', 'sao', 'cao', 'caes'
      ]);
      const words = stripAccents(text.toLowerCase())
        .replace(/[^a-z0-9\s-]/g, ' ')
        .split(/\s+/)
        .filter((word) => word.length >= 4 && !stopwords.has(word));
      const counts = new Map();
      words.forEach((word) => counts.set(word, (counts.get(word) || 0) + 1));
      return [...counts.entries()]
        .sort((a, b) => b[1] - a[1] || a[0].localeCompare(b[0]))
        .map(([word]) => word)
        .slice(0, 12);
    };

    const generateSeo = (force = false) => {
      syncBody();
      const title = titleInput?.value.trim() || '';
      const description = descriptionInput?.value.trim() || '';
      const bodyText = bodyEditor?.innerText.trim() || '';
      const sourceText = [title, description, bodyText].filter(Boolean).join(' ');
      const keywords = extractKeywords(sourceText);
      const firstParagraph = bodyText.split(/\n+/).map((line) => line.trim()).find(Boolean) || '';
      const generatedTitle = limitText(title || firstParagraph, 58);
      const generatedDescription = limitText(description || firstParagraph || sourceText, 155);
      const generatedTags = keywords.slice(0, 8).join(', ');
      const generatedKeywords = keywords.join(', ');

      const fill = (input, value) => {
        if (!input || !value) return;
        if (force || (!input.value.trim() && !touchedSeoFields.has(input))) {
          input.value = value;
        }
      };

      fill(seoTitleInput, generatedTitle);
      fill(seoDescriptionInput, generatedDescription);
      fill(tagsInput, generatedTags);
      fill(seoKeywordsInput, generatedKeywords);

      if (seoStatus) {
        seoStatus.textContent = force
          ? 'SEO gerado com base no conteudo atual.'
          : 'SEO preenchido automaticamente nos campos vazios.';
      }
    };

    seoFields.forEach((field) => {
      field.addEventListener('input', () => touchedSeoFields.add(field));
    });

    document.querySelectorAll('[data-command]').forEach((button) => {
      button.addEventListener('click', () => {
        bodyEditor?.focus();
        document.execCommand(button.dataset.command, false);
        syncBody();
      });
    });

    document.querySelectorAll('[data-block]').forEach((button) => {
      button.addEventListener('click', () => {
        bodyEditor?.focus();
        document.execCommand('formatBlock', false, button.dataset.block);
        syncBody();
      });
    });

    document.querySelector('[data-link]')?.addEventListener('click', () => {
      bodyEditor?.focus();
      const url = prompt('Cole o link completo:');
      if (!url) return;
      document.execCommand('createLink', false, url);
      syncBody();
    });

    document.querySelector('[data-hr]')?.addEventListener('click', () => {
      bodyEditor?.focus();
      document.execCommand('insertHorizontalRule', false);
      syncBody();
    });

    document.querySelector('[data-generate-seo]')?.addEventListener('click', () => generateSeo(true));
    [titleInput, descriptionInput].forEach((input) => input?.addEventListener('blur', () => generateSeo(false)));
    bodyEditor?.addEventListener('input', syncBody);
    bodyEditor?.addEventListener('blur', () => generateSeo(false));
    bodyEditor?.addEventListener('paste', () => setTimeout(() => {
      syncBody();
      generateSeo(false);
    }, 0));
    form?.addEventListener('submit', () => {
      syncBody();
      generateSeo(false);
    });

    const uploader = document.querySelector('[data-image-uploader]');
    if (uploader) {
      const textInput = uploader.querySelector('input[name="image"]');
      const fileInput = uploader.querySelector('input[name="image_upload"]');
      const dropzone = uploader.querySelector('.dropzone');
      const preview = uploader.querySelector('.image-preview');
      const imageFitSelect = document.querySelector('#imageFit');
      const imagePositionSelect = document.querySelector('#imagePosition');

      const updatePreviewStyle = () => {
        if (!preview) return;
        if (imageFitSelect) preview.style.objectFit = imageFitSelect.value;
        if (imagePositionSelect) preview.style.objectPosition = imagePositionSelect.value;
      };

      const showPreview = (file) => {
        if (!file || !preview) return;
        const url = URL.createObjectURL(file);
        preview.src = url;
        preview.hidden = false;
        updatePreviewStyle();
      };

      fileInput?.addEventListener('change', () => {
        const file = fileInput.files?.[0];
        if (file) showPreview(file);
      });

      textInput?.addEventListener('input', () => {
        if (!preview) return;
        const value = textInput.value.trim();
        preview.src = value;
        preview.hidden = value === '';
        updatePreviewStyle();
      });

      imageFitSelect?.addEventListener('change', updatePreviewStyle);
      imagePositionSelect?.addEventListener('change', updatePreviewStyle);
      updatePreviewStyle();

      ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone?.addEventListener(eventName, (event) => {
          event.preventDefault();
          dropzone.classList.add('is-dragover');
        });
      });

      ['dragleave', 'drop'].forEach((eventName) => {
        dropzone?.addEventListener(eventName, (event) => {
          event.preventDefault();
          dropzone.classList.remove('is-dragover');
        });
      });

      dropzone?.addEventListener('drop', (event) => {
        const file = event.dataTransfer?.files?.[0];
        if (!file || !fileInput) return;
        const transfer = new DataTransfer();
        transfer.items.add(file);
        fileInput.files = transfer.files;
        showPreview(file);
      });
    }
  </script>
</body>
</html>
