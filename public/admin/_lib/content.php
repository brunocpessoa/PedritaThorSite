<?php
declare(strict_types=1);

// Biblioteca de conteudo: le e escreve os arquivos Markdown + frontmatter
// em public/content/{articles,products,pages}, sem depender de bibliotecas
// externas de YAML/Markdown. E um parser pragmatico, feito para round-trip
// fiel apenas do formato realmente usado nos arquivos deste site.

define('CONTENT_BASE_DIR', __DIR__ . '/../../content');

/**
 * Schema de cada tipo de conteudo: ordem estavel das chaves do frontmatter
 * e o "tipo" de cada campo, usado tanto no parse quanto na regeracao.
 * Tipos: string, bool, date, list, url
 */
function content_schema(string $type): array
{
    switch ($type) {
        case 'articles':
            return [
                'title' => 'string',
                'description' => 'string',
                'seoTitle' => 'string_optional',
                'seoDescription' => 'string_optional',
                'seoKeywords' => 'list',
                'publishDate' => 'date',
                'category' => 'string',
                'tags' => 'list',
                'image' => 'string_optional',
                'imageFit' => 'string_optional',
                'imagePosition' => 'string_optional',
                'draft' => 'bool',
            ];
        case 'products':
            return [
                'title' => 'string',
                'description' => 'string',
                'seoTitle' => 'string_optional',
                'seoDescription' => 'string_optional',
                'seoKeywords' => 'list',
                'category' => 'string',
                'platform' => 'string',
                'affiliateUrl' => 'string',
                'image' => 'string_optional',
                'badge' => 'string_optional',
                'tags' => 'list',
                'featured' => 'bool',
                'draft' => 'bool',
            ];
        case 'pages':
            return [
                'title' => 'string',
                'linkLabel' => 'string_optional',
                'description' => 'string',
                'seoTitle' => 'string_optional',
                'seoDescription' => 'string_optional',
                'seoKeywords' => 'list',
                'image' => 'string_optional',
                'tags' => 'list',
                'draft' => 'bool',
            ];
        default:
            throw new InvalidArgumentException('Tipo de conteudo invalido: ' . $type);
    }
}

function content_dir(string $type): string
{
    $allowed = ['articles', 'products', 'pages'];
    if (!in_array($type, $allowed, true)) {
        throw new InvalidArgumentException('Tipo de conteudo invalido: ' . $type);
    }
    return CONTENT_BASE_DIR . '/' . $type;
}

function content_default_pages(): array
{
    return [
        'sobre' => [
            'data' => [
                'title' => 'Sobre Pedrita & Thor',
                'linkLabel' => 'Sobre',
                'description' => 'Um projeto pet criado para unir rotina real, informacao util, curiosidades e recomendacoes honestas.',
                'seoTitle' => '',
                'seoDescription' => '',
                'seoKeywords' => [],
                'image' => '',
                'tags' => ['sobre', 'pedrita e thor', 'conteudo pet'],
                'draft' => false,
            ],
            'body' => "Pedrita e Thor sao os personagens centrais da marca. A Pedrita e uma cachorrinha preta, magra, mais nova e cheia de curiosidade. O Thor e branco, mais gordinho, carismatico e com aquele jeito de quem conquista qualquer pessoa.\n\nA ideia do projeto e compartilhar conteudo pet de forma leve, bonita e confiavel: cuidados, produtos que usamos ou recomendamos, curiosidades, noticias e achadinhos de afiliados.\n\nO site nasce pequeno, mas preparado para crescer como uma revista pet, uma vitrine de produtos e uma futura loja.\n",
        ],
        'aviso-de-afiliados' => [
            'data' => [
                'title' => 'Aviso de Afiliados',
                'linkLabel' => 'Afiliados',
                'description' => 'Transparencia sobre recomendacoes de produtos e links afiliados publicados no Pedrita & Thor.',
                'seoTitle' => '',
                'seoDescription' => '',
                'seoKeywords' => [],
                'image' => '',
                'tags' => ['afiliados', 'transparencia', 'produtos pet'],
                'draft' => false,
            ],
            'body' => "Alguns links publicados no Pedrita & Thor podem ser links de afiliado. Isso significa que, ao comprar por esses links, o projeto pode receber uma pequena comissao.\n\nEssa comissao nao gera custo extra para voce e ajuda a manter o conteudo do site.\n\nNosso objetivo e recomendar produtos com curadoria, bom senso e alinhamento com a rotina de quem cuida de cachorros.\n",
        ],
        'politica-de-privacidade' => [
            'data' => [
                'title' => 'Politica de Privacidade',
                'linkLabel' => 'Privacidade',
                'description' => 'Entenda como o site Pedrita & Thor trata informacoes, cookies, metricas e links de afiliados.',
                'seoTitle' => '',
                'seoDescription' => '',
                'seoKeywords' => [],
                'image' => '',
                'tags' => ['privacidade', 'cookies', 'politica'],
                'draft' => false,
            ],
            'body' => "O site Pedrita & Thor pode usar ferramentas de metricas, cookies e links de afiliados para entender o desempenho do conteudo e manter o projeto.\n\nAo clicar em links externos, voce sera direcionado para plataformas de terceiros, como marketplaces e redes sociais. Cada plataforma possui suas proprias politicas.\n\nEsta pagina deve ser revisada antes da publicacao definitiva para refletir exatamente as ferramentas usadas no site.\n",
        ],
    ];
}

