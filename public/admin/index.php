<?php
require_once __DIR__ . '/_lib/auth.php';

if (!admin_credentials_exist()) {
    header('Location: setup.php');
    exit;
}

admin_require_login();
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex" />
    <title>Pedrita & Thor Admin</title>
    <style>
      body { font-family: system-ui, sans-serif; background: #f4f4f4; margin: 0; padding: 32px; }
      .card { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.1); max-width: 520px; margin: 0 auto; }
      h1 { font-size: 18px; margin: 0 0 16px; }
      .links { margin-top: 24px; }
      a { color: #2563eb; text-decoration: none; }
    </style>
    <link rel="stylesheet" href="admin.css" />
  </head>
  <body>
    <div class="card">
      <h1>Admin - Pedrita & Thor</h1>
      <p>Voce esta logado. Use os links abaixo para gerenciar o conteudo do site.</p>
      <ul>
        <li><a href="content.php?type=articles">Artigos</a></li>
        <li><a href="content.php?type=products">Produtos</a></li>
        <li><a href="content.php?type=pages">Paginas</a></li>
        <li><a href="settings.php">Textos do site</a></li>
      </ul>
      <p>As alteracoes feitas aqui no admin entram no ar imediatamente, sem precisar de rebuild. Apenas mudancas de design, layout ou CSS continuam exigindo o fluxo local de build do Astro (<code>npm run build</code> e depois <code>npm run build:upload</code>, enviando o conteudo de <code>dist-upload/</code> para a Hostinger).</p>
      <div class="links"><a href="logout.php">Sair</a></div>
    </div>
  </body>
</html>
