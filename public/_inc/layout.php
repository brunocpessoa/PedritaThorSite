<?php
declare(strict_types=1);

// Funcoes compartilhadas de layout para as paginas PHP publicas, extraidas de
// src/layouts/BaseLayout.astro, src/components/Header.astro e Footer.astro.
// Reaproveita exatamente as mesmas classes CSS e o CSS compartilhado do build Astro.
require_once __DIR__ . '/../admin/_lib/settings.php';

define('SITE_NAME', 'Pedrita & Thor');
define('SITE_URL', 'https://pedritaethor.com');
define('SITE_DESCRIPTION', site_setting('site_description'));
define('SITE_CSS_FALLBACK_HREF', '/_astro/_slug_.BzuyN8KV.css');
define('SITE_INSTAGRAM_URL', 'https://www.instagram.com/pedritaethorstore?igsh=MThlZ3BxcnA1NmV5NQ%3D%3D');
define('SITE_TIKTOK_URL', 'https://www.tiktok.com/@pedritaethor');
define('SITE_YOUTUBE_URL', 'https://www.youtube.com/@pedritaethor');
define('SITE_SHOPEE_STORE_URL', 'https://s.shopee.com.br/6VM8ojrAOa?share_channel_code=1');

function site_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function site_url(string $path = '/'): string
{
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }

    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

function site_json_ld(array $data): void
{
    echo '<script type="application/ld+json">' .
        json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
        '</script>' . "\n";
}

function site_organization_schema(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => SITE_NAME,
        'url' => SITE_URL,
        'logo' => site_url('/logo.png'),
        'sameAs' => [
            SITE_INSTAGRAM_URL,
            SITE_TIKTOK_URL,
            SITE_YOUTUBE_URL,
            SITE_SHOPEE_STORE_URL,
        ],
    ];
}

function site_website_schema(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => SITE_NAME,
        'url' => SITE_URL,
    ];
}

function site_breadcrumb_schema(array $items): array
{
    $elements = [];
    $position = 1;

    foreach ($items as $item) {
        $element = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => (string) ($item['name'] ?? ''),
        ];

        if (!empty($item['item'])) {
            $element['item'] = site_url((string) $item['item']);
        }

        $elements[] = $element;
        $position++;
    }

    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $elements,
    ];
}

/**
 * Encontra o CSS gerado pelo Astro em /_astro sem depender do hash do arquivo.
 */
function site_css_href(): string
{
    static $href = null;

    if ($href !== null) {
        return $href;
    }

    $astroDir = dirname(__DIR__) . '/_astro';
    $files = is_dir($astroDir) ? glob($astroDir . '/*.css') : false;

    if (is_array($files) && !empty($files)) {
        usort($files, function (string $a, string $b): int {
            $mtimeCompare = filemtime($b) <=> filemtime($a);
            return $mtimeCompare !== 0 ? $mtimeCompare : strcmp(basename($a), basename($b));
        });

        $href = '/_astro/' . basename($files[0]);
        return $href;
    }

    $href = SITE_CSS_FALLBACK_HREF;
    return $href;
}

/**
 * Formata uma data no padrao pt-BR usado pelo site: "15 de junho de 2026".
 */
function format_date_ptbr(string $isoDate): string
{
    $months = [
        1 => 'janeiro', 2 => 'fevereiro', 3 => 'marco', 4 => 'abril',
        5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
        9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
    ];

    $ts = strtotime($isoDate);
    if ($ts === false) {
        return site_e($isoDate);
    }

    $day = (int) date('j', $ts);
    $month = $months[(int) date('n', $ts)] ?? '';
    $year = date('Y', $ts);

    return sprintf('%02d de %s de %s', $day, $month, $year);
}

function content_seo_meta(array $data): array
{
    $keywords = $data['seoKeywords'] ?? [];
    if ((!is_array($keywords) || empty($keywords)) && isset($data['tags']) && is_array($data['tags'])) {
        $keywords = $data['tags'];
    }

    return [
        'title' => (string) ($data['seoTitle'] ?? ''),
        'description' => (string) ($data['seoDescription'] ?? ''),
        'keywords' => is_array($keywords) ? $keywords : [],
    ];
}

/**
 * Mapa de categorias de artigos, replicando src/lib/site.ts articleCategories.
 */
