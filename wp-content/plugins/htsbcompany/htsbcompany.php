<?php
/*
Plugin Name:HTSBCOMPANY PLUGIN
*/

define('htsbcompany_path',plugin_dir_path(__FILE__));
define('htsbcompany_url',plugin_dir_url(__FILE__));

/**
 * 激活插件回调
 */
register_activation_hook( __FILE__, function(){
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'contact_us';

    // 1. 创建留言表单数据表
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(120) NOT NULL,
        phone VARCHAR(60),
        company VARCHAR(150),
        message TEXT,
        time DATETIME NOT NULL,
        INDEX idx_email (email)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // 2. 需要自动创建的页面配置 [页面标题, 页面别名pagename, 模板文件名]
    $pages_need = [
        ['企业首页',        'front',        'page-front.php'],
        ['产品展示',        'products',     'page-products.php'],
        ['关于我们',        'about_us',     'page-about.php'],
        ['联系我们',        'form_submit',  'page-contact.php'],
    ];

    foreach ($pages_need as $item){
        list($title, $slug, $tpl_file) = $item;
        $exist_page = get_page_by_path($slug, OBJECT, 'page');
        if(empty($exist_page)){
            wp_insert_post([
                'post_title'     => $title,
                'post_name'      => $slug,
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'post_author'    => get_current_user_id(),
                'post_content'   => '',
                'post_excerpt'   => '',
            ]);
        }
    }

    flush_rewrite_rules();
});

/**
 * 停用插件回调
 */
register_deactivation_hook( __FILE__, function(){
    flush_rewrite_rules();
    // 停用不删除页面、不删除数据表，保留数据
});

/**
 * 【可选】彻底卸载插件清理（如需开启，新建 uninstall.php 放到插件根目录，不要写在主文件内）
 * uninstall.php 内容：
<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
global $wpdb;
$table = $wpdb->prefix.'contact_us';
$wpdb->query("DROP TABLE IF EXISTS {$table}");
$slugs = ['front','products','about_us','form_submit'];
foreach($slugs as $s){
    $p = get_page_by_path($s,OBJECT,'page');
    if($p) wp_delete_post($p->ID,true);
}
 */

add_action('init', function() {
    register_nav_menus(array(
        'primary-menu' => __('主菜单', 'enterprise-theme'),
        'footer-menu' => __('页脚菜单', 'enterprise-theme')
    ));
});

// 注册产品自定义文章类型（若 WooCommerce 已注册 product，则跳过）
add_action('init', function() {
    if (!post_type_exists('product')) {
        register_post_type('product', array(
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
            'has_archive' => false,
            'menu_icon' => 'dashicons-cart',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'comments'),
            'rewrite' => array('slug' => 'products'),
            'show_in_rest' => true,
        ));
    }

    if (!taxonomy_exists('product_cat')) {
        register_taxonomy('product_cat', 'product', array(
            'labels' => array(
                'name' => __('产品分类', 'enterprise-theme'),
                'singular_name' => __('产品分类', 'enterprise-theme')
            ),
            'rewrite' => array('slug' => 'product-category'),
            'hierarchical' => true,
            'show_in_rest' => true
        ));
    }
});


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
        $body = "您收到一个新的联系表单提交：\n";
        $body .= "姓名：$name \n";
        $body .= "邮箱：$email \n";
        $body .= "电话：$phone \n";
        $body .= "公司：$company \n";
        $body .= "消息： $message \n";

        $headers = array('Content-Type: text/plain; charset=UTF-8');

        wp_mail($to, $subject, $body, $headers);

        // 存储到数据库
        global $wpdb;
        $table_name=$wpdb->prefix . 'contact_us';
        $contact_data = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'company' => $company,
            'message' => $message,
            'time' => current_time('mysql')
        );
        $wpdb->insert($table_name,$contact_data);

        set_transient('contact_form_success', '感谢您的提交！我们会尽快联系您。', 30);
        wp_redirect(add_query_arg('submitted', 'true', wp_get_referer()));
        exit;
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
            return new WP_REST_Response([$result],200);
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
        wp_send_json_success(['return'=>$result]);
    }else{
        wp_send_json_error(['return'=>$_POST['data']]);
    }
}

