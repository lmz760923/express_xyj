<?php get_header(); ?>

<div class="container">
    <div class="content-wrapper">
        <main class="main-content">
            <header class="archive-header">
                <h1 class="archive-title">
                    <?php
                    if (is_category()) {
                        single_cat_title('分类：');
                    } elseif (is_tag()) {
                        single_tag_title('标签：');
                    } elseif (is_author()) {
                        the_author();
                    } elseif (is_date()) {
                        if (is_year()) {
                            echo get_the_date('Y年');
                        } elseif (is_month()) {
                            echo get_the_date('Y年m月');
                        } elseif (is_day()) {
                            echo get_the_date('Y年m月d日');
                        }
                    } else {
                        echo '新闻动态';
                    }
                    ?>
                </h1>
                
                <?php
                if (is_category() || is_tag()) {
                    $description = term_description();
                    if ($description):
                ?>
                        <div class="archive-description">
                            <?php echo $description; ?>
                        </div>
                <?php
                    endif;
                }
                ?>
            </header>
            
            <?php if (have_posts()): ?>
                <div class="news-archive">
                    <?php while (have_posts()): the_post(); ?>
                        <article <?php post_class('news-archive-item'); ?>>
                            <header class="entry-header">
                                <h2 class="entry-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                <div class="entry-meta">
                                    <span class="post-date">
                                        <i class="far fa-calendar"></i> <?php echo get_the_date(); ?>
                                    </span>
                                    <span class="post-author">
                                        <i class="far fa-user"></i> <?php the_author(); ?>
                                    </span>
                                    <span class="post-categories">
                                        <i class="far fa-folder"></i> <?php the_category(', '); ?>
                                    </span>
                                </div>
                            </header>
                            
                            <div class="entry-content">
                                <?php if (has_post_thumbnail()): ?>
                                    <div class="post-thumbnail">
                                        <?php the_post_thumbnail('medium'); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="entry-summary">
                                    <?php the_excerpt(); ?>
                                </div>
                            </div>
                            
                            <footer class="entry-footer">
                                <a href="<?php the_permalink(); ?>" class="read-more">
                                    阅读全文 <i class="fas fa-arrow-right"></i>
                                </a>
                            </footer>
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
            <?php else: ?>
                <p>暂无内容。</p>
            <?php endif; ?>
        </main>
        
        <?php get_sidebar(); ?>
    </div>
</div>

<?php get_footer(); ?>
