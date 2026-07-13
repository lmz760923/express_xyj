<?php
/**
 * Plugin Name: Mini JWT Compat
 * Plugin URI:
 * Description: WP JWT账号密码登录+微信一键登录+获取用户信息+小程序JSAPI支付【后台设置版】
 * Version: 1.4
 * Author: Custom
 */

if (!defined('ABSPATH')) {
    exit;
}

// ====================== 不再硬编码业务配置，读取后台选项 ======================
function mpjwt_get_opt($key, $default = '')
{
    $opts = get_option('mpjwt_settings', []);
    return isset($opts[$key]) ? $opts[$key] : $default;
}

register_activation_hook(__FILE__, 'mpjwt_create_wx_table');

/**
 * 创建微信绑定数据表
 */
function mpjwt_create_wx_table()
{
    global $wpdb;
    $table = $wpdb->prefix . 'mini_wechat_bind';
    $sql = "CREATE TABLE IF NOT EXISTS `$table` (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        openid VARCHAR(100) NOT NULL COMMENT '微信openid',
        wp_uid BIGINT NOT NULL COMMENT 'WP用户ID',
        nickname VARCHAR(100),
        avatar VARCHAR(255),
        create_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_openid(openid),
        UNIQUE KEY uk_wpuid(wp_uid)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $wpdb->query($sql);

    // 初始化默认配置
    $exists = get_option('mpjwt_settings');
    if (!$exists) {
        add_option('mpjwt_settings', [
            'jwt_secret'      => 'CHANGE_TO_YOUR_RANDOM_SECRET_2026_ABC789XYZ',
            'jwt_ttl_days'    => 7,
            'wx_appid'        => '',
            'wx_secret'       => '',
            'wxpay_mchid'     => '',
            'wxpay_apikey_v2' => '',
            'woocommerce_ck'  => '',
            'woocommerce_cs'  => '',
            'woocommerce_api' => 'https://1.94.15.131/wp-json/wc/v3',
            'wxpay_notify'    => '',
        ]);
    }
}

/**
 * 后台菜单 + 设置页面
 */
add_action('admin_menu', function () {
    add_options_page(
        '小程序JWT & 支付配置',
        '小程序API配置',
        'manage_options',
        'mpjwt-settings',
        'mpjwt_render_settings_page'
    );
});

add_action('admin_init', function () {
    register_setting('mpjwt_settings_group', 'mpjwt_settings');
});

function mpjwt_render_settings_page()
{
    if (!current_user_can('manage_options')) wp_die();
    $opt = get_option('mpjwt_settings', []);
    ?>
    <div class="wrap">
        <h1>小程序 JWT / 微信支付 / Woo 接口配置</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('mpjwt_settings_group');
            do_settings_sections('mpjwt_settings_group');
            ?>
            <table class="form-table">
                <tr>
                    <th>JWT 加密密钥（务必随机长字符串）</th>
                    <td><input style="width:100%;max-width:650px;" type="text" name="mpjwt_settings[jwt_secret]" value="<?php echo esc_attr($opt['jwt_secret'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <th>Token有效期(天)</th>
                    <td><input type="number" name="mpjwt_settings[jwt_ttl_days]" value="<?php echo esc_attr($opt['jwt_ttl_days'] ?? 7) ?>"></td>
                </tr>
                <tr>
                    <th>微信小程序 AppID</th>
                    <td><input style="width:100%;max-width:650px;" type="text" name="mpjwt_settings[wx_appid]" value="<?php echo esc_attr($opt['wx_appid'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <th>微信小程序 AppSecret</th>
                    <td><input style="width:100%;max-width:650px;" type="text" name="mpjwt_settings[wx_secret]" value="<?php echo esc_attr($opt['wx_secret'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <th>微信支付 商户号 MCHID</th>
                    <td><input style="width:100%;max-width:650px;" type="text" name="mpjwt_settings[wxpay_mchid]" value="<?php echo esc_attr($opt['wxpay_mchid'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <th>微信支付 V2 API密钥（32位）</th>
                    <td><input style="width:100%;max-width:650px;" type="text" name="mpjwt_settings[wxpay_apikey_v2]" value="<?php echo esc_attr($opt['wxpay_apikey_v2'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <th>WooCommerce Consumer Key</th>
                    <td><input style="width:100%;max-width:650px;" type="text" name="mpjwt_settings[woocommerce_ck]" value="<?php echo esc_attr($opt['woocommerce_ck'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <th>WooCommerce Consumer Secret</th>
                    <td><input style="width:100%;max-width:650px;" type="text" name="mpjwt_settings[woocommerce_cs]" value="<?php echo esc_attr($opt['woocommerce_cs'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <th>WooCommerce API地址</th>
                    <td><input style="width:100%;max-width:650px;" type="text" name="mpjwt_settings[woocommerce_api]" value="<?php echo esc_attr($opt['woocommerce_api'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <th>微信支付异步通知URL</th>
                    <td><input style="width:100%;max-width:650px;" type="text" name="mpjwt_settings[wxpay_notify]" value="<?php echo esc_attr($opt['wxpay_notify'] ?? '') ?>"></td>
                </tr>
            </table>
            <?php submit_button('保存配置'); ?>
        </form>
    </div>
    <?php
}