// 加载脚本和样式
function enqueue_theme_scripts() {
    wp_enqueue_style('theme-style', plugins_url('style.css',__FILE__), array(), time());
    wp_add_inline_style('theme-style', '
        .site-header, .site-footer, .container {
            display: block !important;
            visibility: visible !important;
        }
    ');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

    wp_enqueue_script('theme-script', plugins_url('js/main.js',__FILE__), array('jquery'), '1.0', true);
    wp_localize_script('theme-script', 'theme_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('theme_nonce')
    ));

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
    add_post_type_support('post', 'comments');
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

// 修改评论头像大小（原函数钩子错误，修复）
function enterprise_theme_avatar_size($size) {
    return 50;
}
add_filter('get_avatar_size', 'enterprise_theme_avatar_size');

// 防止垃圾评论
function enterprise_theme_comment_spam_protection($commentdata) {
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
    check_ajax_referer('theme_nonce', 'nonce');
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'all';
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 3,
        'paged' => $page,
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => 'publish',
    );

    // ==========BUG修复：taxonomy统一为 product_cat（之前写product_category 不匹配）==========
    if ($category !== 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category
            )
        );
    }

    $filtered_products = new WP_Query($args);
    ob_start();
    if ($filtered_products->have_posts()):
    ?>
        <div class="products-grid">
            <?php while ($filtered_products->have_posts()): $filtered_products->the_post();
                $product_categories = get_the_terms(get_the_ID(), 'product_cat');
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
                        <?php endforeach; endif; ?>
                    </div>
                    <a href="<?php the_permalink(); ?>" class="btn-view">查看详情</a>
                </div>
            <?php endwhile; ?>
        </div>
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
    <?php else: ?>
        <div class="no-products-found">
            <p>该分类下暂无产品。</p>
            <a href="#" class="btn-reset-filter" data-category="all">查看所有产品</a>
        </div>
    <?php endif;
    wp_reset_postdata();
    $output = ob_get_clean();
    wp_send_json_success(array(
        'html' => $output,
        'count' => $filtered_products->found_posts,
        'max_pages' => $filtered_products->max_num_pages,
        'current_page' => $page
    ));
}

// =========BUG修复：taxonomy名称统一 product_cat=========
function handle_category_url_param($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (isset($_GET['category']) && $_GET['category'] !== 'all') {
            $category = sanitize_text_field($_GET['category']);
            $tax_query = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $category
                )
            );
            $query->set('tax_query', $tax_query);
        }
    }
}
add_action('pre_get_posts', 'handle_category_url_param');

add_shortcode('all_products','all_products');
function all_products(){
    ob_start();?>
    <div class="product-filters">
        <ul class="category-filter">
            <li><a href="#" data-category="all" class="active">全部产品</a></li>
            <?php
            $categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => true
            ));
            foreach ($categories as $category):
            ?><li><a href="#" data-category="<?php echo esc_attr($category->slug);?>"><?php echo esc_html($category->name);?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
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
                            $cats = get_the_terms(get_the_ID(), 'product_cat');
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
                            $categories = get_the_terms(get_the_ID(), 'product_cat');
                            if ($categories):
                                foreach ($categories as $category):
                            ?>
                                <span class="product-category"><?php echo esc_html($category->name); ?></span>
                            <?php endforeach; endif; ?>
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
    <?php return ob_get_clean();
}

add_shortcode('featured_products_slider', 'featured_products_slider_func');
function featured_products_slider_func() {
    ob_start();?>
    <section class="featured-products">
    <div class="section-header">
        <h2>特色产品</h2>
    </div>
    <div class="swiper featured-products-slider">
        <div class="swiper-wrapper">
            <?php
            $featured_args = array(
                'post_type' => 'product',
                'posts_per_page' => 3,
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
                                    <img src="" alt="<?php the_title(); ?>">
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
        <div class="swiper-pagination"></div>
    </div>
    <?php
    wp_enqueue_style('swiper-css', plugins_url('swiper-bundle.min.css',__FILE__), array(), '8.4.5');
    wp_enqueue_script('swiper-js', plugins_url('js/swiper-bundle.min.js',__FILE__), array(), '8.4.5', true);
    wp_add_inline_script('swiper-js', '
            document.addEventListener("DOMContentLoaded", function() {
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
                        }
                    });
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

add_filter('template_include',function($template){
    global $wp_query;
    $page=get_query_var('pagename');
    if ($page)
    {
        $mytemplate=htsbcompany_path . 'templates/' . $page .'.php';
        if (file_exists($mytemplate)){
            return $mytemplate;
        }
    }
    if (is_front_page()){
        $mytemplate=htsbcompany_path . 'templates/page-front.php';
        if (file_exists($mytemplate)){
            return $mytemplate;
        }
    }
    return $template;
});

add_filter('post_thumbnail_html','remove_width_height_attribute');
add_filter('image_send_to_editor','remove_width_height_attribute');
function remove_width_height_attribute($html){
 $html=preg_replace('/(width|height)="\d*"\s/',"",$html);
 return $html;
}