function content_ensure_default_pages(): void
{
    $dir = content_dir('pages');
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    $seedMarker = $dir . '/.defaults-seeded';
    if (is_file($seedMarker)) {
        return;
    }

    $existingPages = glob($dir . '/*.md');
    if ($existingPages === false || empty($existingPages)) {
        foreach (content_default_pages() as $slug => $page) {
            content_save('pages', $slug, $page['data'], $page['body']);
        }
    }

    file_put_contents($seedMarker, date('c') . "\n");
}

/**
 * Faz parse pragmatico de um bloco de frontmatter YAML (entre as linhas ---).
 * Suporta:
 *  - chave: valor (string simples, com ou sem aspas)
 *  - chave: "valor entre aspas"
 *  - chave: true / false
 *  - chave: 2026-06-15 (data ISO, sem aspas)
 *  - chave: [a, b, c]  (lista inline)
 *  - chave:\n  - item\n  - item2  (lista multilinha)
 */
function content_parse_frontmatter(string $raw): array
{
    $lines = preg_split('/\r\n|\r|\n/', $raw);
    $data = [];
    $currentListKey = null;

    foreach ($lines as $line) {
        if (trim($line) === '') {
            continue;
        }

        // Item de lista multilinha: "  - valor"
        if ($currentListKey !== null && preg_match('/^\s*-\s*(.*)$/', $line, $m)) {
            $data[$currentListKey][] = content_unquote(trim($m[1]));
            continue;
        }

        // Nova chave encontrada: encerra qualquer lista multilinha em andamento
        if (preg_match('/^([A-Za-z0-9_]+):\s*(.*)$/', $line, $m)) {
            $key = $m[1];
            $value = trim($m[2]);
            $currentListKey = null;

            if ($value === '') {
                // Pode ser inicio de lista multilinha; sera populada nas linhas seguintes.
                $data[$key] = [];
                $currentListKey = $key;
                continue;
            }

            if (preg_match('/^\[(.*)\]$/', $value, $lm)) {
                // Lista inline: [a, b, c]
                $inner = trim($lm[1]);
                if ($inner === '') {
                    $data[$key] = [];
                } else {
                    $parts = array_map('trim', explode(',', $inner));
                    $data[$key] = array_map('content_unquote', $parts);
                }
                continue;
            }

            if ($value === 'true' || $value === 'false') {
                $data[$key] = ($value === 'true');
                continue;
            }

            $data[$key] = content_unquote($value);
            continue;
        }
    }

    return $data;
}

function content_unquote(string $value): string
{
    $value = trim($value);
    if (strlen($value) >= 2) {
        $first = $value[0];
        $last = $value[strlen($value) - 1];
        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            return substr($value, 1, -1);
        }
    }
    return $value;
}

/**
 * Le um arquivo .md completo (frontmatter + corpo) e retorna ['data' => [...], 'body' => '...'].
 */
function content_parse_file(string $path): ?array
{
    if (!is_file($path)) {
        return null;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        return null;
    }

    // Normaliza quebras de linha
    $raw = str_replace("\r\n", "\n", $raw);

    if (!preg_match('/^---\n(.*?)\n---\n?(.*)$/s', $raw, $m)) {
        // Sem frontmatter valido: trata tudo como corpo.
        return ['data' => [], 'body' => trim($raw)];
    }

    $frontmatterRaw = $m[1];
    $body = isset($m[2]) ? ltrim($m[2], "\n") : '';

    $data = content_parse_frontmatter($frontmatterRaw);

    return ['data' => $data, 'body' => rtrim($body) . "\n"];
}

/**
 * Converte um valor PHP para sua representacao YAML de uma linha, conforme o tipo do schema.
 */
function content_format_value(string $fieldType, $value): string
{
    switch ($fieldType) {
        case 'bool':
            return $value ? 'true' : 'false';
        case 'date':
            // Aceita string ISO ou objeto DateTime; normaliza para YYYY-MM-DD
            if ($value instanceof DateTime) {
                return $value->format('Y-m-d');
            }
            $value = (string) $value;
            // Mantem apenas a parte da data se vier com hora
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $value, $m)) {
                return $m[1];
            }
            return $value;
        case 'string':
        case 'string_optional':
            $value = (string) $value;
            // Usa aspas duplas sempre que houver caracteres especiais ou ':'/'#'
            $needsQuotes = $value === '' || preg_match('/[:#"\'\[\]{}]/', $value) || $value !== trim($value);
            if ($needsQuotes) {
                return '"' . str_replace('"', '\\"', $value) . '"';
            }
            return $value;
        default:
            return (string) $value;
    }
}

