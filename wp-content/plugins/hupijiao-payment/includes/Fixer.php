<?php
namespace Hupijiao;

class Fixer {
    
    public static function fix_gateway_issue() {
        $fixes = array();
        
        // 1. 检查并创建网关选项
        $options = array(
            'woocommerce_hupijiao_settings' => array(
                'enabled' => 'yes',
                'title' => '虎皮椒支付',
                'description' => '使用支付宝或微信支付完成付款',
                'instructions' => '感谢您的购买！支付成功后，我们将尽快处理您的订单。',
                'app_id' => get_option('hupijiao_app_id', ''),
                'app_secret' => get_option('hupijiao_app_secret', ''),
                'api_url' => get_option('hupijiao_api_url', 'https://api.xunhupay.com/payment')
            )
        );
        
        foreach ($options as $option_name => $default_value) {
            if (!get_option($option_name)) {
                update_option($option_name, $default_value);
                $fixes[] = "创建选项: {$option_name}";
            }
        }
        
        // 2. 确保网关在WooCommerce中注册
        if (class_exists('WC_Payment_Gateways')) {
            $gateways = WC()->payment_gateways();
            
            // 手动注册网关
            if (!isset($gateways->payment_gateways()['hupijiao'])) {
                $gateways->payment_gateways()['hupijiao'] = new \Hupijiao_WC_Gateway();
                $fixes[] = '手动注册虎皮椒支付网关';
            }
        }
        
        // 3. 清除WooCommerce缓存
        if (function_exists('wc_delete_product_transients')) {
            wc_delete_product_transients();
            $fixes[] = '清除WooCommerce缓存';
        }
        
        return $fixes;
    }
}
