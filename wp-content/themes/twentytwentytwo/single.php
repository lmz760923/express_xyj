<?php
/**
 * 单篇文章模板
 */
get_header(); ?>

<div class="container">
    <div class="content-wrapper">
        <main class="main-content" id="single-post">
            
            <?php
            // 主循环
            if (have_posts()) :
                while (have_posts()) : the_post();
            ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class('single-post'); ?>>
                
                <!-- 文章标题 -->
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                    
                    <div class="entry-meta">
                        <span class="posted-on">
                            <i class="far fa-calendar"></i>
                            <time datetime="<?php echo get_the_date('c'); ?>">
                                <?php echo get_the_date(); ?>
                            </time>
                        </span>
                        
                        <span class="byline">
                            <i class="far fa-user"></i>
                            <span class="author vcard">
                                <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                                    <?php the_author(); ?>
                                </a>
                            </span>
                        </span>
                        
                        <span class="cat-links">
                            <i class="far fa-folder"></i>
                            <?php the_category(', '); ?>
                        </span>
                        
                        <?php if (comments_open()): ?>
                        <span class="comments-link">
                            <i class="far fa-comment"></i>
                            <?php comments_popup_link('0 评论', '1 评论', '% 评论'); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </header>
                
                <!-- 文章特色图片 -->
                <?php if (has_post_thumbnail()): ?>
                <div class="post-thumbnail">
                    <?php the_post_thumbnail('large', array('class' => 'featured-image')); ?>
                </div>
                <?php endif; ?>
                
                <!-- 文章内容 - 关键部分 -->
                <div class="entry-content">
                    <?php 
                    // 显示完整文章内容
                    the_content();
                    
                    // 分页（如果文章使用了 <!--nextpage--> 标签）
                    wp_link_pages(array(
                        'before' => '<div class="page-links"><span class="page-links-title">' . __('页面:') . '</span>',
                        'after' => '</div>',
                        'link_before' => '<span>',
                        'link_after' => '</span>',
                        'pagelink' => '<span class="screen-reader-text">' . __('第') . ' </span>%',
                        'separator' => '<span class="screen-reader-text">, </span>',
                    ));
                    ?>
                </div>
                
                <!-- 文章标签 -->
                <?php if (has_tag()): ?>
                <footer class="entry-footer">
                    <div class="post-tags">
                        <span class="tags-title"><i class="fas fa-tags"></i> 标签：</span>
                        <?php the_tags('', ', ', ''); ?>
                    </div>
                </footer>
                <?php endif; ?>
                
                <!-- 作者信息 -->
                <div class="author-bio">
                    <div class="author-avatar">
                        <?php echo get_avatar(get_the_author_meta('ID'), 80); ?>
                    </div>
                    <div class="author-info">
                        <h4>关于作者：<?php the_author(); ?></h4>
                        <p><?php echo get_the_author_meta('description'); ?></p>
                        <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>" class="author-link">
                            查看该作者的所有文章 <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <!-- 上一篇/下一篇文章 -->
                <nav class="post-navigation">
                    <div class="nav-previous">
                        <?php
                        $prev_post = get_previous_post();
                        if ($prev_post):
                        ?>
                        <div class="nav-label">上一篇</div>
                        <a href="<?php echo get_permalink($prev_post->ID); ?>">
                            <h3><?php echo esc_html($prev_post->post_title); ?></h3>
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="nav-next">
                        <?php
                        $next_post = get_next_post();
                        if ($next_post):
                        ?>
                        <div class="nav-label">下一篇</div>
                        <a href="<?php echo get_permalink($next_post->ID); ?>">
                            <h3><?php echo esc_html($next_post->post_title); ?></h3>
                        </a>
                        <?php endif; ?>
                    </div>
                </nav>
                
                <!-- 相关文章 -->
                <?php
                $related_posts = get_posts(array(
                    'category__in' => wp_get_post_categories(get_the_ID()),
                    'numberposts' => 3,
                    'post__not_in' => array(get_the_ID()),
                    'orderby' => 'rand'
                ));
                
                if ($related_posts):
                ?>
                <div class="related-posts">
                    <h3 class="related-title">相关文章</h3>
                    <div class="related-grid">
                        <?php foreach ($related_posts as $post): setup_postdata($post); ?>
                        <article class="related-post">
                            <?php if (has_post_thumbnail()): ?>
                            <a href="<?php the_permalink(); ?>" class="related-thumbnail">
                                <?php the_post_thumbnail('thumbnail'); ?>
                            </a>
                            <?php endif; ?>
                            <h4 class="related-post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                            <div class="related-post-date"><?php echo get_the_date(); ?></div>
                        </article>
                        <?php endforeach; wp_reset_postdata(); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 评论区域 -->
				<?php
				// 如果允许评论，显示评论区域
				if (comments_open() || get_comments_number()):
				comments_template('/comments.php', true);
				endif;
				?>
                
            </article>
            
            <?php
                endwhile; // 结束循环
            else: // 如果没有文章
            ?>
            
            <div class="no-content">
                <h2>文章未找到</h2>
                <p>抱歉，您要查看的文章不存在或已被删除。</p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-primary">
                    返回首页
                </a>
            </div>
            
            <?php endif; // 结束 if have_posts ?>
            
        </main>
        
        <?php get_sidebar(); ?>
        
    </div>
</div>

<?php get_footer(); ?>
