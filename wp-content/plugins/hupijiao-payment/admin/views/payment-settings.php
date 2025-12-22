<?php
if (!defined('ABSPATH')) {
    exit;
}

// 检查用户权限
if (!current_user_can('manage_options')) {
    wp_die(__('您没有权限访问此页面'));
}

// 处理表单提交
if (isset($_POST['submit']) && check_admin_referer('hupijiao_payment_settings_update')) {
    // 保存支付方式设置
    $payment_methods = isset($_POST['payment_methods']) ? array_map('sanitize_text_field', $_POST['payment_methods']) : array();
    update_option('hupijiao_enabled_methods', $payment_methods);
    
    // 保存手续费设置
    if (isset($_POST['alipay_rate'])) {
        update_option('hupijiao_alipay_rate', floatval($_POST['alipay_rate']));
    }
    if (isset($_POST['wxpay_rate'])) {
        update_option('hupijiao_wxpay_rate', floatval($_POST['wxpay_rate']));
    }
    
    // 保存支付限额
    if (isset($_POST['min_amount'])) {
        update_option('hupijiao_min_amount', floatval($_POST['min_amount']));
    }
    if (isset($_POST['max_amount'])) {
        update_option('hupijiao_max_amount', floatval($_POST['max_amount']));
    }
    
    // 保存支付超时时间
    if (isset($_POST['timeout'])) {
        update_option('hupijiao_payment_timeout', intval($_POST['timeout']));
    }
    
    // 保存回调URL设置
    if (isset($_POST['notify_url'])) {
        update_option('hupijiao_notify_url', esc_url_raw($_POST['notify_url']));
    }
    if (isset($_POST['return_url'])) {
        update_option('hupijiao_return_url', esc_url_raw($_POST['return_url']));
    }
    if (isset($_POST['success_url'])) {
        update_option('hupijiao_success_url', esc_url_raw($_POST['success_url']));
    }
    
    echo '<div class="notice notice-success is-dismissible"><p>设置已保存！</p></div>';
}

// 获取当前设置
$enabled_methods = get_option('hupijiao_enabled_methods', array('alipay', 'wechat'));
$alipay_rate = get_option('hupijiao_alipay_rate', 0);
$wxpay_rate = get_option('hupijiao_wxpay_rate', 0);
$min_amount = get_option('hupijiao_min_amount', 0.01);
$max_amount = get_option('hupijiao_max_amount', 50000);
$timeout = get_option('hupijiao_payment_timeout', 1800);
$notify_url = get_option('hupijiao_notify_url', home_url('/wp-json/hupijiao/v1/notify'));
$return_url = get_option('hupijiao_return_url', home_url('/payment/return'));
$success_url = get_option('hupijiao_success_url', home_url('/payment/success'));
?>

