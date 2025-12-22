<?php
/*
Template Name: 产品列表页面
*/
get_header();
?>

<div class="container">

    <div class="content-wrapper">
        <main class="main-content">
            <header class="page-header">
                <!--h1><?php the_title(); ?></h1-->
                
            </header>
            
            <!-- 产品分类过滤 -->
            <div class="product-filters">
                <!--h3>产品分类</h3-->
                <ul class="category-filter">
                    <li><a href="#" data-category="all" class="active">全部产品</a></li>
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'product_cat',
                        'hide_empty' => true
                    ));
                    
                    foreach ($categories as $category):
                    ?>
                        <li><a href="#" data-category="<?php echo $category->slug; ?>">
                            <?php echo $category->name; ?>
                        </a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- 产品列表 -->
            <div class="products-list" id="products-container">
                <?php
                $products = new WP_Query(array(
                    'post_type' => 'product',
                    'posts_per_page' => 3,
                    'paged' => get_query_var('paged') ? get_query_var('paged') : 1
                ));
                
                if ($products->have_posts()):
                ?>
                    <div class="products-grid">
                        <?php while ($products->have_posts()): $products->the_post(); ?>
                            <div class="product-item" 
                                 data-categories="<?php 
                                    $cats = get_the_terms(get_the_ID(), 'product_category');
                                    if ($cats) {
                                        $cat_slugs = array();
                                        foreach ($cats as $cat) {
                                            $cat_slugs[] = $cat->slug;
                                        }
                                        echo implode(' ', $cat_slugs);
                                    }
                                 ?>">
                                <?php if (has_post_thumbnail()): ?>
                                    <div class="product-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('medium'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <h3 class="product-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                
                                <div class="product-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                                
                                <div class="product-categories">
                                    <?php
                                    $categories = get_the_terms(get_the_ID(), 'product_category');
                                    if ($categories):
                                        foreach ($categories as $category):
                                    ?>
                                        <span class="product-category"><?php echo $category->name; ?></span>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
								<a href="<?php the_permalink(); ?>" class="btn-view">查看详情</a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="pagination ajax-pagination">
					
                        <?php
                        echo paginate_links(array(
                            'total' => $products->max_num_pages,
                            'current' => max(1, get_query_var('paged')),
                            'prev_text' => '« 上一页',
                            'next_text' => '下一页 »',
                            'type' => 'list',
                        ));
                        ?>
                    </div>
                <?php
                    
                    wp_reset_postdata();
                else:
                ?>
                    <p>暂无产品。</p>
                <?php endif; ?>
            </div>
        </main>
        <?php get_sidebar(); ?>
        
    </div>
</div>
<?php get_footer(); ?>