function article_categories(): array
{
    return [
        'cuidados' => [
            'title' => site_setting('category_cuidados_title'),
            'description' => site_setting('category_cuidados_description'),
            'path' => '/cuidados-com-seu-animal/',
        ],
        'curiosidades' => [
            'title' => site_setting('category_curiosidades_title'),
            'description' => site_setting('category_curiosidades_description'),
            'path' => '/curiosidades/',
        ],
        'noticias' => [
            'title' => site_setting('category_noticias_title'),
            'description' => site_setting('category_noticias_description'),
            'path' => '/noticias/',
        ],
    ];
}

function site_published_pages(): array
{
    if (!function_exists('content_list')) {
        return [];
    }

    $items = content_list('pages');
    $pages = [];

    foreach ($items as $item) {
        if (!empty($item['data']['draft'])) {
            continue;
        }

        $slug = (string) ($item['slug'] ?? '');
        if ($slug === '') {
            continue;
        }

        $linkLabel = trim((string) ($item['data']['linkLabel'] ?? ''));
        if ($linkLabel === '') {
            $labels = [
                'sobre' => 'Sobre',
                'aviso-de-afiliados' => 'Afiliados',
                'politica-de-privacidade' => 'Privacidade',
            ];
            $linkLabel = $labels[$slug] ?? (string) ($item['data']['title'] ?? $slug);
        }

        $pages[] = [
            'href' => '/' . $slug . '/',
            'label' => $linkLabel,
            'slug' => $slug,
        ];
    }

    usort($pages, function ($a, $b) {
        $priority = [
            'sobre' => 10,
            'aviso-de-afiliados' => 90,
            'politica-de-privacidade' => 100,
        ];

        $ap = $priority[$a['slug']] ?? 50;
        $bp = $priority[$b['slug']] ?? 50;

        if ($ap !== $bp) {
            return $ap <=> $bp;
        }

        return strcasecmp($a['label'], $b['label']);
    });

    return $pages;
}

/**
 * Renderiza o <head> da pagina (equivalente ao BaseLayout.astro).
 */
function render_head(string $title, string $description, string $canonicalPath = '/', ?string $image = null, array $seo = [], array $structuredData = []): void
{
    $seoTitle = trim((string) ($seo['title'] ?? ''));
    $seoDescription = trim((string) ($seo['description'] ?? ''));
    $keywords = $seo['keywords'] ?? [];
    $headTitle = $seoTitle !== '' ? $seoTitle : $title;
    $headDescription = $seoDescription !== '' ? $seoDescription : $description;

    $fullTitle = ($title === SITE_NAME)
        ? SITE_NAME . ' | Conteudo pet e produtos recomendados'
        : $headTitle . ' | ' . SITE_NAME;

    $canonical = site_url($canonicalPath);
    $imagePath = $image ?: '/logo.png';
    $imageUrl = site_url($imagePath);
    ?>
<meta charset="UTF-8">
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-X5G8VTNHVW"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-X5G8VTNHVW');
</script>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= site_e($fullTitle) ?></title>
<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
<meta name="publisher" content="<?= site_e(SITE_NAME) ?>">
<meta name="description" content="<?= site_e($headDescription) ?>">
<?php if (is_array($keywords) && !empty($keywords)): ?>
<meta name="keywords" content="<?= site_e(implode(', ', array_filter(array_map('strval', $keywords)))) ?>">
<?php endif; ?>
<link rel="canonical" href="<?= site_e($canonical) ?>">
<meta property="og:locale" content="pt_BR">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= site_e(SITE_NAME) ?>">
<meta property="og:title" content="<?= site_e($fullTitle) ?>">
<meta property="og:description" content="<?= site_e($headDescription) ?>">
<meta property="og:url" content="<?= site_e($canonical) ?>">
<meta property="og:image" content="<?= site_e($imageUrl) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= site_e($fullTitle) ?>">
<meta name="twitter:description" content="<?= site_e($headDescription) ?>">
<meta name="twitter:image" content="<?= site_e($imageUrl) ?>">
<meta name="theme-color" content="#f7c948">
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="stylesheet" href="<?= site_e(site_css_href()) ?>">
<?php
    site_json_ld(site_organization_schema());
    site_json_ld(site_website_schema());
    foreach ($structuredData as $schema) {
        if (is_array($schema) && !empty($schema)) {
            site_json_ld($schema);
        }
    }
?>
    <?php
}