add_action('rest_api_init', function () {
    // 全局CORS 解决小程序OPTIONS预检
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    add_filter('rest_pre_serve_request', function ($value) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 86400');
        return $value;
    });

    // 账号密码登录
    register_rest_route(
        'jwt-auth/v1',
        'token',
        [
            'methods' => 'POST',
            'callback' => 'mpjwt_handle_token',
            'permission_callback' => '__return_true'
        ]
    );

    // 微信一键登录
    register_rest_route(
        'jwt-auth/v1',
        'wxlogin',
        [
            'methods' => 'POST',
            'callback' => 'mpjwt_wx_login',
            'permission_callback' => '__return_true'
        ]
    );

    // 微信绑定账号
    register_rest_route(
        'jwt-auth/v1',
        'wxbind',
        [
            'methods' => 'POST',
            'callback' => 'mpjwt_wx_bind',
            'permission_callback' => '__return_true'
        ]
    );

    // 获取当前登录用户信息
    register_rest_route(
        'jwt-auth/v1',
        'me',
        [
            'methods' => 'GET',
            'callback' => 'mpjwt_get_me',
            'permission_callback' => 'mpjwt_token_check'
        ]
    );

    // 创建微信支付预下单
    register_rest_route(
        'jwt-auth/v1',
        'create_payment',
        [
            'methods' => 'POST',
            'callback' => 'mpjwt_create_payment',
            'permission_callback' => 'mpjwt_token_check'
        ]
    );

    // 支付异步回调通知
    register_rest_route(
        'jwt-auth/v1',
        'pay_notify',
        [
            'methods' => 'POST',
            'callback' => 'mpjwt_pay_notify',
            'permission_callback' => '__return_true'
        ]
    );

    register_rest_route('mini/v1', 'request/(?P<wc_path>.+)', [
'methods' => ['GET', 'POST'],
'callback' => function ($req) {
    // 清空全部输出缓冲区，杜绝碎片输出破坏JSON
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $wc_path = trim($req->get_param('wc_path'));
    $query   = $req->get_query_params();
    $body    = $req->get_json_params() ?: [];

    unset($query['wc_path']);
    unset($query['_route']);

    // ====================== 公开接口白名单，无需登录 ======================
    $publicPaths = [
        'products/categories',
        'products'
    ];
    $isPublic = in_array($wc_path, $publicPaths);

    // 如果不是公开接口，执行token校验
    if (!$isPublic) {
        $check = mpjwt_token_check($req);
        if (is_wp_error($check)) {
            return $check;
        }
    }
    // =====================================================================

    if (empty($wc_path) || !preg_match('/^[a-z0-9_\-\/]+$/i', $wc_path)) {
        return new WP_Error('invalid_path', '非法接口路径', ['status' => 400]);
    }
    if (str_contains($wc_path, '..') || str_starts_with(strtolower($wc_path), 'http')) {
        return new WP_Error('invalid_path', '非法接口路径', ['status' => 400]);
    }

    $wc_ck  = mpjwt_get_opt('woocommerce_ck');
    $wc_cs  = mpjwt_get_opt('woocommerce_cs');
    $wc_api = mpjwt_get_opt('woocommerce_api');

    $api_url = add_query_arg($query, trailingslashit($wc_api) . $wc_path);

    $method = strtoupper($req->get_method());
    $args = [
        'method'  => $method,
        'headers' => [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($wc_ck . ':' . $wc_cs)
        ],
        'timeout'  => 25,
        'sslverify' => false,
        'cookies' => [],
    ];

    if (!in_array($method, ['GET', 'HEAD']) && count($body) > 0) {
        $args['body'] = json_encode($body);
    }

    add_filter('http_response_cache_enabled', '__return_false');
    $resp = wp_remote_request($api_url, $args);
    remove_filter('http_response_cache_enabled', '__return_false');

    if (is_wp_error($resp)) {
        return new WP_Error('api_request_error', $resp->get_error_message(), ['status' => 500]);
    }

    $response_code = wp_remote_retrieve_response_code($resp);
    $response_body = wp_remote_retrieve_body($resp);
    $data = json_decode($response_body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('invalid_woo_response', '上游接口返回数据格式错误', [
            'status' => $response_code ?: 502
        ]);
    }

    ob_flush();
    flush();
    return new WP_REST_Response($data, $response_code);
},
// 🔴 这里改为 __return_true，不在路由层面拦截，移到回调内部手动校验
'permission_callback' => '__return_true'
]);
});

