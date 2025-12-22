<aside class="sidebar">
    <?php if (is_active_sidebar('main-sidebar')): ?>
        <?php dynamic_sidebar('main-sidebar'); ?>
    <?php else: ?>
        <!-- 默认侧边栏内容 -->
        <div class="widget">
            <h3 class="widget-title">关于我们</h3>
            <p>欢迎访问我们的企业网站。我们专注于提供高质量的产品和服务。</p>
        </div>
        
        <div class="widget">
            <h3 class="widget-title">最新产品</h3>
            <ul>
                <?php
                $products = new WP_Query(array(
                    'post_type' => 'product',
                    'posts_per_page' => 5
                ));
                
                if ($products->have_posts()):
                    while ($products->have_posts()): $products->the_post();
                ?>
                    <li>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </li>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else:
                ?>
                    <li>暂无产品</li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="widget">
            <h3 class="widget-title">新闻动态</h3>
            <ul>
                <?php
                $news = new WP_Query(array(
                    'posts_per_page' => 5
                ));
                
                if ($news->have_posts()):
                    while ($news->have_posts()): $news->the_post();
                ?>
                    <li>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </li>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else:
                ?>
                    <li>暂无新闻</li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>
</aside>
