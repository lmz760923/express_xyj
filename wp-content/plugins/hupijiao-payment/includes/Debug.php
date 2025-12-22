<?php
namespace Hupijiao;

class Debug {
    
    public static function check_gateway_status() {
        $results = array();
        
        // 1. 检查WooCommerce是否激活
        $results[] = array(
            'label' => 'WooCommerce激活状态',
            'status' => class_exists('WooCommerce'),
            'message' => class_exists('WooCommerce') ? '已激活' : '未激活'
        );
        
        // 2. 检查网关类是否存在
        $results[] = array(
            'label' => '网关类加载状态',
            'status' => class_exists('Hupijiao_WC_Gateway'),
            'message' => class_exists('Hupijiao_WC_Gateway') ? '已加载' : '未加载'
        );
        
        // 3. 检查网关是否注册
        $gateways = WC()->payment_gateways()->payment_gateways();
        $results[] = array(
            'label' => '网关注册状态',
            'status' => isset($gateways['hupijiao']),
            'message' => isset($gateways['hupijiao']) ? '已注册' : '未注册'
        );
        
        // 4. 检查网关是否启用
        if (isset($gateways['hupijiao'])) {
            $gateway = $gateways['hupijiao'];
            $results[] = array(
                'label' => '网关启用状态',
                'status' => $gateway->enabled === 'yes',
                'message' => $gateway->enabled === 'yes' ? '已启用' : '未启用'
            );
            
            // 5. 检查配置是否完整
            $config_ok = !empty($gateway->app_id) && !empty($gateway->app_secret);
            $results[] = array(
                'label' => '网关配置状态',
                'status' => $config_ok,
                'message' => $config_ok ? '配置完整' : 'App ID或App Secret未配置'
            );
        }
        
        return $results;
    }
    
    public static function display_debug_info() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $results = self::check_gateway_status();
        
        echo '<div class="notice notice-info">';
        echo '<h3>虎皮椒支付网关调试信息</h3>';
        echo '<table class="widefat striped">';
        echo '<thead><tr><th>检查项</th><th>状态</th><th>说明</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($results as $result) {
            $status_class = $result['status'] ? 'status-good' : 'status-bad';
            $status_text = $result['status'] ? '✓' : '✗';
            
            echo '<tr>';
            echo '<td>' . esc_html($result['label']) . '</td>';
            echo '<td><span class="' . $status_class . '">' . $status_text . '</span></td>';
            echo '<td>' . esc_html($result['message']) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        echo '</div>';
        
        // 添加样式
        echo '<style>
            .status-good { color: #46b450; font-weight: bold; }
            .status-bad { color: #dc3232; font-weight: bold; }
        </style>';
    }
}