/**
 * Token鉴权中间件
 */
function mpjwt_token_check(WP_REST_Request $request)
{
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        return true;
    }
    $auth = $request->get_header('authorization');
    if (empty($auth) || stripos($auth, 'Bearer ') !== 0) {
        return new WP_Error('no_token', '请先登录', array('status' => 401));
    }
    $token = str_replace('Bearer ', '', $auth);
    $secret = mpjwt_get_opt('jwt_secret');
    $payload = mpjwt_decode($token, $secret);
    if (!$payload) {
        return new WP_Error('token_invalid', '登录已失效，请重新登录', array('status' => 401));
    }
    $request->set_param('_jwt_user', $payload);
    return true;
}

/**
 * 账号密码登录
 */
function mpjwt_handle_token(WP_REST_Request $request)
{
    $username = trim($request->get_param('username'));
    $password = trim($request->get_param('password'));

    if (empty($username) || empty($password)) {
        return new WP_Error('missing_params', '用户名和密码必填', array('status' => 400));
    }

    if (strpos($username, '@') !== false) {
        $user = get_user_by('email', $username);
    } else {
        $user = get_user_by('login', $username);
    }

    if (!$user || !wp_check_password($password, $user->user_pass)) {
        return new WP_Error('invalid_credentials', '账号或密码错误', array('status' => 401));
    }

    return mpjwt_build_token_response($user);
}

/**
 * 微信登录
 */
function mpjwt_wx_login(WP_REST_Request $request)
{
    $code = trim($request->get_param('code'));
    $nickname = trim($request->get_param('nickname'));
    $avatar = trim($request->get_param('avatar'));
    if (empty($code)) {
        return new WP_Error('empty_code', 'code不能为空', array('status' => 400));
    }

    $wx_appid  = mpjwt_get_opt('wx_appid');
    $wx_secret = mpjwt_get_opt('wx_secret');

    $url = sprintf(
        'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code',
        $wx_appid,
        $wx_secret,
        $code
    );
    $res = wp_remote_get($url);
    if (is_wp_error($res)) {
        return new WP_Error('wx_api_fail', '微信接口请求失败', array('status' => 500));
    }
    $body = json_decode(wp_remote_retrieve_body($res), true);
    if (!empty($body['errcode'])) {
        return new WP_Error('code_invalid', 'code无效或已过期', array('status' => 400));
    }
    $openid = $body['openid'];

    global $wpdb;
    $table = $wpdb->prefix . 'mini_wechat_bind';
    $row = $wpdb->get_row($wpdb->prepare("SELECT wp_uid FROM $table WHERE openid=%s", $openid));

    if (!$row) {
        return rest_ensure_response(array(
            'bind' => false,
            'openid' => $openid,
            'msg' => '该微信尚未绑定网站账号，请绑定'
        ));
    }

    $user = get_user_by('ID', $row->wp_uid);
    if (!$user) {
        return new WP_Error('user_not_found', '绑定的用户不存在', array('status' => 404));
    }
    return mpjwt_build_token_response($user);
}

/**
 * 微信绑定账号
 */
