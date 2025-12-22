<?php
/*
Plugin Name:HTSBCOMPANY PLUGIN
*/
add_action('init', function() {
    register_nav_menus(array(
        'primary-menu' => __('主菜单', 'enterprise-theme'),
        'footer-menu' => __('页脚菜单', 'enterprise-theme')
    ));
});



/*
add_theme_support('post-thumbnails');
add_theme_support('title-tag');
add_theme_support('custom-logo');
add_theme_support('widgets');






add_action('init', function() {
    register_post_type('product',
        array(
            'labels' => array(
                'name' => __('产品', 'enterprise-theme'),
                'singular_name' => __('产品', 'enterprise-theme'),
                'add_new' => __('添加新产品', 'enterprise-theme'),
                'add_new_item' => __('添加新产品', 'enterprise-theme'),
                'edit_item' => __('编辑产品', 'enterprise-theme'),
                'new_item' => __('新产品', 'enterprise-theme'),
                'view_item' => __('查看产品', 'enterprise-theme'),
                'search_items' => __('搜索产品', 'enterprise-theme'),
                'not_found' => __('未找到产品', 'enterprise-theme'),
                'not_found_in_trash' => __('回收站中无产品', 'enterprise-theme')
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-cart',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'rewrite' => array('slug' => 'products'),
            'show_in_rest' => true,
        )
    );
    
  
    register_taxonomy(
        'product_category',
        'product',
        array(
            'label' => __('产品分类', 'enterprise-theme'),
            'rewrite' => array('slug' => 'product-category'),
            'hierarchical' => true,
            'show_in_rest' => true
        )
    );
});



add_action('init', function() {
    register_post_type('service',
        array(
            'labels' => array(
                'name' => __('service', 'enterprise-theme'),
                'singular_name' => __('service', 'enterprise-theme'),
                'add_new' => __('add new service', 'enterprise-theme'),
                'add_new_item' => __('new service item', 'enterprise-theme'),
                'edit_item' => __('edit service', 'enterprise-theme'),
                'new_item' => __('new service', 'enterprise-theme'),
                'view_item' => __('view service', 'enterprise-theme'),
                'search_items' => __('search services', 'enterprise-theme'),
                'not_found' => __('not found service', 'enterprise-theme'),
                'not_found_in_trash' => __('not found in trash', 'enterprise-theme')
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-cart',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'rewrite' => array('slug' => 'services'),
            'show_in_rest' => true,
        )
    );
    

    register_taxonomy(
        'service_category',
        'service',
        array(
            'label' => __('service category', 'enterprise-theme'),
            'rewrite' => array('slug' => 'service-category'),
            'hierarchical' => true,
            'show_in_rest' => true
        )
    );
});


add_action('widgets_init', function() {
    register_sidebar(array(
        'name' => __('主侧边栏', 'enterprise-theme'),
        'id' => 'main-sidebar',
        'description' => __('主内容区侧边栏', 'enterprise-theme'),
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>'
    ));
    
    register_sidebar(array(
        'name' => __('页脚侧边栏', 'enterprise-theme'),
        'id' => 'footer-sidebar',
        'description' => __('页脚区域侧边栏', 'enterprise-theme'),
        'before_widget' => '<div class="footer-widget">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="footer-widget-title">',
        'after_title' => '</h4>'
    ));
});
*/

// 处理联系我们表单提交
add_action('template_redirect', function() {
    
    if (isset($_POST['contact_submit']) && wp_verify_nonce($_POST['contact_nonce'], 'contact_form_action')) {
        $name = sanitize_text_field($_POST['contact_name']);
        $email = sanitize_email($_POST['contact_email']);
        $phone = sanitize_text_field($_POST['contact_phone']);
        $company = sanitize_text_field($_POST['contact_company']);
        $message = sanitize_textarea_field($_POST['contact_message']);
        
        // 发送邮件给管理员
        $to = get_option('admin_email');
        $subject = '新联系表单提交 - ' . get_bloginfo('name');
        $body = "您收到一个新的联系表单提交： ";
        $body .= "姓名：$name ";
        $body .= "邮箱：$email ";
        $body .= "电话：$phone ";
        $body .= "公司：$company ";
        $body .= "消息： $message ";
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        wp_mail($to, $subject, $body, $headers);
        
        // 存储到数据库（可选）
        $contact_data = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'company' => $company,
            'message' => $message,
            'time' => current_time('mysql')
        );
        
        // 可以在这里添加数据库存储逻辑
        global $wpdb;
        $table_name=$wpdb->prefix . 'contact_us';

     

        $wpdb->insert($table_name,$contact_data);
        
        // 设置成功消息
        set_transient('contact_form_success', '感谢您的提交！我们会尽快联系您。', 30);
        
        // 重定向防止重复提交
      
        wp_redirect(add_query_arg('submitted', 'true', wp_get_referer()));
        //exit;
    }
});

