<?php
require_once __DIR__ . '/_lib/auth.php';

if (!admin_credentials_exist()) {
    header('Location: setup.php');
    exit;
}

$creds = admin_load_credentials();
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$error = '';

$tokenValid = $creds
    && !empty($creds['reset_token_hash'])
    && !empty($creds['reset_expires'])
    && $creds['reset_expires'] >= time()
    && hash_equals($creds['reset_token_hash'], hash('sha256', $token));

if (!$tokenValid) {
    $error = 'Link invalido ou expirado. Solicite uma nova redefinicao.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Sessao invalida, recarregue a pagina e tente novamente.';
    } else {
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (strlen($password) < 8) {
            $error = 'A senha deve ter pelo menos 8 caracteres.';
        } elseif ($password !== $passwordConfirm) {
            $error = 'As senhas nao coincidem.';
        } else {
            $creds['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            $creds['reset_token_hash'] = null;
            $creds['reset_expires'] = null;
            admin_save_credentials($creds);
            header('Location: login.php?reset=1');
            exit;
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
  <title>Redefinir senha - Pedrita & Thor</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #f4f4f4; display: flex; min-height: 100vh; align-items: center; justify-content: center; margin: 0; }
    .card { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.1); width: 320px; }
    h1 { font-size: 18px; margin: 0 0 16px; }
    label { display: block; font-size: 13px; margin-bottom: 4px; color: #333; }
    input { width: 100%; padding: 8px; margin-bottom: 12px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
    button { width: 100%; padding: 10px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    .error { color: #b91c1c; font-size: 13px; margin-bottom: 12px; }
    .links { margin-top: 8px; }
    .links a { font-size: 13px; color: #2563eb; text-decoration: none; }
  </style>
  <link rel="stylesheet" href="admin.css" />
</head>
<body>
  <div class="card">
    <h1>Definir nova senha</h1>
    <?php if ($error): ?>
      <p class="error"><?= admin_e($error) ?></p>
      <div class="links"><a href="forgot.php">Solicitar novo link</a></div>
    <?php else: ?>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= admin_e($csrf) ?>" />
        <input type="hidden" name="token" value="<?= admin_e($token) ?>" />
        <label>Nova senha</label>
        <input type="password" name="password" minlength="8" required autofocus />
        <label>Confirmar nova senha</label>
        <input type="password" name="password_confirm" minlength="8" required />
        <button type="submit">Salvar nova senha</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
