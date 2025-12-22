<?php
/**
 * 虎皮椒支付快速测试脚本
 * 访问：http://yoursite.com/test-hupijiao.php
 * 测试完成后删除此文件
 */

define('WP_USE_THEMES', false);
require_once('./wp-load.php');

// 只允许管理员访问
if (!current_user_can('manage_options')) {
    die('权限不足');
}

echo '<h1>虎皮椒支付测试</h1>';

// 1. 检查网关
if (class_exists('Hupijiao_WC_Gateway')) {
    echo '<p style="color:green;">✓ 网关类已加载</p>';
    
    // 实例化网关
    $gateway = new Hupijiao_WC_Gateway();
    
    echo '<p>网关ID: ' . $gateway->id . '</p>';
    echo '<p>网关标题: ' . $gateway->title . '</p>';
    echo '<p>启用状态: ' . $gateway->enabled . '</p>';
    echo '<p>App ID: ' . (empty($gateway->app_id) ? '未设置' : '已设置') . '</p>';
    
    // 测试 is_available()
    echo '<h2>测试 is_available()</h2>';
    $available = $gateway->is_available();
    echo '<p>网关可用性: ' . ($available ? '可用' : '不可用') . '</p>';
    
} else {
    echo '<p style="color:red;">✗ 网关类未加载</p>';
}

// 2. 检查WooCommerce支付网关
echo '<h2>WooCommerce支付网关列表</h2>';
if (class_exists('WC_Payment_Gateways')) {
    $wc_gateways = new WC_Payment_Gateways();
    $gateways = $wc_gateways->payment_gateways();
    
    echo '<ul>';
    foreach ($gateways as $id => $gateway) {
        echo '<li>';
        echo 'ID: ' . $id . '<br>';
        echo '名称: ' . $gateway->title . '<br>';
        echo '启用: ' . $gateway->enabled . '<br>';
        echo '类名: ' . get_class($gateway);
        echo '</li>';
    }
    echo '</ul>';
}

// 3. 检查货币
echo '<h2>商店设置</h2>';
echo '<p>货币: ' . get_woocommerce_currency() . '</p>';
echo '<p>货币位置: ' . get_option('woocommerce_currency_pos') . '</p>';

// 4. 创建测试产品
echo '<h2>创建测试产品</h2>';
$product = new WC_Product_Simple();
$product->set_name('虎皮椒测试产品');
$product->set_price(0.01);
$product->set_regular_price(0.01);
$product->save();

echo '<p>测试产品已创建: <a href="' . $product->get_permalink() . '">' . $product->get_name() . '</a></p>';
echo '<p>价格: ' . $product->get_price() . '</p>';

// 5. 创建测试订单
echo '<h2>创建测试订单</h2>';
$order = wc_create_order(array(
    'status' => 'pending',
    'customer_id' => get_current_user_id(),
));

$order->add_product($product, 1);
$order->calculate_totals();

echo '<p>测试订单已创建: #' . $order->get_id() . '</p>';
echo '<p>订单总额: ' . $order->get_total() . '</p>';

// 6. 测试支付流程
echo '<h2>测试支付流程</h2>';
echo '<form method="post">';
echo '<input type="hidden" name="order_id" value="' . $order->get_id() . '">';
echo '<button type="submit" name="test_payment">测试支付</button>';
echo '</form>';

if (isset($_POST['test_payment'])) {
    echo '<h3>支付测试结果</h3>';
    
    // 设置支付方式
    $order->set_payment_method('hupijiao');
    $order->save();
    
    // 获取支付URL
    $result = $gateway->process_payment($order->get_id());
    
    if ($result['result'] === 'success') {
        echo '<p style="color:green;">✓ 支付创建成功</p>';
        echo '<p>重定向URL: ' . $result['redirect'] . '</p>';
        echo '<p><a href="' . $result['redirect'] . '" target="_blank">点击测试支付</a></p>';
    } else {
        echo '<p style="color:red;">✗ 支付创建失败</p>';
        if (isset($result['messages'])) {
            echo '<p>错误信息: ' . $result['messages'] . '</p>';
        }
    }
}

echo '<hr>';
echo '<p><a href="' . admin_url('admin.php?page=hupijiao-debug') . '">查看更多调试信息</a></p>';
echo '<p><a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=hupijiao') . '">前往支付设置</a></p>';