add_action('rest_api_init',function(){
    register_rest_route('custom/v1','/hello',[
        'methods'=>'GET',
        'callback'=>function(WP_REST_Request $request){
            $data=$request->get_param('data');
            $sanitized_data=sanitize_text_field($data);
            $result=$data;
            return new WP_REST_Response(['message'=>'Hello from customer API!','result'=>$result,'success'=>true],200);
        },
        'permission_callback'=>'__return_true'
    ]);

    register_rest_route('myplugin/v1','/submit',[
        'methods'=>'POST',
        'callback'=>function(WP_REST_Request $request){
            $data=$request->get_param('data');
            $sanitized_data=sanitize_text_field($data);
            $result=$sanitized_data;

            return new WP_REST_Response([
                $result,
            ],200);
        },
        'permission_callback'=>function(){
            return true;
        },
    ]);
});

add_action('wp_ajax_my_ajax_action','handle_my_ajax_request');
add_action('wp_ajax_nopriv_my_ajax_action','handle_my_ajax_request');

function handle_my_ajax_request(){
    if (wp_verify_nonce($_POST['security'], 'my_ajax_nonce')) {
    $data=$_POST['data'];
    $sanitized_data=sanitize_text_field($data);
    $result=$sanitized_data;
    wp_send_json_success([
        
        'return'=>$result,
    ]);}
    else{
    wp_send_json_error([
       
        'return'=>$result,
    ]);
    }
}


