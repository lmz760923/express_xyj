<?php get_header(); ?>

<main>
    <section class="page-hero">
        <div class="container">
            <h1><?php the_title(); ?></h1>
        </div>
    </section>

    <section class="page-content">
        <div class="container">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <article class="card">
                    <?php the_content(); ?>
                </article>
            <?php endwhile; endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
