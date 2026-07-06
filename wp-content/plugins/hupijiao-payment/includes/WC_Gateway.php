<?php

/**
 * WooCommerce 虎皮椒支付网关
 */

if (!defined('ABSPATH')) {
    exit;
}

// 确保WooCommerce存在
if (!class_exists('WC_Payment_Gateway')) {
    
    return;
}

class Hupijiao_WC_Gateway extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->id = 'hupijiao';
        $this->icon = HUPIJIAO_PLUGIN_URL . 'assets/images/logo.png';
        $this->has_fields = true;
        $this->method_title = '虎皮椒支付';
        $this->method_description = '通过支付宝/微信支付';
        $this->supports=array('products');

        // 加载设置字段
        $this->init_form_fields();

        // 加载设置
        $this->init_settings();

        // 定义用户设置的变量
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->app_id = $this->get_option('app_id');
        $this->app_secret = $this->get_option('app_secret');
        $this->api_url = $this->get_option('api_url');
        $this->instructions = $this->get_option('instructions');
       
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('api_url:' . $this->api_url);
            error_log('app_secret:' . $this->app_secret);
            error_log('enabled:'. $this->enabled);
        }
        
        
        // 保存设置
        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            array($this, 'process_admin_options')
        );

        // 处理回调
        add_action('woocommerce_api_wc_gateway_' . $this->id, array($this, 'handle_callback'));

        // 添加支付说明
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
   
        
    }

    // 新增方法：检查网关是否可用
    public function is_available()
    {
        return true;
        // 首先调用父类方法检查基本可用性
  
        $is_available = parent::is_available();
/*
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('虎皮椒支付 is_available() 被调用');
            error_log('父类返回：' . ($is_available ? 'true' : 'false'));
            error_log('当前网关enabled：' . $this->enabled);
            error_log('当前货币：' . get_woocommerce_currency());
            error_log('订单金额：' . (WC()->cart ? WC()->cart->get_total('') : '无购物车'));
        }
*/
        // 基础检查
        if ($this->enabled !== 'yes') {
            return false;
        }

        // 检查购物车是否存在
        if (!is_object(WC()->cart)) {
            return true; // 允许在购物车页面外显示
        }

        // 检查最小金额
        $min_amount = $this->get_option('min_amount', 0);
        if ($min_amount > 0 && WC()->cart->total < $min_amount) {
            return false;
        }

        // 检查最大金额
        $max_amount = $this->get_option('max_amount', 0);
        if ($max_amount > 0 && WC()->cart->total > $max_amount) {
            return false;
        }

        // 检查适用商品类别（如果有设置）
        $categories = $this->get_option('categories', array());
        if (!empty($categories)) {
            $cart_categories = array();
            foreach (WC()->cart->get_cart() as $cart_item) {
                $product_categories = get_the_terms($cart_item['product_id'], 'product_cat');
                if ($product_categories) {
                    foreach ($product_categories as $cat) {
                        $cart_categories[] = $cat->term_id;
                    }
                }
            }

            if (!empty($cart_categories)) {
                $intersect = array_intersect($categories, $cart_categories);
                if (empty($intersect)) {
                    return false;
                }
            }
        }

        // 检查必要的配置
        if (empty($this->app_id) || empty($this->app_secret)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('虎皮椒支付：App ID 或 App Secret 未配置');
            }
            return false;
        }

        // 检查是否启用了网关
        if ($this->enabled !== 'yes') {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('虎皮椒支付：网关未启用');
            }
            return false;
        }

        // 检查货币（虎皮椒通常只支持人民币）
 /*     $currency = get_woocommerce_currency();
        if ($currency !== 'CNY' && $currency !== 'RMB') {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('虎皮椒支付：不支持当前货币 ' . $currency);
            }
            return false;
        }
*/
        // 检查最小金额（如果有设置）
        $min_amount = $this->get_option('min_amount', 0.01);
        if (WC()->cart && WC()->cart->get_total('') < $min_amount) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('虎皮椒支付：订单金额低于最低限制');
            }
            return false;
        }

        // 记录最终结果
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('虎皮椒支付网关最终可用性：' . ($is_available ? 'true' : 'false'));
        }


        
        return $is_available;
    }

    /**
     * 初始化设置字段
     */
    public function init_form_fields()
    {
            $this->form_fields = array(
            'enabled' => array(
                'title' => '启用/禁用',
                'type' => 'checkbox',
                'label' => '启用虎皮椒支付',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => '支付方式名称',
                'type' => 'text',
                'description' => '客户在结账时看到的支付方式名称',
                'default' => '虎皮椒支付',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => '支付方式描述',
                'type' => 'textarea',
                'description' => '支付方式描述，显示在结账页面',
                'default' => '使用支付宝或微信支付完成付款',
                'desc_tip' => true,
            ),
            'app_id' => array(
                'title' => 'App ID',
                'type' => 'text',
                'description' => '虎皮椒支付商户App ID',
                'default' => '',
                'desc_tip' => true,
            ),
            'app_secret' => array(
                'title' => 'App Secret',
                'type' => 'password',
                'description' => '虎皮椒支付商户App Secret',
                'default' => '',
                'desc_tip' => true,
            ),
            'api_url' => array(
                'title' => 'API地址',
                'type' => 'text',
                'description' => '虎皮椒支付API地址',
                'default' => 'https://api.xunhupay.com/payment',
                'desc_tip' => true,
            ),
            'instructions' => array(
                'title' => '支付说明',
                'type' => 'textarea',
                'description' => '支付完成后显示的说明信息',
                'default' => '感谢您的购买！支付成功后，我们将尽快处理您的订单。',
                'desc_tip' => true,
            )
        );
    }

    /**
     * 处理支付
     */
    public function process_payment($order_id)
    {

        wc_add_notice('支付宝微信支付还没开通', 'error');
        return array('result' => 'fail');

        $order = wc_get_order($order_id);

        // 检查订单是否有效
        if (!$order) {
            wc_add_notice('订单不存在', 'error');
            return array('result' => 'fail');
        }
        
        // 检查金额
        if ($order->get_total() <= 0) {
            wc_add_notice('订单金额无效', 'error');
            return array('result' => 'fail');
        }

        try {
            // 加载支付类
            if (!class_exists('Hupijiao\\Payment')) {
                require_once HUPIJIAO_PLUGIN_DIR . 'includes/Payment.php';
            }

            // 设置支付参数
            $payment_params = array(
                'app_id' => $this->app_id,
                'app_secret' => $this->app_secret,
                'api_url' => $this->api_url
            );

            // 创建支付实例
            $payment = new Hupijiao\Payment($payment_params);

            // 构建支付参数
            $params = array(
                'order_id' => $order_id . '-' . $this->generate_order_no($order),
                'amount' => floatval($order->get_total()),
                'type' => 'alipay', // 默认支付宝，可以改为用户选择
                'name' => '订单支付 - ' . $order->get_order_number(),
                'notify_url' => WC()->api_request_url('wc_gateway_' . $this->id),
                'return_url' => $this->get_return_url($order)
            );

            // 调用虎皮椒API创建支付
            $result = $payment->create_order($params);

            if ($result['success']) {
                // 保存交易信息到订单备注
                $order->add_order_note(sprintf(
                    '虎皮椒支付已创建，订单号：%s，支付链接：%s',
                    $result['order_id'],
                    $result['payment_url']
                ));

                // 更新订单状态为等待支付
                $order->update_status('pending', __('等待客户支付', 'woocommerce'));

                // 保存交易号到订单元数据
                $order->update_meta_data('_hupijiao_order_id', $result['order_id']);
                $order->update_meta_data('_hupijiao_trade_no', $result['trade_no']);
                $order->save();

                // 跳转到支付页面
                return array(
                    'result' => 'success',
                    'redirect' => $result['payment_url']
                );
            } else {
                wc_add_notice('支付创建失败：' . $result['message'], 'error');
                return array('result' => 'fail');
            }
        } catch (Exception $e) {
            wc_add_notice('支付处理出错：' . $e->getMessage(), 'error');
            return array('result' => 'fail');
        }
    }

    /**
     * 生成订单号
     */
    private function generate_order_no($order)
    {
        // 使用WooCommerce订单ID加上时间戳，确保唯一性
        return 'WC' . $order->get_id() . 'T' . time();
    }

    /**
     * 处理回调通知
     */
    public function handle_callback()
    {
        // 获取回调数据
        $data = $_POST;

        // 记录日志
        $this->log('虎皮椒回调收到数据：' . print_r($data, true));

        // 验证签名
        $sign = isset($data['hash']) ? $data['hash'] : '';
        unset($data['hash']);

        // 加载支付类验证签名
        if (!class_exists('Hupijiao\\Payment')) {
            require_once HUPIJIAO_PLUGIN_DIR . 'includes/Payment.php';
        }

        $payment = new Hupijiao\Payment(array(
            'app_id' => $this->app_id,
            'app_secret' => $this->app_secret
        ));

        if (!$payment->verify_sign($data, $sign)) {
            $this->log('签名验证失败');
            wp_die('签名验证失败', '虎皮椒支付', array('response' => 403));
        }

        // 获取订单信息
        $order_no = isset($data['out_trade_no']) ? $data['out_trade_no'] : '';
        $trade_no = isset($data['transaction_id']) ? $data['transaction_id'] : '';
        $amount = isset($data['total_fee']) ? floatval($data['total_fee']) : 0;

        // 解析订单ID（从我们生成的订单号中提取）
        preg_match('/^WC(\d+)T/', $order_no, $matches);
        $order_id = isset($matches[1]) ? intval($matches[1]) : 0;

        if (!$order_id) {
            $this->log('无法解析订单ID，订单号：' . $order_no);
            wp_die('订单号无效', '虎皮椒支付', array('response' => 400));
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            $this->log('订单不存在，ID：' . $order_id);
            wp_die('订单不存在', '虎皮椒支付', array('response' => 404));
        }

        // 检查订单是否已支付
        if ($order->is_paid()) {
            $this->log('订单已支付，ID：' . $order_id);
            echo 'success';
            exit;
        }

        // 验证金额
        $order_amount = floatval($order->get_total());
        if (abs($order_amount - $amount) > 0.01) {
            $this->log('金额不匹配，订单金额：' . $order_amount . '，回调金额：' . $amount);
            wp_die('金额不匹配', '虎皮椒支付', array('response' => 400));
        }

        // 更新订单状态
        $order->payment_complete($trade_no);
        $order->add_order_note(sprintf(
            '虎皮椒支付成功，交易号：%s，金额：%s元',
            $trade_no,
            $amount
        ));

        // 保存回调数据
        $order->update_meta_data('_hupijiao_callback_data', json_encode($data));
        $order->save();

        $this->log('订单支付成功，ID：' . $order_id . '，交易号：' . $trade_no);

        // 返回成功响应
        echo 'success';
        exit;
    }

    /**
     * 感谢页面显示
     */
    public function thankyou_page($order_id)
    {
        $order = wc_get_order($order_id);

        if ($order && $order->get_payment_method() === $this->id) {
            if ($order->is_paid()) {
                echo '<div class="woocommerce-message">' . wp_kses_post(wpautop(wptexturize($this->instructions))) . '</div>';
            } else {
                echo '<div class="woocommerce-info">等待支付完成...</div>';
            }
        }
    }

 

    /**
     * 记录日志
     */
    private function log($message)
    {
        if ($this->get_option('debug') === 'yes') {
            $logger = wc_get_logger();
            $logger->debug($message, array('source' => 'hupijiao-payment'));
        }
    }
}