// 加载脚本和样式
function enqueue_theme_scripts() {
	
    //wp_enqueue_style('theme-style', get_stylesheet_uri(), array(), time()); // 添加time()防止缓存
    wp_enqueue_style('theme-style', plugins_url('style.css',__FILE__), array(), time()); // 添加time()防止缓存
	
	  // 添加布局修复样式
    wp_add_inline_style('theme-style', '
        /* 确保基础显示 */
        .site-header, .site-footer, .container {
            display: block !important;
            visibility: visible !important;
        }
    ');
	
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
	
	
    
    //wp_enqueue_script('theme-script', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0', true);
    wp_enqueue_script('theme-script', plugins_url('js/main.js',__FILE__), array('jquery'), '1.0', true);
    wp_localize_script('theme-script', 'theme_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('theme_nonce')
    ));

    //wp_enqueue_script('my-ajax-script',get_template_directory_uri() . '/js/my_ajax.js',['jquery'],null,true);
    wp_enqueue_script('my-ajax-script',plugins_url('js/my_ajax.js',__FILE__),['jquery'],null,true);
    wp_localize_script('my-ajax-script','my_ajax_obj',[
        'ajax_url'=>admin_url('admin-ajax.php'),
        'nonce'=>wp_create_nonce('my_ajax_nonce'),
        'action'=>'my_ajax_action'
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_theme_scripts');


// 添加评论支持
function enterprise_theme_comment_support() {
    // 为"post"文章类型启用评论
    add_post_type_support('post', 'comments');
    
    // 为"product"产品类型启用评论（可选）
    add_post_type_support('product', 'comments');
}
add_action('init', 'enterprise_theme_comment_support');

// 修改评论表单字段顺序
function enterprise_theme_comment_fields($fields) {
    $comment_field = $fields['comment'];
    unset($fields['comment']);
    $fields['comment'] = $comment_field;
    return $fields;
}
add_filter('comment_form_fields', 'enterprise_theme_comment_fields');

// 修改评论头像大小
function enterprise_theme_avatar_size() {
    return 50;
}
add_filter('avatar_defaults', 'enterprise_theme_avatar_size');

// 防止垃圾评论（简单验证）
function enterprise_theme_comment_spam_protection($commentdata) {
    // 检查评论内容是否包含链接（可根据需要调整）
    $max_links = 2;
    $links = preg_match_all('/<a [^>]*href/i', $commentdata['comment_content']);
    
    if ($links >= $max_links) {
        wp_die(__('抱歉，您的评论包含太多链接，可能被视为垃圾评论。', 'enterprise-theme'));
    }
    
    return $commentdata;
}
add_filter('preprocess_comment', 'enterprise_theme_comment_spam_protection');


// AJAX产品分类过滤
add_action('wp_ajax_filter_products', 'ajax_filter_products');
add_action('wp_ajax_nopriv_filter_products', 'ajax_filter_products');

function ajax_filter_products() {
    // 验证nonce（安全验证）
    check_ajax_referer('theme_nonce', 'nonce');
    
    // 获取分类参数
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'all';
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    
    // 构建查询参数
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 3,
        'paged' => $page,
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => 'publish',
		//'nopaging'=>true
    );
    
    // 如果有具体的分类（不是"all"）
    if ($category !== 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category
            )
        );
    }
    
    // 执行查询
    $filtered_products = new WP_Query($args);
    
    // 开始输出缓冲
    ob_start();
    /*
    echo '<pre>';
    echo $filtered_products->request;
    echo '</pre>';
    */
    if ($filtered_products->have_posts()):
    ?>
        <div class="products-grid">
            <?php while ($filtered_products->have_posts()): $filtered_products->the_post(); 
                // 获取产品分类
                $product_categories = get_the_terms(get_the_ID(), 'product_category');
                $category_classes = '';
                if ($product_categories && !is_wp_error($product_categories)) {
                    $category_slugs = array();
                    foreach ($product_categories as $cat) {
                        $category_slugs[] = $cat->slug;
                    }
                    $category_classes = implode(' ', $category_slugs);
                }
            ?>
                <div class="product-item" data-categories="<?php echo esc_attr($category_classes); ?>">
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
                        if ($product_categories && !is_wp_error($product_categories)):
                            foreach ($product_categories as $category):
                        ?>
                            <span class="product-category"><?php echo esc_html($category->name); ?></span>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                    
                    <a href="<?php the_permalink(); ?>" class="btn-view">查看详情</a>
                </div>
            <?php endwhile; ?>
        </div>
        
        <!-- 分页 -->
        <?php if ($filtered_products->max_num_pages > 1): ?>
        <div class="pagination ajax-pagination">
            <?php
			
            echo paginate_links(array(
                'total' => $filtered_products->max_num_pages,
                'current' => $page,
                'prev_text' => '« 上一页',
                'next_text' => '下一页 »',
                'type' => 'list',
                'add_args' => array(
                    'category' => $category,
                    'ajax' => 'true'
                )
            ));
			
            ?>
        </div>
        <?php endif; ?>
    <?php
    else:
    ?>
        <div class="no-products-found">
            <p>该分类下暂无产品。</p>
            <a href="#" class="btn-reset-filter" data-category="all">查看所有产品</a>
        </div>
    <?php
    endif;
    
    wp_reset_postdata();
    
    // 获取输出内容
    $output = ob_get_clean();
    
    // 返回JSON响应
    wp_send_json_success(array(
        'html' => $output,
        'count' => $filtered_products->found_posts,
        'max_pages' => $filtered_products->max_num_pages,
        'current_page' => $page
    ));
}

// 处理URL中的分类参数
function handle_category_url_param($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (isset($_GET['category']) && $_GET['category'] !== 'all') {
            $category = sanitize_text_field($_GET['category']);
            
            $tax_query = array(
                array(
                    'taxonomy' => 'product_category',
                    'field' => 'slug',
                    'terms' => $category
                )
            );
            
            $query->set('tax_query', $tax_query);
        }
    }
}
add_action('pre_get_posts', 'handle_category_url_param');



