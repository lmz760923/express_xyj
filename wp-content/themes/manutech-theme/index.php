<?php get_header(); ?>

<main class="page-content">
    <div class="container">
        <h1 class="section-title"><?php single_post_title(); ?></h1>
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class('card'); ?>>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p><?php the_excerpt(); ?></p>
                </article>
            <?php endwhile; ?>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <div class="card">
                <p>暂无内容。</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
