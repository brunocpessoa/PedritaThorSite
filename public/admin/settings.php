<?php
declare(strict_types=1);

require_once __DIR__ . '/_lib/auth.php';
require_once __DIR__ . '/_lib/settings.php';
require_once __DIR__ . '/_lib/uploads.php';

if (!admin_credentials_exist()) {
    header('Location: setup.php');
    exit;
}

admin_require_login();

$errors = [];
$saved = isset($_GET['saved']);
$fields = site_settings_fields();
$values = site_settings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sessao invalida, recarregue a pagina e tente novamente.';
    } else {
        foreach ($fields as $groupFields) {
            foreach ($groupFields as $key => $field) {
                if (($field['type'] ?? 'text') !== 'image') {
                    continue;
                }

                $uploadedImage = admin_handle_named_image_upload('upload_' . $key, $errors);
                if ($uploadedImage !== null) {
                    $_POST[$key] = $uploadedImage;
                }
            }
        }

        if (empty($errors)) {
            site_settings_save($_POST);
            header('Location: settings.php?saved=1');
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $defaultValue) {
        if (isset($_POST[$key]) && is_string($_POST[$key])) {
            $values[$key] = $_POST[$key];
        }
    }
}

$csrf = admin_csrf_token();
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex" />
  <title>Textos do site - Pedrita & Thor Admin</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #f4f4f4; margin: 0; padding: 32px; }
    .card { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.1); max-width: 920px; margin: 0 auto; }
    h1 { font-size: 22px; margin: 0 0 8px; }
    h2 { font-size: 15px; margin: 28px 0 14px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
    a { color: #2563eb; text-decoration: none; }
    label { display: block; font-size: 13px; margin-bottom: 5px; color: #333; font-weight: 700; }
    input[type=text], textarea {
      width: 100%; padding: 9px 10px; margin-bottom: 16px; box-sizing: border-box;
      border: 1px solid #ccc; border-radius: 5px; font-family: inherit; font-size: 14px;
    }
    textarea { min-height: 92px; resize: vertical; line-height: 1.5; }
    button { padding: 10px 18px; background: #15803d; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: 700; }
    .intro { color: #555; margin: 0 0 20px; }
    .ok { color: #15803d; font-size: 13px; margin-bottom: 12px; }
    .error { color: #b91c1c; font-size: 13px; margin-bottom: 12px; }
    .error ul { margin: 0; padding-left: 18px; }
    .actions { display: flex; gap: 14px; align-items: center; margin-top: 24px; flex-wrap: wrap; }
    .back { display: inline-flex; font-weight: 700; }
    .image-uploader { display: grid; grid-template-columns: minmax(0, 1fr) 220px; gap: 14px; align-items: start; margin-bottom: 18px; }
    .image-uploader input[type=text] { margin-bottom: 10px; }
    .dropzone {
      display: grid; gap: 5px; place-items: center; min-height: 92px; padding: 14px; text-align: center;
      border: 1px dashed #9ca3af; border-radius: 8px; background: #f8fafc; cursor: pointer;
    }
    .dropzone.is-dragover { border-color: #15803d; background: #ecfdf3; }
    .dropzone input { position: absolute; inline-size: 1px; block-size: 1px; opacity: 0; pointer-events: none; }
    .dropzone span { font-size: 13px; font-weight: 800; color: #333; }
    .dropzone small { color: #777; }
    .image-preview {
      width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 8px;
      border: 1px solid #e5e7eb; background: #f8fafc;
    }
    @media (max-width: 720px) {
      .image-uploader { grid-template-columns: 1fr; }
    }
  </style>
  <link rel="stylesheet" href="admin.css" />
</head>
<body>
  <div class="card">
    <h1>Textos do site</h1>
    <p class="intro">Edite aqui os textos das telas principais. As alteracoes entram no ar imediatamente nas paginas PHP do site.</p>

    <?php if ($saved): ?><p class="ok">Textos salvos.</p><?php endif; ?>
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

      <?php foreach ($fields as $group => $groupFields): ?>
      <section>
        <h2><?= admin_e($group) ?></h2>
        <?php foreach ($groupFields as $key => $field): ?>
        <?php
        $label = (string) ($field['label'] ?? $key);
        $type = (string) ($field['type'] ?? 'text');
        $value = (string) ($values[$key] ?? '');
        ?>
        <label for="<?= admin_e($key) ?>"><?= admin_e($label) ?></label>
        <?php if ($type === 'textarea'): ?>
        <textarea id="<?= admin_e($key) ?>" name="<?= admin_e($key) ?>"><?= admin_e($value) ?></textarea>
        <?php elseif ($type === 'image'): ?>
        <div class="image-uploader" data-image-uploader>
          <div>
            <input type="text" id="<?= admin_e($key) ?>" name="<?= admin_e($key) ?>" value="<?= admin_e($value) ?>" placeholder="/uploads/imagem.webp ou https://..." />
            <label class="dropzone" for="upload_<?= admin_e($key) ?>">
              <input type="file" id="upload_<?= admin_e($key) ?>" name="upload_<?= admin_e($key) ?>" accept="image/jpeg,image/png,image/webp,image/gif" />
              <span>Arraste uma imagem aqui ou clique para escolher</span>
              <small>JPG, PNG, WebP ou GIF ate 5 MB</small>
            </label>
          </div>
          <img class="image-preview" src="<?= admin_e($value) ?>" alt="" <?= $value !== '' ? '' : 'hidden' ?> />
        </div>
        <?php else: ?>
        <input type="text" id="<?= admin_e($key) ?>" name="<?= admin_e($key) ?>" value="<?= admin_e($value) ?>" />
        <?php endif; ?>
        <?php endforeach; ?>
      </section>
      <?php endforeach; ?>

      <div class="actions">
        <button type="submit">Salvar textos</button>
        <a class="back" href="index.php">&larr; Voltar ao painel</a>
      </div>
    </form>
  </div>
  <script>
    document.querySelectorAll('[data-image-uploader]').forEach((uploader) => {
      const textInput = uploader.querySelector('input[type="text"]');
      const fileInput = uploader.querySelector('input[type="file"]');
      const dropzone = uploader.querySelector('.dropzone');
      const preview = uploader.querySelector('.image-preview');

      const showPreview = (file) => {
        if (!file || !preview) return;
        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
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
      });

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
    });
  </script>
</body>
</html>
