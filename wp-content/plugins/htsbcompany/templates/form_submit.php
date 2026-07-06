<?php
/*
Template Name: 联系我们
*/
include('header.php');
?>

<div class="container">

    <div class="content-wrapper">
        <main class="main-content">
            <header class="page-header">
                <!--h1><php the_title(); ?></h1-->
                
            </header>
            <?php echo do_shortcode('[user_data_form show_phone="true" show_company="true" show_message="true" submit_text="提交信息"]');?>
            <?php
            echo '<pre>';
            global $wp_query;
            //print_r($wp_query);
            echo '</pre>';
            ?>
            
        </main>
        <!--?php get_sidebar();?-->
        
    </div>
</div>
<?php include('footer.php'); ?>