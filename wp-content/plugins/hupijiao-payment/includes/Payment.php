<?php
namespace Hupijiao;

class Payment {
    
    private $api_url;
    private $app_id;
    private $app_secret;
    
    public function __construct() {
        $this->api_url = get_option('hupijiao_api_url', 'https://api.xunhupay.com/payment');
        $this->app_id = get_option('hupijiao_app_id', '');
        $this->app_secret = get_option('hupijiao_app_secret', '');
        
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // 添加支付短代码
        add_shortcode('hupijiao_payment', array($this, 'payment_shortcode'));
        
        // 处理支付回调
        add_action('init', array($this, 'handle_callback'));
        
        // 处理异步通知
        add_action('wp_ajax_nopriv_hupijiao_notify', array($this, 'handle_notify'));
        add_action('wp_ajax_hupijiao_notify', array($this, 'handle_notify'));
    }
    
    /**
     * 创建支付订单
     */
    public function create_order($params) {
        global $wpdb;
        
        $defaults = array(
            'order_id' => $this->generate_order_no(),
            'amount' => 0,
            'type' => 'alipay', // alipay, wechat
            'notify_url' => get_option('hupijiao_notify_url', home_url('/hupijiao/notify')),
            'return_url' => get_option('hupijiao_return_url', home_url('/hupijiao/return')),
            'name' => '商品支付',
            'client_ip' => $this->get_client_ip()
        );
        
        $params = wp_parse_args($params, $defaults);
        
        // 保存到数据库
        $table_name = $wpdb->prefix . 'hupijiao_orders';
        $wpdb->insert(
            $table_name,
            array(
                'order_id' => $params['order_id'],
                'amount' => $params['amount'],
                'payment_method' => $params['type'],
                'status' => 'pending',
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            )
        );
        
    
        // 调用虎皮椒API
        $result = $this->call_api('do.html', $params);
        
        if ($result && $result['errcode'] == 0) {
            return array(
                'success' => true,
                'order_id' => $params['order_id'],
                'payment_url' => $result['url_qrcode'],
                'trade_no' => $result['trade_no']
            );
        }
        
        return array(
            'success' => false,
            'message' => $result['errmsg'] ?? 'pay creation fail'
        );
    }
    
    /**
     * 调用虎皮椒API
     */
    private function call_api($method, $params) {
        $url = rtrim($this->api_url, '/') . '/' . $method;
        
        // 添加必要参数
        $params['appid'] = $this->app_id;
        $params['hash'] = $this->generate_sign($params);
        
        $response = wp_remote_post($url, array(
            'body' => $params,
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
    /**
     * 生成签名
     */
    private function generate_sign($params) {
        ksort($params);
        $sign_string = '';
        
        foreach ($params as $key => $value) {
            if ($key != 'hash' && $value !== '') {
                $sign_string .= $key . '=' . $value . '&';
            }
        }
        
        $sign_string = rtrim($sign_string, '&');
        return md5($sign_string . $this->app_secret);
    }
    
    /**
     * 验证签名
     */
    private function verify_sign($params, $sign) {
        $local_sign = $this->generate_sign($params);
        return $local_sign === $sign;
    }
    
    /**
     * 处理支付回调
     */
    public function handle_callback() {
        if (!isset($_GET['hupijiao_callback'])) {
            return;
        }
        
        $action = $_GET['action'] ?? '';
        
        if ($action === 'notify') {
            $this->handle_notify();
        } elseif ($action === 'return') {
            $this->handle_return();
        }
    }
    
    /**
     * 处理异步通知
     */
    public function handle_notify() {
        $data = $_POST;
        
        // 验证签名
        $sign = $data['hash'] ?? '';
        unset($data['hash']);
        
        if (!$this->verify_sign($data, $sign)) {
            wp_die('签名验证失败');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'hupijiao_orders';
        
        // 更新订单状态
        $order_id = $data['out_trade_no'] ?? '';
        $trade_no = $data['transaction_id'] ?? '';
        
        if ($order_id) {
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'paid',
                    'trade_no' => $trade_no,
                    'paid_at' => current_time('mysql'),
                    'notify_data' => json_encode($data)
                ),
                array('order_id' => $order_id)
            );
            
            // 触发订单支付完成钩子
            do_action('hupijiao_order_paid', $order_id, $data);
        }
        
        echo 'success';
        exit;
    }
    
    /**
     * 处理同步返回
     */
    private function handle_return() {
        $order_id = $_GET['out_trade_no'] ?? '';
        
        if ($order_id) {
            // 跳转到支付成功页面
            $success_url = get_option('hupijiao_success_url', home_url());
            wp_redirect(add_query_arg('order_id', $order_id, $success_url));
            exit;
        }
    }
    
    /**
     * 生成订单号
     */
    private function generate_order_no() {
        return date('YmdHis') . mt_rand(1000, 9999);
    }
    
    /**
     * 获取客户端IP
     */
    private function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        return $ip;
    }
    
    /**
     * 支付短代码
     */
    public function payment_shortcode($atts) {
        $atts = shortcode_atts(array(
            'amount' => 0,
            'type' => 'alipay',
            'title' => '商品支付'
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '<p>请先登录</p>';
        }
        
        ob_start();
        //echo plugins_url('assets/script.js',__DIR__);
        
        wp_enqueue_script('pay_ajax',HUPIJIAO_PLUGIN_URL . 'assets/script.js',array(),1.0,true);
        wp_localize_script('pay_ajax','pay_ajax_obj',array(
            'ajax_url'=>admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hupijiao_create_order')
            )
        );
        ?>

        <div class="hupijiao-payment-form">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <form method="post" id="myform">
                <input type="hidden" name="action" value="hupijiao_create_order">
                <input type="hidden" name="amount" value="<?php echo $atts['amount']; ?>">
                <input type="hidden" name="type" value="<?php echo $atts['type']; ?>">
                
                <div class="form-group">
                    <label>支付金额：</label>
                    <input type="number" name="custom_amount" value="<?php echo $atts['amount']; ?>" min="0.01" step="0.01">
                </div>
                
                <div class="form-group">
                    <label>支付方式：</label>
                    <select name="custom_type">
                        <option value="alipay" <?php selected($atts['type'], 'alipay'); ?>>支付宝</option>
                        <option value="wechat" <?php selected($atts['type'], 'wechat'); ?>>微信支付</option>
                    </select>
                </div>
                
               
                <button type="submit" class="btn btn-primary" onclick="submit_form(event)">立即支付</button>
            </form>
        </div>
          
        <?php
        return ob_get_clean();
    }
}
