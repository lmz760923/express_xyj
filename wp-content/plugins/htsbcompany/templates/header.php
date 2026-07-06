<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--link rel="profile" href="https://gmpg.org/xfn/11"-->
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div class="site-wrapper">

        <header class="site-header">
            <div class="container">
                <div class="header-content">
                    <div class="logo-area">
                        <?php if (has_custom_logo()): ?>
                            <?php the_custom_logo(); ?>
                        <?php else: ?>
                            <h1 class="site-title">
                                <a href="<?php echo esc_url(home_url('/')); ?>">
                                    <!--?php bloginfo('name'); ?-->
                                    <i class="fa fa-cogs"></i>
                                </a>
                            </h1>
                            <!--p class="site-description"><?php bloginfo('description'); ?></p-->
                        <?php endif; ?>
                    </div>

                    <nav class="main-navigation">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'primary-menu',
                            'menu_class' => 'primary-menu',
                            'container' => false,
                            'fallback_cb' => false
                        ));

                        // 如果没有设置菜单，显示默认菜单
                        /*
    if (!has_nav_menu('primary-menu')) {
        echo '<ul class="primary-menu">';
        echo '<li><a href="' . home_url('/') . '">首页</a></li>';
        echo '<li><a href="' . home_url('/products') . '">产品</a></li>';
        echo '<li><a href="' . home_url('/news') . '">新闻动态</a></li>';
        echo '<li><a href="' . home_url('/about') . '">关于我们</a></li>';
        echo '</ul>';
    }
    */
                        ?>
                    </nav>

                    <div class="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
            </div>
        </header>

        <div class="mobile-menu-container">
            <?php wp_nav_menu(array(
                'theme_location' => 'primary-menu',
                'menu_class' => 'mobile-menu',
                'container' => false
            )); ?>
        </div>