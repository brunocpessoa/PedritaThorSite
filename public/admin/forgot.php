<?php
require_once __DIR__ . '/_lib/auth.php';

if (!admin_credentials_exist()) {
    header('Location: setup.php');
    exit;
}

$error = '';
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Sessao invalida, recarregue a pagina e tente novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $creds = admin_load_credentials();

        // Always show the same success message, whether or not the e-mail matches,
        // so this form can't be used to confirm which e-mail is registered.
        if ($creds && hash_equals($creds['email'], $email)) {
            $token = bin2hex(random_bytes(32));
            $creds['reset_token_hash'] = hash('sha256', $token);
            $creds['reset_expires'] = time() + 1800; // 30 minutes
            admin_save_credentials($creds);

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'pedritaethor.com';
            $resetUrl = "$scheme://$host/admin/reset.php?token=$token";

            $subject = 'Redefinicao de senha - Admin Pedrita & Thor';
            $body = "Voce solicitou a redefinicao da senha do admin do site Pedrita & Thor.\n\n"
                . "Clique no link abaixo para criar uma nova senha (valido por 30 minutos):\n"
                . "$resetUrl\n\n"
                . "Se voce nao solicitou isso, ignore este e-mail.";
            $headers = "From: nao-responder@" . preg_replace('/^www\./', '', $host) . "\r\n";

            @mail($creds['email'], $subject, $body, $headers);
        }
        $sent = true;
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
  <title>Esqueci minha senha - Pedrita & Thor</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #f4f4f4; display: flex; min-height: 100vh; align-items: center; justify-content: center; margin: 0; }
    .card { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.1); width: 320px; }
    h1 { font-size: 18px; margin: 0 0 16px; }
    label { display: block; font-size: 13px; margin-bottom: 4px; color: #333; }
    input { width: 100%; padding: 8px; margin-bottom: 12px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
    button { width: 100%; padding: 10px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    .error { color: #b91c1c; font-size: 13px; margin-bottom: 12px; }
    .ok { color: #15803d; font-size: 13px; margin-bottom: 12px; }
    .links { margin-top: 8px; }
    .links a { font-size: 13px; color: #2563eb; text-decoration: none; }
  </style>
  <link rel="stylesheet" href="admin.css" />
</head>
<body>
  <div class="card">
    <h1>Esqueci minha senha</h1>
    <?php if ($sent): ?>
      <p class="ok">Se o e-mail informado estiver cadastrado, um link de redefinicao foi enviado.</p>
    <?php else: ?>
      <?php if ($error): ?><p class="error"><?= admin_e($error) ?></p><?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= admin_e($csrf) ?>" />
        <label>E-mail cadastrado</label>
        <input type="email" name="email" required autofocus />
        <button type="submit">Enviar link de redefinicao</button>
      </form>
    <?php endif; ?>
    <div class="links"><a href="login.php">Voltar para o login</a></div>
  </div>
</body>
</html>
