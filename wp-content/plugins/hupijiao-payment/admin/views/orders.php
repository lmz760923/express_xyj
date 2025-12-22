<?php
global $wpdb;
$table_name = $wpdb->prefix . 'hupijiao_orders';

// 处理查询
$page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// 构建查询条件
$where = '1=1';
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

if ($search) {
    $where .= $wpdb->prepare(" AND (order_id LIKE %s OR trade_no LIKE %s)", 
        '%' . $wpdb->esc_like($search) . '%',
        '%' . $wpdb->esc_like($search) . '%'
    );
}

// 获取订单总数
$total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");

// 获取订单列表
$orders = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_name WHERE $where ORDER BY id DESC LIMIT %d OFFSET %d",
        $per_page, $offset
    )
);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">支付订单</h1>
    
    <form method="get" action="">
        <input type="hidden" name="page" value="hupijiao-orders">
        <p class="search-box">
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="搜索订单号或交易号">
            <input type="submit" class="button" value="搜索">
        </p>
    </form>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>订单号</th>
                <th>交易号</th>
                <th>金额</th>
                <th>支付方式</th>
                <th>状态</th>
                <th>创建时间</th>
                <th>支付时间</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders): ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo esc_html($order->order_id); ?></td>
                    <td><?php echo esc_html($order->trade_no); ?></td>
                    <td><?php echo number_format($order->amount, 2); ?></td>
                    <td><?php echo esc_html($order->payment_method); ?></td>
                    <td>
                        <span class="status-<?php echo esc_attr($order->status); ?>">
                            <?php 
                            $status_labels = array(
                                'pending' => '待支付',
                                'paid' => '已支付',
                                'failed' => '支付失败'
                            );
                            echo $status_labels[$order->status] ?? $order->status;
                            ?>
                        </span>
                    </td>
                    <td><?php echo $order->created_at; ?></td>
                    <td><?php echo $order->paid_at; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">暂无订单</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php
    // 分页
    if ($total > $per_page) {
        $total_pages = ceil($total / $per_page);
        echo '<div class="tablenav">';
        echo paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'total' => $total_pages,
            'current' => $page
        ));
        echo '</div>';
    }
    ?>
</div>

