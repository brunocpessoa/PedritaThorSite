# Pedrita & Thor Site

Site editorial e vitrine de afiliados para `pedritaethor.com`, criado com Astro e conteudo em Markdown.

## Por que esta estrutura

- Nao depende de PHP.
- O site publico e rapido, estatico e bom para SEO.
- Artigos e produtos ficam em arquivos Markdown organizados.
- O painel Decap CMS pode editar os conteudos pelo navegador.
- A estrutura pode crescer para um CMS mais robusto no futuro, como Payload ou Directus.

## Comandos

```bash
npm install
npm run dev
npm run build
npm run preview
```

## Conteudos

- Artigos: `src/content/articles`
- Produtos: `src/content/products`
- Paginas institucionais: `src/content/pages`

## Painel editorial

Depois do deploy e da configuracao de login do Decap CMS, acesse:

```text
/admin/
```

Em desenvolvimento, o arquivo `public/admin/config.yml` esta com `local_backend: true`.

## Proximos ajustes importantes

1. Trocar imagens temporarias por fotos reais da Pedrita e do Thor.
2. Configurar Decap CMS com GitHub/GitLab/Bitbucket.
3. Definir links reais de Instagram, TikTok e YouTube.
4. Configurar Search Console e Analytics.
5. Criar politica de privacidade e aviso de afiliados definitivos.
