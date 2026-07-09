<?php
declare(strict_types=1);

define('SITE_SETTINGS_FILE', __DIR__ . '/../_data/site-settings.json');

function site_settings_defaults(): array
{
    return [
        'site_description' => 'Conteudo pet com dicas de cuidados, curiosidades, noticias leves e produtos recomendados para quem ama cachorros.',

        'home_hero_eyebrow' => 'Conteudo pet com personalidade',
        'home_hero_title' => 'Pedrita & Thor',
        'home_hero_text' => 'Dicas de cuidados, curiosidades leves, noticias do mundo pet e produtos que fazem sentido para a rotina com cachorros.',
        'home_hero_primary_label' => 'Ver produtos',
        'home_hero_primary_url' => '/produtos-que-amamos/',
        'home_hero_secondary_label' => 'Ler artigos',
        'home_hero_secondary_url' => '/cuidados-com-seu-animal/',
        'home_hero_image' => '/uploads/imagem_central_site_Pedrita_Thor.png',
        'home_hero_image_alt' => 'Pedrita e Thor em cards com suas fotos e descricoes',

        'home_products_eyebrow' => 'Afiliados com curadoria',
        'home_products_title' => 'Produtos que amamos',
        'home_products_text' => 'Uma vitrine limpa para achadinhos, acessorios, brinquedos e itens de cuidado.',
        'home_products_all_label' => 'Ver todos',
        'home_products_empty_title' => 'Produtos em preparacao',
        'home_products_empty_text' => 'Cadastre produtos no admin para eles aparecerem aqui.',

        'home_articles_eyebrow' => 'Conteudo recente',
        'home_articles_title' => 'Artigos novos',
        'home_articles_text' => 'Cuidados, curiosidades e novidades para quem quer entender melhor a vida com pets.',
        'home_articles_empty_title' => 'Artigos em preparacao',
        'home_articles_empty_text' => 'Cadastre artigos no admin para eles aparecerem aqui.',

        'home_social_eyebrow' => 'Acompanhe a rotina',
        'home_social_title' => 'Siga Pedrita & Thor nas redes',
        'home_social_text' => 'Videos curtos, achadinhos e momentos do dia a dia para deixar o conteudo mais perto de quem ama cachorro.',

        'products_page_eyebrow' => 'Vitrine afiliada',
        'products_page_title' => 'Produtos que amamos',
        'products_page_description' => 'Uma selecao de produtos para cachorro, casa com pet, passeio, higiene, brincadeiras e bem-estar.',
        'products_page_meta_description' => 'Vitrine de produtos recomendados pela Pedrita & Thor, com links de afiliado da Shopee, TikTok Shop, Mercado Livre e outras plataformas.',
        'products_page_empty_title' => 'Nenhum produto publicado',
        'products_page_empty_text' => 'Os produtos publicados no admin aparecem aqui automaticamente.',
        'product_card_details_label' => 'Detalhes',
        'product_card_offer_label' => 'Ver oferta',
        'product_detail_button_label' => 'Ver produto',
        'product_detail_affiliate_note' => 'Este link pode gerar comissao para Pedrita & Thor, sem custo extra para voce.',
        'product_detail_no_image_text' => 'Adicione uma imagem do produto pelo admin.',

        'category_cuidados_eyebrow' => 'Guias e rotina',
        'category_cuidados_title' => 'Cuidados com seu animal',
        'category_cuidados_description' => 'Guias simples sobre saude, higiene, alimentacao, rotina e bem-estar para cachorros.',

        'category_curiosidades_eyebrow' => 'Leve e compartilhavel',
        'category_curiosidades_title' => 'Curiosidades',
        'category_curiosidades_description' => 'Conteudos leves, informativos e compartilhaveis sobre o mundo dos pets.',

        'category_noticias_eyebrow' => 'Mundo pet',
        'category_noticias_title' => 'Noticias',
        'category_noticias_description' => 'Novidades do universo pet, tendencias, alertas e assuntos que valem acompanhar.',

        'category_empty_title' => 'Nenhum artigo publicado',
        'category_empty_text' => 'Os artigos publicados nessa categoria aparecem aqui automaticamente.',
    ];
}