<div class="wrap">
    <h1><span class="dashicons dashicons-money-alt"></span> 虎皮椒支付设置</h1>
    
    <div class="nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=hupijiao-payment'); ?>" class="nav-tab">基本设置</a>
        <a href="<?php echo admin_url('admin.php?page=hupijiao-payment-settings'); ?>" class="nav-tab nav-tab-active">支付设置</a>
        <a href="<?php echo admin_url('admin.php?page=hupijiao-orders'); ?>" class="nav-tab">订单管理</a>
    </div>
    
    <div class="hupijiao-settings-container">
        <div class="hupijiao-settings-main">
            <form method="post" action="">
                <?php wp_nonce_field('hupijiao_payment_settings_update'); ?>
                
                <!-- 支付方式设置 -->
                <div class="hupijiao-card">
                    <h2><span class="dashicons dashicons-forms"></span> 支付方式设置</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">启用支付方式</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="payment_methods[]" value="alipay" 
                                        <?php checked(in_array('alipay', $enabled_methods)); ?>>
                                    支付宝支付
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="payment_methods[]" value="wechat" 
                                        <?php checked(in_array('wechat', $enabled_methods)); ?>>
                                    微信支付
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- 手续费设置 -->
                <div class="hupijiao-card">
                    <h2><span class="dashicons dashicons-chart-area"></span> 手续费设置</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">支付宝手续费率</th>
                            <td>
                                <input type="number" name="alipay_rate" value="<?php echo esc_attr($alipay_rate); ?>" 
                                    min="0" max="100" step="0.01" style="width: 100px;"> %
                                <p class="description">支付宝支付手续费率，设置为0表示不收取手续费</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">微信支付手续费率</th>
                            <td>
                                <input type="number" name="wxpay_rate" value="<?php echo esc_attr($wxpay_rate); ?>" 
                                    min="0" max="100" step="0.01" style="width: 100px;"> %
                                <p class="description">微信支付手续费率，设置为0表示不收取手续费</p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- 支付限额设置 -->
                <div class="hupijiao-card">
                    <h2><span class="dashicons dashicons-money"></span> 支付限额设置</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">最低支付金额</th>
                            <td>
                                <input type="number" name="min_amount" value="<?php echo esc_attr($min_amount); ?>" 
                                    min="0.01" step="0.01" style="width: 150px;"> 元
                                <p class="description">单笔支付最低金额，不能低于0.01元</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">最高支付金额</th>
                            <td>
                                <input type="number" name="max_amount" value="<?php echo esc_attr($max_amount); ?>" 
                                    min="1" step="1" style="width: 150px;"> 元
                                <p class="description">单笔支付最高金额</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">支付超时时间</th>
                            <td>
                                <input type="number" name="timeout" value="<?php echo esc_attr($timeout); ?>" 
                                    min="60" step="60" style="width: 150px;"> 秒
                                <p class="description">订单支付超时时间，建议设置为1800秒（30分钟）</p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- 回调URL设置 -->
                <div class="hupijiao-card">
                    <h2><span class="dashicons dashicons-admin-links"></span> 回调URL设置</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">异步通知URL</th>
                            <td>
                                <input type="url" name="notify_url" value="<?php echo esc_url($notify_url); ?>" 
                                    class="regular-text" readonly>
                                <button type="button" class="button button-small copy-url" data-clipboard-target="#notify-url">
                                    复制
                                </button>
                                <input type="hidden" id="notify-url" value="<?php echo esc_url($notify_url); ?>">
                                <p class="description">将此URL填写到虎皮椒支付后台的异步通知地址中</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">同步返回URL</th>
                            <td>
                                <input type="url" name="return_url" value="<?php echo esc_url($return_url); ?>" 
                                    class="regular-text">
                                <p class="description">支付完成后同步跳转的URL</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">支付成功页面</th>
                            <td>
                                <input type="url" name="success_url" value="<?php echo esc_url($success_url); ?>" 
                                    class="regular-text">
                                <p class="description">支付成功后的跳转页面</p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button button-primary" value="保存设置">
                </p>
            </form>
        </div>
        
        <div class="hupijiao-settings-sidebar">
            <!-- 使用说明 -->
            <div class="hupijiao-card">
                <h3><span class="dashicons dashicons-info"></span> 使用说明</h3>
                <ul>
                    <li>1. 在虎皮椒官网申请商户账号</li>
                    <li>2. 获取App ID和App Secret</li>
                    <li>3. 在基本设置中填写API信息</li>
                    <li>4. 将异步通知URL复制到虎皮椒后台</li>
                    <li>5. 测试支付功能是否正常</li>
                </ul>
            </div>
            
            <!-- 回调测试 -->
            <div class="hupijiao-card">
                <h3><span class="dashicons dashicons-admin-tools"></span> 回调测试</h3>
                <p>测试异步通知是否正常工作：</p>
                <button type="button" class="button button-secondary" id="test-notify">测试回调</button>
                <div id="test-result" style="margin-top: 10px; display: none;"></div>
            </div>
            
            <!-- 订单统计 -->
            <div class="hupijiao-card">
                <h3><span class="dashicons dashicons-chart-line"></span> 订单统计</h3>
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'hupijiao_orders';
                
                $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                $today_orders = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE DATE(created_at) = %s",
                    current_time('Y-m-d')
                ));
                $total_amount = $wpdb->get_var("SELECT SUM(amount) FROM $table_name WHERE status = 'paid'");
                ?>
                <ul>
                    <li>总订单数：<?php echo intval($total_orders); ?></li>
                    <li>今日订单：<?php echo intval($today_orders); ?></li>
                    <li>总交易额：<?php echo number_format($total_amount ?: 0, 2); ?> 元</li>
                </ul>
            </div>

            // 在 payment-settings.php 中添加
