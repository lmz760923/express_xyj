<?php
/**
 * Plugin Name: 虎皮椒支付
 * Plugin URI: https://1.94.15.131/
 * Description: 集成虎皮椒支付到WordPress网站
 * Version: 1.0.0
 * Author: mz.li
 * License: GPL v2 or later
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('HUPIJIAO_VERSION', '1.0.0');
define('HUPIJIAO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HUPIJIAO_PLUGIN_URL', plugin_dir_url(__FILE__));

// 自动加载类
spl_autoload_register(function ($class) {
    $prefix = 'Hupijiao\\';
    $base_dir = HUPIJIAO_PLUGIN_DIR . 'includes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// 初始化插件
class Hupijiao_Payment_Plugin {
    private $payment=null;
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    public function load_hpj(){
        if (class_exists('WooCommerce'))
        require_once HUPIJIAO_PLUGIN_DIR . 'includes/WC_Gateway.php';
    }
    private function init_hooks() {
        // 激活/停用插件
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        

        add_action('plugins_loaded', array($this, 'load_hpj'));
        // 初始化
        add_action('plugins_loaded', array($this, 'init'));
        
        // 添加设置菜单
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // 注册设置
        add_action('admin_init', array($this, 'register_settings'));
        
        add_action('admin_notices',function(){
            if (isset($_GET['hupijiao_debug']) && current_user_can('manage_options')){
                if (class_exists('Hupijiao\\Debug')){
                    Hupijiao\Debug::display_debug_info();
                }
            }
        });
        
    }

    private function init_ajax(){
        // 添加Ajax处理函数
        add_action('wp_ajax_hupijiao_test_notify', array($this,'hupijiao_test_notify_callback'));
        add_action('wp_ajax_nopriv_hupijiao_test_notify', array($this,'hupijiao_test_notify_callback'));

        add_action('wp_ajax_hupijiao_create_order',array($this,'hupijiao_create_order'));
        add_action('wp_ajax_nopriv_hupijiao_create_order',array($this,'hupijiao_create_order'));

        // 新增调试相关的AJAX处理
        add_action('wp_ajax_hupijiao_force_refresh', array($this, 'ajax_force_refresh'));
        add_action('wp_ajax_hupijiao_clear_cache', array($this, 'ajax_clear_cache'));
        add_action('wp_ajax_hupijiao_test_checkout', array($this, 'ajax_test_checkout'));

    }

    // 新增方法：强制刷新网关
public function ajax_force_refresh() {
    check_ajax_referer('hupijiao_refresh', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('权限不足');
    }
    
    // 删除WooCommerce网关缓存
    delete_transient('woocommerce_gateway_order');
    delete_transient('wc_gateway_hupijiao');
    
    // 清除选项缓存
    wp_cache_delete('alloptions', 'options');
    
    // 重新初始化支付网关
    if (class_exists('WC_Payment_Gateways')) {
        WC()->payment_gateways()->init();
    }
    
    wp_send_json_success('网关已强制刷新，请重新检查设置页面。');
}

// 新增方法：清除缓存
public function ajax_clear_cache() {
    check_ajax_referer('hupijiao_clear_cache', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('权限不足');
    }
    
    // 清除WordPress缓存
    wp_cache_flush();
    
    // 清除WooCommerce缓存
    if (function_exists('wc_delete_product_transients')) {
        wc_delete_product_transients();
    }
    
    // 清除WooCommerce会话
    if (function_exists('WC')) {
        WC()->session->cleanup_sessions();
    }
    
    wp_send_json_success('缓存已清除，请刷新页面查看效果。');
}

// 新增方法：测试订单
public function ajax_test_checkout() {
    check_ajax_referer('hupijiao_test', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('权限不足');
    }
    
    $amount = floatval($_POST['test_amount']);
    $type = sanitize_text_field($_POST['test_payment_type']);
    
    if ($amount <= 0) {
        wp_send_json_error('金额无效');
    }
    
    try {
        // 创建测试订单
        $order = wc_create_order(array(
            'status' => 'pending',
            'customer_id' => get_current_user_id(),
        ));
        
        // 添加虚拟产品
        $product = new WC_Product_Simple();
        $product->set_name('测试商品');
        $product->set_price($amount);
        $product->set_regular_price($amount);
        $product->save();
        
        $order->add_product($product, 1);
        $order->calculate_totals();
        
        // 设置为虎皮椒支付
        $order->set_payment_method('hupijiao');
        $order->save();
        
        // 创建支付
        require_once HUPIJIAO_PLUGIN_DIR . 'includes/Payment.php';
        $payment = new Hupijiao\Payment();
        
        $result = $payment->create_order(array(
            'order_id' => 'TEST' . $order->get_id(),
            'amount' => $amount,
            'type' => $type,
            'name' => '测试订单 - ' . $order->get_id(),
            'notify_url' => home_url('/?hupijiao_callback=notify'),
            'return_url' => home_url('/?hupijiao_callback=return')
        ));
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => '测试订单创建成功！订单号：' . $order->get_id() . 'notify_url:' . home_url('/?hupijiao_callback=notify'),
                'redirect' => $result['payment_url']
            ));
        } else {
            wp_send_json_error('支付创建失败：' . $result['message']);
        }
        
    } catch (Exception $e) {
        wp_send_json_error('创建订单时出错：' . $e->getMessage());
    }
}

    
    public function hupijiao_create_order(){
        if (!wp_verify_nonce($_POST['hupijiao_nonce'],'hupijiao_create_order')) {
        //if (check_ajax_referer('hupijiao_create_order','hupijiao_nonce'))
       
            wp_die('secrity check fail');
        }
        $amount=floatval($_POST['amount']);
        $type=sanitize_text_field($_POST['type']);
        if ($amount <=0){
            wp_die(json_encode(array(
                'success'=>false,
                'message'=>'amount invalid!'
            )));
        }
       
      
        $result=$this->payment->create_order(array(
            'amount'=>$amount,
            'type'=>$type,
            'name'=>'test'
        ));
        wp_die(json_encode($result));
    }

    public function hupijiao_test_notify_callback() {
         // 验证nonce
             if (!wp_verify_nonce($_POST['nonce'], 'hupijiao_test')) {
             wp_die('安全检查失败');
            }
    
                // 检查用户权限
            if (!current_user_can('manage_options')) {
                 wp_die('权限不足');
            }
    
            // 模拟支付回调测试
            $test_data = array(
                'out_trade_no' => 'TEST' . time(),
                'transaction_id' => 'TEST' . mt_rand(100000, 999999),
                'total_fee' => '1.00',
                'trade_status' => 'TRADE_SUCCESS',
                 'timestamp' => time()
                );
    
            // 这里可以添加更复杂的测试逻辑
            // 例如：检查回调URL是否可访问
    
            wp_die(json_encode($test_data));
            }
    
    public function activate() {
        // 创建必要的数据表
        $this->create_tables();
        
        // 添加默认选项
        add_option('hupijiao_payment_version', HUPIJIAO_VERSION);
    }
    
    public function deactivate() {
        // 清理临时数据
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'hupijiao_orders';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id varchar(50) NOT NULL,
            trade_no varchar(100) DEFAULT NULL,
            amount decimal(10,2) NOT NULL,
            payment_method varchar(20) DEFAULT NULL,
            status varchar(20) DEFAULT 'pending',
            user_id bigint(20) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            paid_at datetime DEFAULT NULL,
            notify_data text,
            PRIMARY KEY (id),
            UNIQUE KEY order_id (order_id),
            KEY trade_no (trade_no),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function init() {
        
        if (class_exists('WooCommerce')){
            // 加载支付网关
            add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateway'));
        }
        // 或者作为独立支付系统
        if (class_exists('Hupijiao\\Payment')) {
            $this->payment=new Hupijiao\Payment();
        }

        $this->init_ajax();
    }
    
    public function add_payment_gateway($gateways) {
        $gateways[] = 'Hupijiao_WC_Gateway';
        
        return $gateways;
    }
    
    public function add_admin_menu() {
        add_menu_page(
            '虎皮椒支付设置',
            '虎皮椒支付',
            'manage_options',
            'hupijiao-payment',
            array($this, 'settings_page'),
            'dashicons-money-alt',
            58
        );
        
        add_submenu_page(
            'hupijiao-payment',
            '订单管理',
            '订单管理',
            'manage_options',
            'hupijiao-orders',
            array($this, 'orders_page')
        );
        
        add_submenu_page(
            'hupijiao-payment',
            '支付设置',
            '支付设置',
            'manage_options',
            'hupijiao-payment-setting',
            array($this, 'payment_settings_page')
        );

        // 添加调试页面（仅管理员可见）
        add_submenu_page(
        'hupijiao-payment',
        '调试信息',
        '调试信息',
        'manage_options',
        'hupijiao-debug',
        function(){include HUPIJIAO_PLUGIN_DIR . 'admin/views/debug.php';}
    );

    }


    
    
    public function settings_page() {
        include HUPIJIAO_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    public function orders_page() {
        include HUPIJIAO_PLUGIN_DIR . 'admin/views/orders.php';
    }
    
    public function payment_settings_page() {
        include HUPIJIAO_PLUGIN_DIR . 'admin/views/payment-settings.php';
    }
    
    public function register_settings() {
        // 基本设置
        register_setting('hupijiao_settings', 'hupijiao_api_url');
        register_setting('hupijiao_settings', 'hupijiao_app_id');
        register_setting('hupijiao_settings', 'hupijiao_app_secret');
        
        // 支付设置
        register_setting('hupijiao_payment_settings', 'hupijiao_alipay_enabled');
        register_setting('hupijiao_payment_settings', 'hupijiao_alipay_rate');
        register_setting('hupijiao_payment_settings', 'hupijiao_wxpay_enabled');
        register_setting('hupijiao_payment_settings', 'hupijiao_wxpay_rate');
        
        // 回调设置
        register_setting('hupijiao_payment_settings', 'hupijiao_notify_url');
        register_setting('hupijiao_payment_settings', 'hupijiao_return_url');
    }
}

// 初始化插件
Hupijiao_Payment_Plugin::get_instance();