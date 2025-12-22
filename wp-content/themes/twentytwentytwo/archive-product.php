<?php get_header(); ?>

<div class="container">
    <div class="content-wrapper">
        <main class="main-content">
            <header class="archive-header">
                <h1 class="archive-title">产品中心</h1>
                <div class="archive-description">
                    <p>浏览我们的所有产品</p>
                </div>
            </header>
            
            <!-- 产品分类 -->
            <?php
            $product_categories = get_terms(array(
                'taxonomy' => 'product_category',
                'hide_empty' => true,
                'parent' => 0
            ));
            
            if ($product_categories && !is_wp_error($product_categories)):
            ?>
                <div class="product-categories-list">
                    <h3>产品分类</h3>
                    <ul class="categories-nav">
                        <li class="active"><a href="<?php echo get_post_type_archive_link('product'); ?>">全部产品</a></li>
                        <?php foreach ($product_categories as $category): ?>
                            <li>
                                <a href="<?php echo get_term_link($category); ?>">
                                    <?php echo $category->name; ?>
                                    <span class="count">(<?php echo $category->count; ?>)</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- 产品列表 -->
            <?php if (have_posts()): ?>
                <div class="products-archive">
                    <div class="products-grid">
                        <?php while (have_posts()): the_post(); ?>
                            <article class="product-archive-item">
                                <a href="<?php the_permalink(); ?>" class="product-link">
                                    <?php if (has_post_thumbnail()): ?>
                                        <div class="product-thumbnail">
                                            <?php the_post_thumbnail('medium'); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h3 class="product-title"><?php the_title(); ?></h3>
                                    
                                    <div class="product-excerpt">
                                        <?php the_excerpt(); ?>
                                    </div>
                                    
                                    <span class="view-details">查看详情</span>
                                </a>
                            </article>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="pagination">
                        <?php the_posts_pagination(array(
                            'mid_size' => 2,
                            'prev_text' => '? 上一页',
                            'next_text' => '下一页 ?'
                        )); ?>
                    </div>
                </div>
            <?php else: ?>
                <p>暂无产品。</p>
            <?php endif; ?>
        </main>
        
        <?php get_sidebar(); ?>
    </div>
</div>

<?php get_footer(); ?>