function mpjwt_wx_bind(WP_REST_Request $request)
{
    $openid = trim($request->get_param('openid'));
    $username = trim($request->get_param('username'));
    $password = trim($request->get_param('password'));
    $nickname = trim($request->get_param('nickname'));
    $avatar = trim($request->get_param('avatar'));

    if (empty($openid) || empty($username) || empty($password)) {
        return new WP_Error('empty_params', '参数不全', array('status' => 400));
    }

    if (strpos($username, '@') !== false) {
        $user = get_user_by('email', $username);
    } else {
        $user = get_user_by('login', $username);
    }
    if (!$user || !wp_check_password($password, $user->user_pass)) {
        return new WP_Error('invalid_credentials', '账号密码错误', array('status' => 401));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'mini_wechat_bind';
    $exist = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table WHERE openid=%s", $openid));
    if ($exist) {
        $wpdb->update($table, compact('wp_uid', 'nickname', 'avatar'), array('openid' => $openid));
    } else {
        $wpdb->insert($table, array(
            'openid' => $openid,
            'wp_uid' => $user->ID,
            'nickname' => $nickname,
            'avatar' => $avatar
        ));
    }

    return mpjwt_build_token_response($user);
}

/**
 * 获取当前登录用户信息
 */
function mpjwt_get_me(WP_REST_Request $request)
{
    $jwtData = $request->get_param('_jwt_user');
    $user = get_user_by('ID', $jwtData['uid']);
    if (!$user) {
        return new WP_Error('not_found', '用户不存在', array('status' => 404));
    }
    return rest_ensure_response(array(
        'id' => $user->ID,
        'username' => $user->user_login,
        'email' => $user->user_email,
        'display_name' => $user->display_name
    ));
}

/**
 * 统一生成token返回
 */
function mpjwt_build_token_response($user)
{
    $ttl_days = (int)mpjwt_get_opt('jwt_ttl_days',7);
    $payload = array(
        'iss'  => get_home_url(),
        'iat'  => time(),
        'exp'  => time() + $ttl_days * 24 * 3600,
        'uid'  => $user->ID,
        'login' => $user->user_login,
        'email' => $user->user_email
    );
    $secret = mpjwt_get_opt('jwt_secret');
    $token = mpjwt_encode($payload, $secret);

    return rest_ensure_response(array(
        'token' => $token,
        'user_email' => $user->user_email,
        'user_nicename' => $user->user_login,
        'user_display_name' => $user->display_name
    ));
}

// ================= JWT基础编码解码 =================
function mpjwt_encode(array $payload, string $secret): string
{
    $header = json_encode(array('alg' => 'HS256', 'typ' => 'JWT'));
    $payloadJson = json_encode($payload);

    $h = mpjwt_b64url($header);
    $p = mpjwt_b64url($payloadJson);
    $sigRaw = hash_hmac('sha256', "$h.$p", $secret, true);
    $s = mpjwt_b64url($sigRaw);
    return "$h.$p.$s";
}

function mpjwt_decode(string $token, string $secret)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    list($hStr, $pStr, $sStr) = $parts;

    $sigExpect = hash_hmac('sha256', "$hStr.$pStr", $secret, true);
    $sigInput = mpjwt_b64urldec($sStr);
    if (!hash_equals($sigExpect, $sigInput)) {
        return false;
    }

    $rawPayload = mpjwt_b64urldec($pStr);
    $data = json_decode($rawPayload, true);
    if (!$data || empty($data['exp']) || time() > $data['exp']) {
        return false;
    }
    return $data;
}

function mpjwt_b64url($str): string
{
    return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
}
function mpjwt_b64urldec($str): string
{
    return base64_decode(strtr($str, '-_', '+/'));
}

/**
 * 微信支付预下单接口
 */