function site_settings_fields(): array
{
    return [
        'Geral' => [
            'site_description' => ['label' => 'Descricao padrao do site', 'type' => 'textarea'],
        ],
        'Home - topo' => [
            'home_hero_eyebrow' => ['label' => 'Texto pequeno acima do titulo'],
            'home_hero_title' => ['label' => 'Titulo principal'],
            'home_hero_text' => ['label' => 'Texto principal', 'type' => 'textarea'],
            'home_hero_primary_label' => ['label' => 'Botao principal'],
            'home_hero_primary_url' => ['label' => 'Link do botao principal'],
            'home_hero_secondary_label' => ['label' => 'Botao secundario'],
            'home_hero_secondary_url' => ['label' => 'Link do botao secundario'],
            'home_hero_image' => ['label' => 'Imagem principal', 'type' => 'image'],
            'home_hero_image_alt' => ['label' => 'Descricao da imagem'],
        ],
        'Home - produtos' => [
            'home_products_eyebrow' => ['label' => 'Texto pequeno'],
            'home_products_title' => ['label' => 'Titulo'],
            'home_products_text' => ['label' => 'Descricao', 'type' => 'textarea'],
            'home_products_all_label' => ['label' => 'Texto do link Ver todos'],
            'home_products_empty_title' => ['label' => 'Titulo quando nao houver produtos'],
            'home_products_empty_text' => ['label' => 'Texto quando nao houver produtos', 'type' => 'textarea'],
        ],
        'Home - artigos' => [
            'home_articles_eyebrow' => ['label' => 'Texto pequeno'],
            'home_articles_title' => ['label' => 'Titulo'],
            'home_articles_text' => ['label' => 'Descricao', 'type' => 'textarea'],
            'home_articles_empty_title' => ['label' => 'Titulo quando nao houver artigos'],
            'home_articles_empty_text' => ['label' => 'Texto quando nao houver artigos', 'type' => 'textarea'],
        ],
        'Home - redes sociais' => [
            'home_social_eyebrow' => ['label' => 'Texto pequeno'],
            'home_social_title' => ['label' => 'Titulo'],
            'home_social_text' => ['label' => 'Descricao', 'type' => 'textarea'],
        ],
        'Pagina de produtos' => [
            'products_page_eyebrow' => ['label' => 'Texto pequeno'],
            'products_page_title' => ['label' => 'Titulo'],
            'products_page_description' => ['label' => 'Descricao visivel', 'type' => 'textarea'],
            'products_page_meta_description' => ['label' => 'Descricao para SEO', 'type' => 'textarea'],
            'products_page_empty_title' => ['label' => 'Titulo quando nao houver produtos'],
            'products_page_empty_text' => ['label' => 'Texto quando nao houver produtos', 'type' => 'textarea'],
            'product_card_details_label' => ['label' => 'Botao de detalhes nos cards'],
            'product_card_offer_label' => ['label' => 'Botao de oferta nos cards'],
            'product_detail_button_label' => ['label' => 'Botao no detalhe do produto'],
            'product_detail_affiliate_note' => ['label' => 'Aviso de afiliado no detalhe', 'type' => 'textarea'],
            'product_detail_no_image_text' => ['label' => 'Texto quando produto nao tem imagem', 'type' => 'textarea'],
        ],
        'Categoria - Cuidados' => [
            'category_cuidados_eyebrow' => ['label' => 'Texto pequeno'],
            'category_cuidados_title' => ['label' => 'Titulo'],
            'category_cuidados_description' => ['label' => 'Descricao', 'type' => 'textarea'],
        ],
        'Categoria - Curiosidades' => [
            'category_curiosidades_eyebrow' => ['label' => 'Texto pequeno'],
            'category_curiosidades_title' => ['label' => 'Titulo'],
            'category_curiosidades_description' => ['label' => 'Descricao', 'type' => 'textarea'],
        ],
        'Categoria - Noticias' => [
            'category_noticias_eyebrow' => ['label' => 'Texto pequeno'],
            'category_noticias_title' => ['label' => 'Titulo'],
            'category_noticias_description' => ['label' => 'Descricao', 'type' => 'textarea'],
        ],
        'Categorias - vazio' => [
            'category_empty_title' => ['label' => 'Titulo quando nao houver artigos'],
            'category_empty_text' => ['label' => 'Texto quando nao houver artigos', 'type' => 'textarea'],
        ],
    ];
}

function site_settings(): array
{
    static $settings = null;

    if ($settings !== null) {
        return $settings;
    }

    $settings = site_settings_defaults();

    if (!is_file(SITE_SETTINGS_FILE)) {
        return $settings;
    }

    $raw = file_get_contents(SITE_SETTINGS_FILE);
    $saved = json_decode($raw === false ? '' : $raw, true);

    if (!is_array($saved)) {
        return $settings;
    }

    foreach ($settings as $key => $defaultValue) {
        if (isset($saved[$key]) && is_string($saved[$key]) && trim($saved[$key]) !== '') {
            $settings[$key] = $saved[$key];
        }
    }

    return $settings;
}

function site_setting(string $key): string
{
    $settings = site_settings();
    return (string) ($settings[$key] ?? '');
}

function site_settings_save(array $values): void
{
    $defaults = site_settings_defaults();
    $data = [];

    foreach ($defaults as $key => $defaultValue) {
        $value = isset($values[$key]) ? trim((string) $values[$key]) : '';
        $data[$key] = $value !== '' ? $value : $defaultValue;
    }

    $dir = dirname(SITE_SETTINGS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    file_put_contents(SITE_SETTINGS_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    chmod(SITE_SETTINGS_FILE, 0640);
}