<div class="hupijiao-card">
    <h3><span class="dashicons dashicons-hammer"></span> 故障修复</h3>
    <p>如果支付选项不显示，请尝试以下修复：</p>
    
    <form method="post" action="">
        <?php wp_nonce_field('hupijiao_fix_issue', 'fix_nonce'); ?>
        <input type="hidden" name="action" value="fix_gateway">
        <button type="submit" class="button button-secondary">修复网关问题</button>
        <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=hupijiao'); ?>" 
           class="button">前往WooCommerce支付设置</a>
        <a href="<?php echo add_query_arg('hupijiao_debug', '1'); ?>" 
           class="button">查看调试信息</a>
    </form>
    
    <?php
    if (isset($_POST['action']) && $_POST['action'] === 'fix_gateway') {
        if (wp_verify_nonce($_POST['fix_nonce'], 'hupijiao_fix_issue')) {
            if (class_exists('Hupijiao\\Fixer')) {
                $fixes = Hupijiao\Fixer::fix_gateway_issue();
                if (!empty($fixes)) {
                    echo '<div class="notice notice-success"><p>修复完成：</p><ul>';
                    foreach ($fixes as $fix) {
                        echo '<li>' . esc_html($fix) . '</li>';
                    }
                    echo '</ul></div>';
                } else {
                    echo '<div class="notice notice-info"><p>未发现问题，无需修复</p></div>';
                }
            }
        }
    }
    ?>
</div>

        </div>
    </div>
</div>

<style>
.hupijiao-settings-container {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.hupijiao-settings-main {
    flex: 3;
}

.hupijiao-settings-sidebar {
    flex: 1;
    min-width: 300px;
}

.hupijiao-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    margin-bottom: 20px;
    padding: 20px;
}

.hupijiao-card h2,
.hupijiao-card h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.hupijiao-card h2 .dashicons,
.hupijiao-card h3 .dashicons {
    margin-right: 10px;
    color: #0073aa;
}

.nav-tab-wrapper {
    margin-bottom: 20px;
}

.nav-tab-active {
    background: #fff;
    border-bottom: 1px solid #fff;
}
</style>

<script>
jQuery(document).ready(function($) {
    // 复制URL功能
    $('.copy-url').click(function() {
        var target = $(this).data('clipboard-target');
        var url = $(target).val();
        
        navigator.clipboard.writeText(url).then(function() {
            alert('URL已复制到剪贴板');
        }).catch(function(err) {
            console.error('复制失败:', err);
        });
    });
    
    // 测试回调功能
    $('#test-notify').click(function() {
        var button = $(this);
        var resultDiv = $('#test-result');
        
        button.prop('disabled', true).text('测试中...');
        resultDiv.hide().empty();
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'hupijiao_test_notify',
                nonce: '<?php echo wp_create_nonce('hupijiao_test'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    resultDiv.html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                } else {
                    resultDiv.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
                resultDiv.show();
            },
            error: function() {
                resultDiv.html('<div class="notice notice-error"><p>请求失败，请检查网络连接</p></div>');
                resultDiv.show();
            },
            complete: function() {
                button.prop('disabled', false).text('测试回调');
            }
        });
    });
});
</script>