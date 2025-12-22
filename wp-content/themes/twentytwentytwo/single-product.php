<?php get_header(); ?>

<div class="container">
    <div class="content-wrapper">
        <main class="main-content">
            <?php while (have_posts()): the_post(); ?>
                <article class="product-detail">
                    <header class="product-header">
                        <h1 class="product-title"><?php the_title(); ?></h1>
                        <div class="product-meta">
                            <span class="post-date"><?php echo get_the_date(); ?></span>
                            <span class="product-categories">
                                <?php
                                $categories = get_the_terms(get_the_ID(), 'product_category');
                                if ($categories):
                                    foreach ($categories as $category):
                                ?>
                                    <a href="<?php echo get_term_link($category); ?>">
                                        <?php echo $category->name; ?>
                                    </a>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </span>
                        </div>
                    </header>
                    
                    <div class="product-content">
                        <?php if (has_post_thumbnail()): ?>
                            <div class="product-featured-image">
                                <?php the_post_thumbnail('large'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-description">
                            <?php the_content(); ?>
                        </div>
                        
                        <!-- 产品规格 -->
                        <?php if (get_field('specifications') || get_field('features')): ?>
                            <div class="product-specs">
                                <h3>产品规格</h3>
                                
                                <?php if (get_field('features')): ?>
                                    <div class="product-features">
                                        <h4>产品特点</h4>
                                        <?php echo get_field('features'); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (get_field('specifications')): ?>
                                    <div class="specifications">
                                        <h4>技术参数</h4>
                                        <?php echo get_field('specifications'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- 相关产品 -->
                    <?php
                    $related_products = new WP_Query(array(
                        'post_type' => 'product',
                        'posts_per_page' => 3,
                        'post__not_in' => array(get_the_ID()),
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'product_category',
                                'field' => 'term_id',
                                'terms' => wp_get_post_terms(get_the_ID(), 'product_category', array('fields' => 'ids')),
                                'operator' => 'IN'
                            )
                        )
                    ));
                    
                    if ($related_products->have_posts()):
                    ?>
                        <div class="related-products">
                            <h3>相关产品</h3>
                            <div class="related-grid">
                                <?php while ($related_products->have_posts()): $related_products->the_post(); ?>
                                    <div class="related-item">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php if (has_post_thumbnail()): ?>
                                                <?php the_post_thumbnail('thumbnail'); ?>
                                            <?php endif; ?>
                                            <h4><?php the_title(); ?></h4>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php
                        wp_reset_postdata();
                    endif;
                    ?>
                </article>
            <?php endwhile; ?>
        </main>
        
        <?php get_sidebar(); ?>
    </div>
</div>

<?php get_footer(); ?>
