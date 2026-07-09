# Pedrita & Thor Site

Site editorial e vitrine de afiliados para `pedritaethor.com`.

O projeto combina:

- Astro para layout, paginas estaticas, CSS, sitemap e build.
- PHP para o admin online e para conteudos editaveis em producao.
- Markdown como armazenamento simples de artigos, produtos e paginas.

## Estrutura

- `src/`: codigo Astro do site estatico.
- `src/content/`: conteudo usado pelo Astro como base local.
- `public/admin/`: painel PHP de login, cadastro, edicao e recuperacao de senha.
- `public/content/`: conteudo Markdown lido e salvo pelo admin PHP.
- `public/_inc/layout.php`: layout compartilhado pelas paginas PHP publicas.
- `scripts/build-upload.mjs`: cria o pacote seguro para upload na Hostinger.
- `dist/`: saida bruta do Astro.
- `dist-upload/`: pacote recomendado para enviar ao `public_html`.

## Comandos

```bash
npm install
npm run dev
npm run build
npm run build:upload
```

Use `npm run dev` para trabalhar no Astro localmente.

Use `npm run build:upload` antes de publicar na Hostinger. Esse comando gera `dist-upload/` com os arquivos estaticos, PHPs e assets necessarios, mas sem sobrescrever as pastas dinamicas que devem continuar sendo gerenciadas pelo admin.

## Conteudo

No servidor, o admin PHP salva conteudo em:

- Artigos: `public/content/articles`
- Produtos: `public/content/products`
- Paginas institucionais editaveis: `public/content/pages`

Os arquivos em `src/content/` continuam servindo como fonte local para o Astro e como semente inicial, mas o conteudo vivo em producao fica em `public/content/`.

## Admin

Acesse:

```text
/admin/
```

Na primeira visita, se ainda nao existir credencial, o site redireciona para `setup.php` para criar o acesso inicial.

As credenciais ficam em `public/admin/_data/credentials.json`, ignoradas pelo git e protegidas por `.htaccess`.

## Deploy rapido

```powershell
cd C:\projects\PedritaThorSite
npm install
npm run build:upload
```

Depois envie o conteudo de `dist-upload/` para `public_html`.

No primeiro deploy do admin, tambem garanta que `public/content/` exista no servidor como semente. Nos deploys seguintes, nao substitua essa pasta sem backup, porque ela pode conter conteudo editado pelo painel.

## Proximos ajustes importantes

1. Trocar imagens temporarias por fotos reais da Pedrita e do Thor.
2. Definir links reais de Instagram, TikTok e YouTube.
3. Configurar Search Console e Analytics.
4. Revisar politica de privacidade e aviso de afiliados definitivos.
5. Validar o PHP no ambiente da Hostinger depois do upload.