// 在functions.php中添加短代码
add_shortcode('featured_products_slider', 'featured_products_slider_func');
function featured_products_slider_func() {
    ob_start();?>
    
    <section class="featured-products">
    <div class="section-header">
        <h2>特色产品</h2>
        <!--p class="section-subtitle">精选优质产品，满足您的需求</p-->
    </div>
    
    <div class="swiper featured-products-slider">
        <div class="swiper-wrapper">
            <?php
            $featured_args = array(
                'post_type' => 'product',
                'posts_per_page' => 3,
                'meta_query' => array(
                    array(
                        /*'key' => '_featured',
                        'value' => 'yes',
                        'compare' => '='*/
                    )
                ),
                'orderby' => 'date',
                'order' => 'DESC'
            );
            
            $featured_products = new WP_Query($featured_args);
            
            if ($featured_products->have_posts()):
                while ($featured_products->have_posts()): $featured_products->the_post();
                    $product_price = get_post_meta(get_the_ID(), '_product_price', true);
                    $is_featured = get_post_meta(get_the_ID(), '_featured', true) == 'yes';
            ?>
                    <div class="swiper-slide">
                        <?php if ($is_featured): ?>
                        <div class="featured-badge">特色产品</div>
                        <?php endif; ?>
                        
                        <div class="slide-product-image">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail('medium'); ?>
                                <?php else: ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/images/default-product.jpg" 
                                         alt="<?php the_title(); ?>">
                                <?php endif; ?>
                            </a>
                        </div>
                        
                        <div class="slide-product-info">
                            <h3 class="slide-product-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            
                            <div class="slide-product-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                            </div>
                            
                            <?php if ($product_price): ?>
                            <div class="slide-product-price">
                                <?php echo esc_html($product_price); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="slide-product-actions">
                                <a href="<?php the_permalink(); ?>" class="btn-detail">查看详情</a>
                            </div>
                        </div>
                    </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else:
            ?>
                <div class="swiper-slide no-products-slide">
                    <div class="no-products-content">
                        <i class="fas fa-box-open"></i>
                        <h3>暂无特色产品</h3>
                        <p>请先在产品编辑中标记特色产品</p>
                        <a href="/wp-admin/edit.php?post_type=product" class="btn-primary">去设置</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 分页器 -->
        <div class="swiper-pagination"></div>
        
        <!-- 导航按钮 -->
        <!--<div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>-->
    </div>
    
    <?php if ($featured_products->have_posts()): ?>
    <div class="view-all-products">
        <a href="/产品" class="btn-view-all">
            查看所有产品 <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <?php endif; ?>
</section>

        
    <?php
   
    //wp_enqueue_style('swiper-css', get_template_directory_uri() . '/swiper-bundle.min.css', array(), '8.4.5');
    wp_enqueue_style('swiper-css', plugins_url('swiper-bundle.min.css',__FILE__), array(), '8.4.5');    
        
        //wp_enqueue_script('swiper-js', get_template_directory_uri() .'/js/swiper-bundle.min.js', array(), '8.4.5', true);
        wp_enqueue_script('swiper-js', plugins_url('js/swiper-bundle.min.js',__FILE__), array(), '8.4.5', true);
        
        wp_add_inline_script('swiper-js', '
            document.addEventListener("DOMContentLoaded", function() {
                // 等待页面完全加载
                if (typeof Swiper !== "undefined") {
                    const swiper = new Swiper(".featured-products-slider", {
                        slidesPerView: 1,
                        spaceBetween: 30,
                        loop: true,
                        centeredSlides: false,
                        autoplay: {
                            delay: 2000,
                            disableOnInteraction: false,
                            pauseOnMouseEnter: true,
                        },
                        speed: 800,
                        grabCursor: true,
                        pagination: {
                            el: ".swiper-pagination",
                            clickable: true,
                            dynamicBullets: true,
                        },
                        navigation: {
                            nextEl: ".swiper-button-next",
                            prevEl: ".swiper-button-prev",
                        },
                        breakpoints: {
                            640: {
                                slidesPerView: 2,
                                spaceBetween: 20,
                            },
                            1024: {
                                slidesPerView: 3,
                                spaceBetween: 30,
                            },
                            1280: {
                                slidesPerView: 4,
                                spaceBetween: 30,
                            }
                        },
                        on: {
                            init: function() {
                                console.log("特色产品轮播已初始化");
                            }
                        }
                    });
                    
                    // 鼠标悬停时暂停自动播放
                    const slider = document.querySelector(".featured-products-slider");
                    if (slider) {
                        slider.addEventListener("mouseenter", function() {
                            swiper.autoplay.stop();
                        });
                        slider.addEventListener("mouseleave", function() {
                            swiper.autoplay.start();
                        });
                    }
                }
            });
        ');
    
    wp_reset_postdata();
    return ob_get_clean();
}
