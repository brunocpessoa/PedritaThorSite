<?php
require_once __DIR__ . '/_lib/auth.php';

if (!admin_credentials_exist()) {
    header('Location: setup.php');
    exit;
}

if (admin_is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$created = isset($_GET['created']);
$resetOk = isset($_GET['reset']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Sessao invalida, recarregue a pagina e tente novamente.';
    } else {
        $password = $_POST['password'] ?? '';
        $creds = admin_load_credentials();

        if ($creds && password_verify($password, $creds['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            header('Location: index.php');
            exit;
        }
        $error = 'Senha incorreta.';
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
  <title>Login admin - Pedrita & Thor</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #f4f4f4; display: flex; min-height: 100vh; align-items: center; justify-content: center; margin: 0; }
    .card { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.1); width: 320px; }
    h1 { font-size: 18px; margin: 0 0 16px; }
    label { display: block; font-size: 13px; margin-bottom: 4px; color: #333; }
    input { width: 100%; padding: 8px; margin-bottom: 12px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
    button { width: 100%; padding: 10px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    .error { color: #b91c1c; font-size: 13px; margin-bottom: 12px; }
    .ok { color: #15803d; font-size: 13px; margin-bottom: 12px; }
    .links { display: flex; justify-content: space-between; margin-top: 8px; }
    .links a { font-size: 13px; color: #2563eb; text-decoration: none; }
  </style>
  <link rel="stylesheet" href="admin.css" />
</head>
<body>
  <div class="card">
    <h1>Admin - Pedrita & Thor</h1>
    <?php if ($created): ?><p class="ok">Acesso criado. Faca login com a senha cadastrada.</p><?php endif; ?>
    <?php if ($resetOk): ?><p class="ok">Senha redefinida. Faca login com a nova senha.</p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= admin_e($error) ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= admin_e($csrf) ?>" />
      <label>Senha</label>
      <input type="password" name="password" required autofocus />
      <button type="submit">Entrar</button>
    </form>
    <div class="links">
      <a href="forgot.php">Esqueci minha senha</a>
    </div>
  </div>
</body>
</html>