function mpjwt_create_payment(WP_REST_Request $request)
{
    if (!function_exists('wc_get_order')) {
        return new WP_Error('woocommerce_missing', '未启用WooCommerce插件', ['status' => 500]);
    }
    $order_id = (int)$request->get_param('order_id');
    $code = trim($request->get_param('code'));

    if (empty($order_id) || empty($code)) {
        return new WP_Error('empty_param', 'order_id、code不能为空', ['status' => 400]);
    }
    $jwtData = $request->get_param('_jwt_user');
    $user_id = $jwtData['uid'];

    $order = wc_get_order($order_id);
    if (!$order) {
        return new WP_Error('order_not_found', '订单不存在', ['status' => 404]);
    }
    if ($order->is_paid()) {
        return new WP_Error('order_paid', '订单已支付', ['status' => 400]);
    }

    $wx_appid      = mpjwt_get_opt('wx_appid');
    $wx_secret     = mpjwt_get_opt('wx_secret');
    $mchid         = mpjwt_get_opt('wxpay_mchid');
    $wxpay_v2_key  = mpjwt_get_opt('wxpay_apikey_v2');
    $notify_url    = mpjwt_get_opt('wxpay_notify');

    if (empty($mchid) || empty($wxpay_v2_key)) {
        return new WP_Error('merchant_empty', '后台未配置微信商户参数', ['status' => 500]);
    }

    // 1、code换取openid
    $sessionUrl = sprintf(
        'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code',
        $wx_appid,
        $wx_secret,
        $code
    );
    $sessionResp = wp_remote_get($sessionUrl);
    if (is_wp_error($sessionResp)) {
        return new WP_Error('wx_session_fail', '获取openid失败', ['status' => 500]);
    }
    $sessionData = json_decode(wp_remote_retrieve_body($sessionResp), true);
    if (!empty($sessionData['errcode'])) {
        return new WP_Error('code_error', 'code失效', ['status' => 400]);
    }
    $openid = $sessionData['openid'];

    // 2、统一下单
    $total_fee = (int)($order->get_total() * 100);
    $out_trade_no = $order->get_order_key();
    $body = '订单#' . $order_id;

    $params = [
        'appid' => $wx_appid,
        'mch_id' => $mchid,
        'nonce_str' => mpjwt_random_str(24),
        'body' => $body,
        'out_trade_no' => $out_trade_no,
        'total_fee' => $total_fee,
        'spbill_create_ip' => mpjwt_get_client_ip(),
        'notify_url' => $notify_url,
        'trade_type' => 'JSAPI',
        'openid' => $openid
    ];
    ksort($params);
    $signRaw = http_build_query($params) . '&key=' . $wxpay_v2_key;
    $params['sign'] = strtoupper(md5($signRaw));

    $xml = mpjwt_array_to_xml($params);
    $payResp = wp_remote_post('https://api.mch.weixin.qq.com/pay/unifiedorder', [
        'body' => $xml,
        'timeout' => 30
    ]);
    if (is_wp_error($payResp)) {
        return new WP_Error('wx_unified_fail', '请求微信统一下单失败', ['status' => 500]);
    }
    $payResult = mpjwt_xml_to_array(wp_remote_retrieve_body($payResp));
    if ($payResult['return_code'] !== 'SUCCESS' || $payResult['result_code'] !== 'SUCCESS') {
        return new WP_Error('prepay_failed', $payResult['return_msg'], ['status' => 400]);
    }
    $prepay_id = $payResult['prepay_id'];

    // 3、小程序支付签名包
    $timeStamp = (string)time();
    $payPackage = [
        'appId' => $wx_appid,
        'timeStamp' => $timeStamp,
        'nonceStr' => mpjwt_random_str(24),
        'package' => "prepay_id={$prepay_id}",
        'signType' => 'MD5'
    ];

    ksort($payPackage);
    $paySignRaw = http_build_query($payPackage) . '&key=' . $wxpay_v2_key;
    $payPackage['paySign'] = strtoupper(md5($paySignRaw));

    return rest_ensure_response($payPackage);
}

/**
 * 微信支付异步回调
 */
function mpjwt_pay_notify()
{
    $xml = file_get_contents('php://input');
    $data = mpjwt_xml_to_array($xml);
    if ($data['return_code'] != 'SUCCESS') {
        echo mpjwt_array_to_xml(['return_code' => 'FAIL', 'return_msg' => 'error']);
        exit;
    }
    $out_trade_no = $data['out_trade_no'];
    $order = wc_get_order($out_trade_no);
    if ($order && !$order->is_paid()) {
        $order->payment_complete($data['transaction_id']);
        $order->add_order_note('微信小程序支付成功，微信交易号：' . $data['transaction_id']);
    }
    echo mpjwt_array_to_xml(['return_code' => 'SUCCESS', 'return_msg' => 'OK']);
    exit;
}

//================工具函数================
function mpjwt_random_str($len = 16)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $str = '';
    for ($i = 0; $i < $len; $i++) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $str;
}

function mpjwt_array_to_xml($arr)
{
    $xml = '<xml>';
    foreach ($arr as $k => $v) {
        $xml .= "<{$k}><![CDATA[{$v}]]></{$k}>";
    }
    $xml .= '</xml>';
    return $xml;
}

function mpjwt_xml_to_array($xml)
{
    libxml_disable_entity_loader(true);
    return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
}

function mpjwt_get_client_ip()
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return $_SERVER['REMOTE_ADDR'];
}