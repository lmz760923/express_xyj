<?php
/*
Template Name: 产品列表页面
*/
include('header.php');
?>

<div class="container">

    <div class="content-wrapper">
        <main class="main-content">
            <header class="page-header">
                <!--h1><?php the_title(); ?></h1-->
                
            </header>
            <?php echo do_shortcode('[featured_products_slider]');?>
            <a href="/products" class="btn-view">所有产品>>>>>>></a>
        </main>
        <!--?php get_sidebar();?-->
        
    </div>
</div>
<?php include('footer.php'); ?>

