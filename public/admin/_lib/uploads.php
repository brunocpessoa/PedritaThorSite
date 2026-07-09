<?php
declare(strict_types=1);

function admin_upload_slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'imagem';
}

function admin_handle_named_image_upload(string $inputName, array &$errors): ?string
{
    if (empty($_FILES[$inputName]) || !is_array($_FILES[$inputName])) {
        return null;
    }

    $file = $_FILES[$inputName];
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

    $uploadsDir = dirname(__DIR__, 2) . '/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    $baseName = pathinfo((string) ($file['name'] ?? 'imagem'), PATHINFO_FILENAME);
    $fileName = admin_upload_slugify($baseName) . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = $uploadsDir . '/' . $fileName;

    if (!move_uploaded_file($tmpName, $target)) {
        $errors[] = 'Nao foi possivel salvar a imagem enviada.';
        return null;
    }

    chmod($target, 0644);
    return '/uploads/' . $fileName;
}