/**
 * Regenera o bloco de frontmatter na ordem estavel definida pelo schema.
 */
function content_build_frontmatter(string $type, array $data): string
{
    $schema = content_schema($type);
    $lines = [];

    foreach ($schema as $key => $fieldType) {
        if ($fieldType === 'list') {
            $list = isset($data[$key]) && is_array($data[$key]) ? $data[$key] : [];
            if (empty($list)) {
                $lines[] = $key . ': []';
            } else {
                $quoted = array_map(function ($item) {
                    return content_format_value('string', $item);
                }, $list);
                $lines[] = $key . ': [' . implode(', ', $quoted) . ']';
            }
            continue;
        }

        if ($fieldType === 'string_optional') {
            $value = $data[$key] ?? '';
            if ($value === '' || $value === null) {
                // Omite campos opcionais vazios, igual ao schema Zod (.optional()).
                continue;
            }
            $lines[] = $key . ': ' . content_format_value('string', $value);
            continue;
        }

        $value = $data[$key] ?? ($fieldType === 'bool' ? false : '');
        $lines[] = $key . ': ' . content_format_value($fieldType, $value);
    }

    return "---\n" . implode("\n", $lines) . "\n---\n";
}

/**
 * Lista todos os itens de um tipo de conteudo (slug + dados de frontmatter, sem o corpo).
 */
function content_list(string $type): array
{
    if ($type === 'pages') {
        content_ensure_default_pages();
    }

    $dir = content_dir($type);
    $items = [];

    if (!is_dir($dir)) {
        return $items;
    }

    $files = glob($dir . '/*.md');
    if ($files === false) {
        return $items;
    }

    foreach ($files as $file) {
        $slug = pathinfo($file, PATHINFO_FILENAME);
        $parsed = content_parse_file($file);
        if ($parsed === null) {
            continue;
        }
        $items[] = [
            'slug' => $slug,
            'data' => $parsed['data'],
        ];
    }

    return $items;
}

/**
 * Carrega um item especifico (frontmatter + corpo).
 */
function content_load(string $type, string $slug): ?array
{
    if ($type === 'pages') {
        content_ensure_default_pages();
    }

    $slug = content_sanitize_slug($slug);
    $dir = content_dir($type);
    $path = $dir . '/' . $slug . '.md';

    $parsed = content_parse_file($path);
    if ($parsed === null) {
        return null;
    }

    return [
        'slug' => $slug,
        'data' => $parsed['data'],
        'body' => $parsed['body'],
    ];
}

/**
 * Salva (cria ou atualiza) um item, regenerando o frontmatter de forma deterministica.
 */
function content_save(string $type, string $slug, array $data, string $body): void
{
    $slug = content_sanitize_slug($slug);
    $dir = content_dir($type);

    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    $frontmatter = content_build_frontmatter($type, $data);
    $body = rtrim($body) . "\n";

    $path = $dir . '/' . $slug . '.md';
    file_put_contents($path, $frontmatter . "\n" . $body);
}

/**
 * Remove um item de conteudo.
 */
function content_delete(string $type, string $slug): void
{
    $slug = content_sanitize_slug($slug);
    $dir = content_dir($type);
    $path = $dir . '/' . $slug . '.md';

    if (is_file($path)) {
        unlink($path);
    }

    if ($type === 'pages') {
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $seedMarker = $dir . '/.defaults-seeded';
        if (!is_file($seedMarker)) {
            file_put_contents($seedMarker, date('c') . "\n");
        }
    }
}

function content_sanitize_slug(string $slug): string
{
    // Protege contra path traversal: mantem so caracteres validos de slug.
    $slug = trim($slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));
    return (string) $slug;
}

/**
 * Gera um slug a partir de um titulo: minusculas, sem acentos, nao-alfanumerico -> hifen,
 * hifens repetidos colapsados, hifens nas pontas removidos.
 */
function slugify(string $title): string
{
    $title = trim($title);

    // Remove acentos via transliteracao.
    $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT', $title);
    if ($transliterated !== false) {
        $title = $transliterated;
    }

    $title = strtolower($title);
    $title = preg_replace('/[^a-z0-9]+/', '-', $title);
    $title = preg_replace('/-+/', '-', (string) $title);
    $title = trim((string) $title, '-');

    if ($title === '') {
        $title = 'item';
    }

    return $title;
}

/**
 * Garante que o slug seja unico dentro do diretorio do tipo de conteudo,
 * acrescentando -2, -3, ... se necessario.
 */
function content_unique_slug(string $type, string $baseSlug): string
{
    $dir = content_dir($type);
    $slug = $baseSlug;
    $counter = 2;

    while (is_file($dir . '/' . $slug . '.md')) {
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }

    return $slug;
}
