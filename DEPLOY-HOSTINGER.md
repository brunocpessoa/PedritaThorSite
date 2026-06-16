# Publicacao na Hostinger

Este projeto Astro gera um site estatico. Na Hostinger, o caminho mais simples e publicar o conteudo da pasta `dist/` dentro de `public_html`.

## 1. Gerar o site

No PowerShell:

```powershell
cd C:\projects\BolaoPedritaThor\PedritaThorSite
npm install
npm run build
```

Ao final, o Astro cria a pasta:

```text
C:\projects\BolaoPedritaThor\PedritaThorSite\dist
```

## 2. Fazer backup do WordPress atual

Antes de substituir o site antigo:

1. Entre no hPanel da Hostinger.
2. Abra `Files > File Manager`.
3. Acesse `public_html`.
4. Compacte ou baixe os arquivos atuais do WordPress.
5. Guarde tambem um backup do banco de dados, se quiser manter uma copia completa do WordPress antigo.

## 3. Publicar pelo File Manager

1. No hPanel, abra seu site `pedritaethor.com`.
2. Va em `Files > File Manager`.
3. Entre na pasta `public_html`.
4. Remova os arquivos antigos do WordPress depois do backup.
5. Envie todos os arquivos de dentro da pasta `dist/`.
6. Nao envie a pasta `dist` como uma pasta dentro de `public_html`; envie o conteudo dela.

O resultado deve ficar assim:

```text
public_html/
  index.html
  404.html
  logo.png
  favicon.svg
  robots.txt
  sitemap-index.xml
  _astro/
  artigos/
  produtos-que-amamos/
```

## 4. Conferir no dominio

Abra:

```text
https://pedritaethor.com/
```

Depois confira:

```text
https://pedritaethor.com/produtos-que-amamos/
https://pedritaethor.com/robots.txt
https://pedritaethor.com/sitemap-index.xml
```

## Observacao sobre o painel `/admin`

O painel Decap CMS foi deixado preparado, mas em hospedagem estatica da Hostinger ele nao fica funcional automaticamente.

Para editar conteudo agora, use os arquivos Markdown em:

```text
src/content/articles
src/content/products
src/content/pages
```

Depois de editar, rode `npm run build` de novo e publique a nova `dist/`.

Para ter painel online de verdade, os caminhos recomendados sao:

- hospedar o site em Netlify/Cloudflare Pages e apontar o dominio;
- ou configurar um CMS externo;
- ou criar uma area administrativa propria no futuro.
