<?php get_header(); ?>

<div class="container">
    <div class="content-wrapper">
        <main class="main-content">
            <article class="error-404">
                <header class="page-header">
                    <h1>404 - 页面未找到</h1>
                </header>
                
                <div class="page-content">
                    <p>抱歉，您访问的页面不存在或已被移动。</p>
                    
                    <div class="search-form-404">
                        <h3>搜索网站内容</h3>
                        <?php get_search_form(); ?>
                    </div>
                    
                    <div class="suggestions">
                        <h3>您可能想访问：</h3>
                        <ul>
                            <li><a href="<?php echo home_url(); ?>">返回首页</a></li>
                            <li><a href="<?php echo get_post_type_archive_link('product'); ?>">产品中心</a></li>
                            <li><a href="<?php echo get_permalink(get_option('page_for_posts')); ?>">新闻动态</a></li>
                            <li><a href="<?php echo home_url('/about'); ?>">关于我们</a></li>
                        </ul>
                    </div>
                </div>
            </article>
        </main>
        
        <?php get_sidebar(); ?>
    </div>
</div>

<?php get_footer(); ?>
