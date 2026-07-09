# Publicacao na Hostinger

Este projeto usa Astro para gerar os arquivos estaticos e PHP para o admin e as paginas dinamicas de conteudo. Na Hostinger, publique o conteudo de `dist-upload/` dentro de `public_html`.

Nao publique `dist/` diretamente, porque ela contem HTML estatico para rotas que devem ser atendidas pelo PHP.

## 1. Gerar o pacote de upload

No PowerShell:

```powershell
cd C:\projects\PedritaThorSite
npm install
npm run build:upload
```

Ao final, sera criada a pasta:

```text
C:\projects\PedritaThorSite\dist-upload
```

Essa pasta inclui:

- paginas estaticas geradas pelo Astro;
- assets em `_astro/`;
- PHPs publicos;
- admin em `admin/`;
- `.htaccess` com as regras de rota.

Ela exclui as pastas dinamicas `artigos/`, `produtos-que-amamos/`, `noticias/`, `curiosidades/`, `cuidados-com-seu-animal/` e `content/` para evitar conflito com as rotas PHP e para nao sobrescrever conteudo editado no servidor.

## 2. Fazer backup do site atual

Antes de substituir arquivos:

1. Entre no hPanel da Hostinger.
2. Abra `Files > File Manager`.
3. Acesse `public_html`.
4. Compacte ou baixe os arquivos atuais.
5. Se existir WordPress antigo, guarde tambem backup do banco de dados.
6. Se o admin PHP ja estiver em uso, faca backup de `public_html/content/` e `public_html/admin/_data/`.

## 3. Publicar pelo File Manager

1. No hPanel, abra `Files > File Manager`.
2. Entre em `public_html`.
3. Envie todos os arquivos de dentro de `dist-upload/`.
4. Nao envie a pasta `dist-upload` como uma pasta dentro de `public_html`; envie o conteudo dela.
5. No primeiro deploy, envie tambem a pasta `public/content/` como semente caso `public_html/content/` ainda nao exista.
6. Em deploys futuros, nao sobrescreva `public_html/content/` sem backup.

O resultado deve ficar parecido com:

```text
public_html/
  .htaccess
  404.html
  logo.png
  favicon.png
  robots.txt
  sitemap-index.xml
  _astro/
  _inc/
  admin/
  content/
  artigos.php
  produto.php
  pagina.php
  noticias.php
  curiosidades.php
  cuidados-com-seu-animal.php
  produtos-que-amamos/
```

## 4. Configurar o admin

Depois do upload, acesse:

```text
https://pedritaethor.com/admin/
```

Se ainda nao existir usuario, o sistema abrira a tela de criacao do acesso inicial. Depois disso, `setup.php` passa a redirecionar para o login.

As credenciais ficam em:

```text
public_html/admin/_data/credentials.json
```

Esse arquivo nao deve ir para o git nem ser compartilhado.

## 5. Conferir no dominio

Abra:

```text
https://pedritaethor.com/
https://pedritaethor.com/produtos-que-amamos/
https://pedritaethor.com/noticias/
https://pedritaethor.com/curiosidades/
https://pedritaethor.com/cuidados-com-seu-animal/
https://pedritaethor.com/admin/
https://pedritaethor.com/robots.txt
https://pedritaethor.com/sitemap-index.xml
```

Tambem teste um artigo e um produto individuais.

## 6. Observacoes importantes

- Mudancas de conteudo feitas pelo admin entram no ar sem rebuild.
- Mudancas de layout, CSS, componentes Astro ou scripts exigem `npm run build:upload` e novo upload.
- O arquivo `public/_inc/layout.php` encontra automaticamente o CSS gerado pelo Astro em `_astro/`, sem depender do hash do nome do arquivo.
- Se a Hostinger nao enviar e-mails pelo `mail()`, a recuperacao de senha pode exigir ajuste de SMTP no futuro.
