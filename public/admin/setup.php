<?php
require_once __DIR__ . '/_lib/auth.php';

if (admin_credentials_exist()) {
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Sessao invalida, recarregue a pagina e tente novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Informe um e-mail valido.';
        } elseif (strlen($password) < 8) {
            $error = 'A senha deve ter pelo menos 8 caracteres.';
        } elseif ($password !== $passwordConfirm) {
            $error = 'As senhas nao coincidem.';
        } else {
            admin_save_credentials([
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'reset_token_hash' => null,
                'reset_expires' => null,
            ]);
            header('Location: login.php?created=1');
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
  <title>Configurar admin - Pedrita & Thor</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #f4f4f4; display: flex; min-height: 100vh; align-items: center; justify-content: center; margin: 0; }
    .card { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.1); width: 320px; }
    h1 { font-size: 18px; margin: 0 0 16px; }
    label { display: block; font-size: 13px; margin-bottom: 4px; color: #333; }
    input { width: 100%; padding: 8px; margin-bottom: 12px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
    button { width: 100%; padding: 10px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    .error { color: #b91c1c; font-size: 13px; margin-bottom: 12px; }
    .hint { font-size: 12px; color: #666; margin-top: 12px; }
  </style>
  <link rel="stylesheet" href="admin.css" />
</head>
<body>
  <div class="card">
    <h1>Criar acesso de admin</h1>
    <?php if ($error): ?><p class="error"><?= admin_e($error) ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= admin_e($csrf) ?>" />
      <label>E-mail (recebe o link de recuperacao de senha)</label>
      <input type="email" name="email" required />
      <label>Senha</label>
      <input type="password" name="password" minlength="8" required />
      <label>Confirmar senha</label>
      <input type="password" name="password_confirm" minlength="8" required />
      <button type="submit">Criar acesso</button>
    </form>
    <p class="hint">Esta pagina so funciona enquanto nenhum admin tiver sido criado. Depois de salvar, ela passa a redirecionar para o login.</p>
  </div>
</body>
</html>