/**
 * Renderiza o cabecalho do site (Header.astro), identico em todas as paginas.
 */
function render_header(): void
{
    $pageLinks = site_published_pages();
    $links = array_merge([
        ['href' => '/', 'label' => 'Inicio'],
    ], $pageLinks, [
        ['href' => '/produtos-que-amamos/', 'label' => 'Produtos'],
        ['href' => '/cuidados-com-seu-animal/', 'label' => 'Cuidados'],
        ['href' => '/curiosidades/', 'label' => 'Curiosidades'],
        ['href' => '/noticias/', 'label' => 'Noticias'],
    ]);
    ?>
<a class="skip-link" href="#conteudo">Pular para o conteudo</a>
<header class="site-header">
  <div class="site-header__inner">
    <a class="brand" href="/" aria-label="<?= site_e(SITE_NAME) ?>">
      <img class="brand__logo" src="/logo.png" alt="Pedrita & Thor" width="180" height="64">
    </a>
    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="menu-principal" aria-label="Abrir menu">
      Menu
    </button>
    <nav class="site-nav" aria-label="Menu principal">
      <ul id="menu-principal">
        <?php foreach ($links as $link): ?>
        <li><a href="<?= site_e($link['href']) ?>"><?= site_e($link['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
</header>
<script type="module">const e=document.querySelector(".menu-toggle"),n=document.querySelector(".site-nav");e?.addEventListener("click",()=>{const t=e.getAttribute("aria-expanded")==="true";e.setAttribute("aria-expanded",String(!t)),n?.classList.toggle("is-open",!t)});n?.addEventListener("click",t=>{t.target instanceof HTMLAnchorElement&&(e?.setAttribute("aria-expanded","false"),n.classList.remove("is-open"))});</script>
    <?php
}

/**
 * Renderiza o rodape do site (Footer.astro), identico em todas as paginas.
 */
function render_footer(): void
{
    $year = date('Y');
    $pageLinks = site_published_pages();
    ?>
<footer class="site-footer">
  <div class="container site-footer__inner">
    <p>&copy; <?= site_e($year) ?> Pedrita & Thor</p>
    <nav aria-label="Links do rodape">
      <a href="/">Inicio</a>
      <a href="/produtos-que-amamos/">Produtos</a>
      <a href="/noticias/">Noticias</a>
      <?php foreach ($pageLinks as $pageLink): ?>
      <a href="<?= site_e($pageLink['href']) ?>"><?= site_e($pageLink['label']) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</footer>
    <?php
}

function sanitize_rich_content(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $html = preg_replace('/<\s*(script|style|iframe|object|embed|form|input|button|textarea|select)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $html) ?? '';
    $html = preg_replace('/<\s*(script|style|iframe|object|embed|form|input|button|textarea|select)[^>]*\/?\s*>/is', '', $html) ?? '';
    $html = strip_tags($html, '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><blockquote><hr>');
    $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
    $html = preg_replace('/\s+(style|class|id)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
    $html = preg_replace('/href\s*=\s*([\'"])\s*javascript:[^\'"]*\1/i', 'href="#"', $html) ?? '';
    $html = preg_replace('/<a\b(?![^>]*\brel=)([^>]*)>/i', '<a$1 rel="nofollow noopener">', $html) ?? '';
    $html = preg_replace('/<a\b(?![^>]*\btarget=)([^>]*)>/i', '<a$1 target="_blank">', $html) ?? '';

    return $html;
}

/**
 * Renderiza conteudo salvo pelo admin. Conteudo novo pode vir em HTML
 * formatado; conteudo antigo em texto simples continua virando paragrafos.
 */
function render_markdown_basic(string $content): string
{
    $content = str_replace("\r\n", "\n", trim($content));
    if ($content === '') {
        return '';
    }

    if (preg_match('/<([a-z][a-z0-9]*)\b/i', $content)) {
        return sanitize_rich_content($content);
    }

    $blocks = preg_split('/\n\s*\n/', $content);
    $output = '';

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        $escaped = site_e($block);
        $escaped = nl2br($escaped);
        $output .= '<p>' . $escaped . '</p>' . "\n";
    }

    return $output;
}
