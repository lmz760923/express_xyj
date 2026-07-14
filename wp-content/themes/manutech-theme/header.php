<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
    <div class="container header-inner">
        <div class="site-branding">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <span class="brand-cn">智造装备科技</span>
                <span class="brand-en">MANUTECH INDUSTRIAL</span>
            </a>
        </div>
        <nav aria-label="主导航">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'primary-menu',
                'fallback_cb' => 'manutech_fallback_menu',
            ]);
            ?>
        </nav>
    </div>
</header>
