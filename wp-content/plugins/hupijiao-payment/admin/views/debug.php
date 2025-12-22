<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// 收集调试信息
$debug_info = array();

// 1. 检查WooCommerce
$debug_info[] = array(
    'label' => 'WooCommerce激活状态',
    'value' => class_exists('WooCommerce') ? '✓ 已激活' : '✗ 未激活',
    'status' => class_exists('WooCommerce')
);

// 2. 检查网关类
$debug_info[] = array(
    'label' => '网关类加载状态',
    'value' => class_exists('Hupijiao_WC_Gateway') ? '✓ 已加载' : '✗ 未加载',
    'status' => class_exists('Hupijiao_WC_Gateway')
);

// 3. 获取所有支付网关
$gateways = array();
if (function_exists('WC') && method_exists(WC(), 'payment_gateways')) {
    $gateways_obj = WC()->payment_gateways();
    if ($gateways_obj) {
        $all_gateways = $gateways_obj->payment_gateways();
        foreach ($all_gateways as $id => $gateway) {
            $gateways[] = array(
                'id' => $id,
                'title' => $gateway->title,
                'enabled' => $gateway->enabled,
                'class' => get_class($gateway)
            );
        }
    }
}

// 4. 检查虎皮椒网关
$hupijiao_gateway = null;
foreach ($gateways as $gateway) {
    if ($gateway['id'] === 'hupijiao') {
        $hupijiao_gateway = $gateway;
        break;
    }
}

$debug_info[] = array(
    'label' => '虎皮椒网关注册状态',
    'value' => $hupijiao_gateway ? '✓ 已注册' : '✗ 未注册',
    'status' => !empty($hupijiao_gateway)
);

if ($hupijiao_gateway) {
    $debug_info[] = array(
        'label' => '虎皮椒网关启用状态',
        'value' => $hupijiao_gateway['enabled'] === 'yes' ? '✓ 已启用' : '✗ 未启用',
        'status' => $hupijiao_gateway['enabled'] === 'yes'
    );
}

// 5. 检查货币
$currency = get_woocommerce_currency();
$debug_info[] = array(
    'label' => '商店货币',
    'value' => $currency,
    'status' => in_array($currency, array('CNY', 'RMB', '¥')),
    'note' => '虎皮椒支付通常只支持人民币(CNY/RMB)'
);

// 6. 检查配置
$app_id = get_option('woocommerce_hupijiao_settings');
if (is_array($app_id) && isset($app_id['app_id'])) {
    $app_id_value = $app_id['app_id'];
} else {
    $app_id_value = get_option('hupijiao_app_id', '');
}

$debug_info[] = array(
    'label' => 'App ID配置',
    'value' => !empty($app_id_value) ? '✓ 已配置' : '✗ 未配置',
    'status' => !empty($app_id_value)
);

// 7. 检查WordPress和WooCommerce版本
$debug_info[] = array(
    'label' => 'WordPress版本',
    'value' => get_bloginfo('version'),
    'status' => version_compare(get_bloginfo('version'), '5.0', '>=')
);

$debug_info[] = array(
    'label' => 'WooCommerce版本',
    'value' => defined('WC_VERSION') ? WC_VERSION : '未知',
    'status' => defined('WC_VERSION') && version_compare(WC_VERSION, '3.0', '>=')
);

// 8. 检查PHP版本
$debug_info[] = array(
    'label' => 'PHP版本',
    'value' => PHP_VERSION,
    'status' => version_compare(PHP_VERSION, '7.0', '>=')
);

// 9. 检查主题兼容性
$theme = wp_get_theme();
$debug_info[] = array(
    'label' => '当前主题',
    'value' => $theme->get('Name') . ' v' . $theme->get('Version'),
    'status' => true,
    'note' => ''
);

// 10. 检查插件冲突
$active_plugins = get_option('active_plugins');
$payment_plugins = array();
foreach ($active_plugins as $plugin) {
    if (strpos($plugin, 'woocommerce') !== false || 
        strpos($plugin, 'payment') !== false ||
        strpos($plugin, 'gateway') !== false) {
        $payment_plugins[] = $plugin;
    }
}

$debug_info[] = array(
    'label' => '支付相关插件',
    'value' => count($payment_plugins) . ' 个',
    'status' => true,
    'note' => implode(', ', $payment_plugins)
);
?>

