<?php

if (!defined('ABSPATH')) {
    exit;
}

function manutech_theme_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('menus');

    register_nav_menus([
        'primary' => __('主导航', 'manutech-theme'),
    ]);
}
add_action('after_setup_theme', 'manutech_theme_setup');

function manutech_enqueue_assets(): void
{
    wp_enqueue_style(
        'manutech-style',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get('Version')
    );

    wp_enqueue_script(
        'manutech-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        wp_get_theme()->get('Version'),
        true
    );

}
add_action('wp_enqueue_scripts', 'manutech_enqueue_assets');

function manutech_company_profile(): array
{
    $profile = [
        'company_name' => '智造装备科技有限公司',
        'company_intro' => '聚焦高端设备研发、非标产线集成与智能运维服务，为制造企业提供可持续提升的产能方案。',
        'phone' => '400-880-2026',
        'email' => 'sales@example.com',
        'hq_address' => '江苏省苏州市工业园区星港街 88 号',
        'south_address' => '广东省东莞市松山湖科技产业园',
        'service_hours' => '周一至周五 08:30 - 18:00',
        'emergency_support' => '7x24 小时热线',
    ];

    return apply_filters('manutech_company_profile', $profile);
}

function manutech_get_contact_notice(): array
{
    $status = isset($_GET['inquiry']) ? sanitize_key((string) wp_unslash($_GET['inquiry'])) : '';
    $map = [
        'success' => ['type' => 'success', 'text' => '已收到你的需求，我们将在 1 个工作日内联系你。'],
        'invalid' => ['type' => 'error', 'text' => '提交失败：请检查必填项并重试。'],
        'mailfail' => ['type' => 'warning', 'text' => '需求已记录，但邮件发送失败，请电话联系以加快处理。'],
    ];

    if (!isset($map[$status])) {
        return [];
    }

    return $map[$status];
}

function manutech_get_page_url_by_slug(string $slug): string
{
    $page = get_page_by_path($slug);
    if ($page instanceof WP_Post) {
        return (string) get_permalink($page);
    }

    return home_url('/');
}

function manutech_ensure_site_pages(): void
{
    if (get_option('manutech_pages_ready') === '1') {
        return;
    }

    $definitions = [
        [
            'title' => '首页',
            'slug' => 'home',
            'template' => 'front-page.php',
        ],
        [
            'title' => '产品中心',
            'slug' => 'products',
            'template' => 'page-products.php',
        ],
        [
            'title' => '解决方案',
            'slug' => 'solutions',
            'template' => 'page-solutions.php',
        ],
        [
            'title' => '关于我们',
            'slug' => 'about',
            'template' => 'page-about.php',
        ],
        [
            'title' => '联系我们',
            'slug' => 'contact',
            'template' => 'page-contact.php',
        ],
    ];

    $page_ids = [];

    foreach ($definitions as $def) {
        $existing = get_page_by_path($def['slug']);

        if ($existing instanceof WP_Post) {
            $page_id = (int) $existing->ID;
        } else {
            $page_id = wp_insert_post([
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => $def['title'],
                'post_name' => $def['slug'],
            ]);

            if (!is_int($page_id) || $page_id <= 0) {
                continue;
            }
        }

        if (!empty($def['template'])) {
            update_post_meta($page_id, '_wp_page_template', $def['template']);
        }

        $page_ids[$def['slug']] = $page_id;
    }

    if (!empty($page_ids['home'])) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) $page_ids['home']);
    }

    update_option('manutech_pages_ready', '1');
}
add_action('init', 'manutech_ensure_site_pages');

function manutech_is_local_env(): bool
{
    $host = (string) wp_parse_url(home_url('/'), PHP_URL_HOST);
    $local_hosts = ['127.0.0.1', 'localhost'];

    return PHP_SAPI === 'cli-server' || in_array($host, $local_hosts, true);
}

function manutech_log_inquiry(array $data, string $status): void
{
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
        return;
    }

    $log_file = trailingslashit($uploads['basedir']) . 'inquiries-local.log';
    $entry = [
        'time' => current_time('mysql'),
        'status' => $status,
        'data' => $data,
    ];

    $line = wp_json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($line)) {
        return;
    }

    file_put_contents($log_file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function manutech_fallback_menu(): void
{
    echo '<ul class="primary-menu">';
    echo '<li><a href="' . esc_url(manutech_get_page_url_by_slug('home')) . '">首页</a></li>';
    echo '<li><a href="' . esc_url(manutech_get_page_url_by_slug('products')) . '">产品中心</a></li>';
    echo '<li><a href="' . esc_url(manutech_get_page_url_by_slug('solutions')) . '">解决方案</a></li>';
    echo '<li><a href="' . esc_url(manutech_get_page_url_by_slug('about')) . '">关于我们</a></li>';
    echo '<li><a href="' . esc_url(manutech_get_page_url_by_slug('contact')) . '">联系我们</a></li>';
    echo '</ul>';
}