<div class="wrap">
    <h1>虎皮椒支付调试信息</h1>
    
    <div class="card">
        <h2>系统状态检查</h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th width="30%">检查项</th>
                    <th width="40%">状态</th>
                    <th width="30%">备注</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($debug_info as $info): ?>
                <tr>
                    <td><?php echo esc_html($info['label']); ?></td>
                    <td>
                        <span class="status-indicator status-<?php echo $info['status'] ? 'good' : 'bad'; ?>">
                            <?php echo $info['value']; ?>
                        </span>
                    </td>
                    <td><?php echo isset($info['note']) ? esc_html($info['note']) : ''; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="card">
        <h2>已注册的支付网关</h2>
        <?php if (!empty($gateways)): ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名称</th>
                    <th>启用状态</th>
                    <th>类名</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gateways as $gateway): ?>
                <tr class="<?php echo $gateway['id'] === 'hupijiao' ? 'hupijiao-highlight' : ''; ?>">
                    <td><?php echo esc_html($gateway['id']); ?></td>
                    <td><?php echo esc_html($gateway['title']); ?></td>
                    <td>
                        <span class="status-indicator status-<?php echo $gateway['enabled'] === 'yes' ? 'good' : 'bad'; ?>">
                            <?php echo $gateway['enabled'] === 'yes' ? '已启用' : '未启用'; ?>
                        </span>
                    </td>
                    <td><code><?php echo esc_html($gateway['class']); ?></code></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>未检测到支付网关</p>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <h2>常见问题解决方案</h2>
        <ol>
            <li><strong>网关未启用：</strong> 确保在 WooCommerce → 设置 → 支付 → 虎皮椒支付 中启用了该网关</li>
            <li><strong>App ID未配置：</strong> 填写正确的 App ID 和 App Secret</li>
            <li><strong>货币不支持：</strong> 虎皮椒支付仅支持人民币 (CNY/RMB)，请更改商店货币</li>
            <li><strong>插件冲突：</strong> 暂时禁用其他支付插件进行测试</li>
            <li><strong>主题兼容性：</strong> 切换到默认主题 (Storefront/Twenty系列) 进行测试</li>
            <li><strong>缓存问题：</strong> 清除所有缓存（WordPress、WooCommerce、浏览器）</li>
        </ol>
        
        <div class="button-group">
            <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=hupijiao'); ?>" 
               class="button button-primary">前往支付设置</a>
            <button id="force-refresh" class="button">强制刷新网关</button>
            <button id="clear-cache" class="button">清除缓存</button>
            <a href="<?php echo admin_url('admin.php?page=hupijiao-orders'); ?>" 
               class="button">查看订单</a>
        </div>
    </div>
    
    <div class="card">
        <h2>测试支付网关</h2>
        <p>创建一个测试订单检查网关是否正常工作：</p>
        <form method="post">
            <input type="hidden" name="action" value="hupijiao_test_checkout">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hupijiao_test'); ?>">
            
            <table class="form-table">
                <tr>
                    <th>测试金额：</th>
                    <td>
                        <input type="number" name="test_amount" value="0.01" min="0.01" step="0.01">
                        <span class="description">建议使用0.01元进行测试</span>
                    </td>
                </tr>
                <tr>
                    <th>支付方式：</th>
                    <td>
                        <select name="test_payment_type">
                            <option value="alipay">支付宝</option>
                            <option value="wechat">微信支付</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <button type="submit" class="button button-secondary">创建测试订单</button>
        </form>
        <div id="test-result" style="margin-top: 20px;"></div>
    </div>
    
    <div class="card">
        <h2>调试日志</h2>
        <textarea rows="10" style="width: 100%; font-family: monospace;" 
                  placeholder="如果启用了WP_DEBUG，这里会显示相关日志...">
<?php
// 尝试读取最近的错误日志
$debug_log = WP_CONTENT_DIR . '/debug.log';
if (file_exists($debug_log)) {
    $lines = file($debug_log);
    $hupijiao_logs = array();
    foreach ($lines as $line) {
        if (strpos($line, '虎皮椒') !== false) {
            $hupijiao_logs[] = $line;
        }
    }
    if (!empty($hupijiao_logs)) {
        echo implode('', array_slice($hupijiao_logs, -50)); // 显示最后50条相关日志
    }
}
?></textarea>
    </div>
</div>

<style>
.status-indicator {
    padding: 3px 8px;
    border-radius: 3px;
    font-weight: bold;
}

.status-good {
    background-color: #d4edda;
    color: #155724;
}

.status-bad {
    background-color: #f8d7da;
    color: #721c24;
}

.hupijiao-highlight {
    background-color: #fff3cd !important;
}

.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    margin-bottom: 20px;
    padding: 20px;
}

.card h2 {
    margin-top: 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.button-group {
    margin-top: 20px;
}

.button-group .button {
    margin-right: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // 强制刷新网关
    $('#force-refresh').click(function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hupijiao_force_refresh',
                nonce: '<?php echo wp_create_nonce("hupijiao_refresh"); ?>'
            },
            beforeSend: function() {
                $('#force-refresh').text('刷新中...').prop('disabled', true);
            },
            success: function(response) {
                alert(response.data);
                location.reload();
            },
            complete: function() {
                $('#force-refresh').text('强制刷新网关').prop('disabled', false);
            }
        });
    });
    
    // 清除缓存
    $('#clear-cache').click(function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hupijiao_clear_cache',
                nonce: '<?php echo wp_create_nonce("hupijiao_clear_cache"); ?>'
            },
            beforeSend: function() {
                $('#clear-cache').text('清除中...').prop('disabled', true);
            },
            success: function(response) {
                alert(response.data);
            },
            complete: function() {
                $('#clear-cache').text('清除缓存').prop('disabled', false);
            }
        });
    });
    
    // 测试订单
    $('form').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var resultDiv = $('#test-result');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: form.serialize(),
            beforeSend: function() {
                form.find('button').text('创建中...').prop('disabled', true);
                resultDiv.html('<div class="notice notice-info"><p>正在创建测试订单...</p></div>');
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.redirect) {
                        window.open(response.data.redirect, '_blank');
                        resultDiv.html('<div class="notice notice-success"><p>订单创建成功！已在新窗口打开支付页面。</p></div>');
                    } else {
                        resultDiv.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    }
                } else {
                    resultDiv.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function() {
                resultDiv.html('<div class="notice notice-error"><p>请求失败，请检查网络连接</p></div>');
            },
            complete: function() {
                form.find('button').text('创建测试订单').prop('disabled', false);
            }
        });
    });
});
</script>
