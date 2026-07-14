<?php
/**
 * Plugin Name: Manutech Inventory Manager
 * Description: Simple inventory management for products with stock adjustments, low-stock alerts, and movement logs.
 * Version: 1.1.0
 * Author: Manutech
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Manutech_Inventory_Manager
{
    private const TABLE_SUFFIX = 'mt_inventory_items';
    private const LOG_TABLE_SUFFIX = 'mt_inventory_logs';

    public static function init(): void
    {
        register_activation_hook(__FILE__, [self::class, 'activate']);

        add_action('admin_init', [self::class, 'maybe_create_tables']);
        add_action('admin_init', [self::class, 'register_settings']);
        add_action('admin_menu', [self::class, 'register_admin_menu']);
        add_action('admin_bar_menu', [self::class, 'add_admin_bar_entry'], 100);
        add_action('admin_post_mtm_add_item', [self::class, 'handle_add_item']);
        add_action('admin_post_mtm_adjust_stock', [self::class, 'handle_adjust_stock']);
        add_action('admin_post_mtm_delete_item', [self::class, 'handle_delete_item']);
        add_shortcode('mtm_inventory', [self::class, 'render_inventory_shortcode']);
    }

    public static function register_settings(): void
    {
        register_setting('mtm_inventory_settings', 'mtm_inventory_settings');
    }

    public static function activate(): void
    {
        self::maybe_create_tables();
    }

    public static function maybe_create_tables(): void
    {
        global $wpdb;

        $table_name = self::get_table_name();
        $log_table_name = self::get_log_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $items_sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            sku VARCHAR(120) NOT NULL,
            item_name VARCHAR(190) NOT NULL,
            quantity INT NOT NULL DEFAULT 0,
            min_stock INT NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY sku (sku)
        ) {$charset_collate};";

        $logs_sql = "CREATE TABLE {$log_table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            item_id BIGINT UNSIGNED NULL,
            sku VARCHAR(120) NOT NULL,
            item_name VARCHAR(190) NOT NULL,
            action_type VARCHAR(20) NOT NULL,
            adjustment INT NOT NULL,
            new_quantity INT NOT NULL,
            note VARCHAR(190) NOT NULL DEFAULT '',
            user_id BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY item_id (item_id),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta($items_sql);
        dbDelta($logs_sql);
    }

    public static function register_admin_menu(): void
    {
        add_options_page(
            'Inventory Manager',
            'Inventory',
            'manage_options',
            'mtm-inventory',
            [self::class, 'render_admin_page']
            
            
        );
    }

    public static function add_admin_bar_entry(WP_Admin_Bar $admin_bar): void
    {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            return;
        }

        $admin_bar->add_node(
            [
                'id' => 'mtm-inventory',
                'title' => 'Inventory',
                'href' => admin_url('admin.php?page=mtm-inventory'),
                'meta' => ['class' => 'mtm-inventory-shortcut'],
            ]
        );
    }

    public static function render_inventory_shortcode(): string
    {
        if (!is_user_logged_in()) {
            return '<p>Please log in to view inventory.</p>';
        }

        global $wpdb;

        $table_name = self::get_table_name();
        $items = $wpdb->get_results("SELECT sku, item_name, quantity, min_stock, updated_at FROM {$table_name} ORDER BY item_name ASC");

        ob_start();

        echo '<div class="mtm-inventory-shortcode">';
        echo '<h2>Inventory</h2>';

        if (empty($items)) {
            echo '<p>No inventory items available.</p>';
            echo '</div>';
            return (string) ob_get_clean();
        }

        echo '<table style="width:100%;border-collapse:collapse;">';
        echo '<thead><tr><th style="text-align:left;border-bottom:1px solid #ddd;padding:8px;">Name</th><th style="text-align:left;border-bottom:1px solid #ddd;padding:8px;">SKU</th><th style="text-align:left;border-bottom:1px solid #ddd;padding:8px;">Quantity</th><th style="text-align:left;border-bottom:1px solid #ddd;padding:8px;">Status</th><th style="text-align:left;border-bottom:1px solid #ddd;padding:8px;">Updated</th></tr></thead>';
        echo '<tbody>';

        foreach ($items as $item) {
            $is_low_stock = ((int) $item->quantity <= (int) $item->min_stock);
            $status = $is_low_stock ? 'Low stock' : 'In stock';
            $status_color = $is_low_stock ? '#b32d2e' : '#2271b1';

            echo '<tr>';
            echo '<td style="border-bottom:1px solid #f1f1f1;padding:8px;">' . esc_html($item->item_name) . '</td>';
            echo '<td style="border-bottom:1px solid #f1f1f1;padding:8px;">' . esc_html($item->sku) . '</td>';
            echo '<td style="border-bottom:1px solid #f1f1f1;padding:8px;">' . esc_html((string) $item->quantity) . '</td>';
            echo '<td style="border-bottom:1px solid #f1f1f1;padding:8px;color:' . esc_attr($status_color) . ';font-weight:600;">' . esc_html($status) . '</td>';
            echo '<td style="border-bottom:1px solid #f1f1f1;padding:8px;">' . esc_html($item->updated_at) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';

        return (string) ob_get_clean();
    }

    public static function render_admin_page(): void
    {

        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }

        global $wpdb;
        $table_name = self::get_table_name();
        $log_table_name = self::get_log_table_name();

        $items = $wpdb->get_results("SELECT id, sku, item_name, quantity, min_stock, updated_at FROM {$table_name} ORDER BY updated_at DESC");
        $logs = $wpdb->get_results("SELECT sku, item_name, action_type, adjustment, new_quantity, user_id, created_at, note FROM {$log_table_name} ORDER BY id DESC LIMIT 20");

        $notice_code = isset($_GET['mtm_notice']) ? sanitize_text_field(wp_unslash($_GET['mtm_notice'])) : '';
        $notice_map = [
            'added' => ['type' => 'updated', 'text' => 'Item added successfully.'],
            'adjusted' => ['type' => 'updated', 'text' => 'Stock adjusted successfully.'],
            'deleted' => ['type' => 'updated', 'text' => 'Item deleted successfully.'],
            'error' => ['type' => 'error', 'text' => 'Operation failed. Please check your input and try again.'],
        ];

        echo '<div class="wrap">';
        echo '<h1>Inventory Manager</h1>';

        if (isset($notice_map[$notice_code])) {
            $notice = $notice_map[$notice_code];
            echo '<div class="' . esc_attr($notice['type']) . ' notice is-dismissible"><p>' . esc_html($notice['text']) . '</p></div>';
        }

        echo '<h2>Add Item</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        
        //ob_start();
        //settings_fields('mtm_inventory_settings');
        //do_settings_sections('mtm_inventory_settings');
        //wp_nonce_field('mtm_add_item');
        //$ret=ob_get_clean();

        
        //echo '<pre>'.esc_html(admin_url('admin-post.php')).'</pre>';
        //echo '<pre>'.esc_html($ret).'</pre>';

        wp_nonce_field('mtm_add_item');
        echo '<input type="hidden" name="action" value="mtm_add_item" />';
        echo '<table class="form-table" role="presentation">';
        echo '<tr><th><label for="mtm_item_name">Name</label></th><td><input required type="text" id="mtm_item_name" name="item_name" class="regular-text" /></td></tr>';
        echo '<tr><th><label for="mtm_sku">SKU</label></th><td><input required type="text" id="mtm_sku" name="sku" class="regular-text" /></td></tr>';
        echo '<tr><th><label for="mtm_quantity">Initial Quantity</label></th><td><input required type="number" id="mtm_quantity" name="quantity" min="0" step="1" value="0" /></td></tr>';
        echo '<tr><th><label for="mtm_min_stock">Low Stock Threshold</label></th><td><input required type="number" id="mtm_min_stock" name="min_stock" min="0" step="1" value="0" /></td></tr>';
        echo '</table>';
        submit_button('Add Item');
        echo '</form>';

        echo '<hr />';
        echo '<h2>Adjust Stock</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('mtm_adjust_stock');
        echo '<input type="hidden" name="action" value="mtm_adjust_stock" />';
        echo '<table class="form-table" role="presentation">';
        echo '<tr><th><label for="mtm_item_id">Item</label></th><td>';
        echo '<select required id="mtm_item_id" name="item_id">';
        echo '<option value="">Select an item</option>';
        foreach ($items as $item) {
            echo '<option value="' . esc_attr((string) $item->id) . '">' . esc_html($item->item_name . ' (' . $item->sku . ')') . '</option>';
        }
        echo '</select>';
        echo '</td></tr>';
        echo '<tr><th><label for="mtm_adjustment">Adjustment</label></th><td><input required type="number" id="mtm_adjustment" name="adjustment" step="1" /> <p class="description">Use positive numbers for stock-in and negative numbers for stock-out.</p></td></tr>';
        echo '</table>';
        submit_button('Apply Adjustment');
        echo '</form>';

        echo '<hr />';
        echo '<h2>Current Inventory</h2>';

        if (empty($items)) {
            echo '<p>No inventory items yet.</p>';
            echo '</div>';
            return;
        }

        echo '<table class="widefat striped">';
        echo '<thead><tr><th>ID</th><th>Name</th><th>SKU</th><th>Quantity</th><th>Low Stock Threshold</th><th>Status</th><th>Last Updated</th><th>Actions</th></tr></thead>';
        echo '<tbody>';

        foreach ($items as $item) {
            $is_low_stock = ((int) $item->quantity <= (int) $item->min_stock);
            $status = $is_low_stock ? 'Low stock' : 'OK';

            echo '<tr>';
            echo '<td>' . esc_html((string) $item->id) . '</td>';
            echo '<td>' . esc_html($item->item_name) . '</td>';
            echo '<td>' . esc_html($item->sku) . '</td>';
            echo '<td>' . esc_html((string) $item->quantity) . '</td>';
            echo '<td>' . esc_html((string) $item->min_stock) . '</td>';
            echo '<td><strong style="color:' . ($is_low_stock ? '#b32d2e' : '#2271b1') . ';">' . esc_html($status) . '</strong></td>';
            echo '<td>' . esc_html($item->updated_at) . '</td>';
            echo '<td>';
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Delete this item?\');" style="display:inline;">';
            wp_nonce_field('mtm_delete_item_' . $item->id);
            echo '<input type="hidden" name="action" value="mtm_delete_item" />';
            echo '<input type="hidden" name="item_id" value="' . esc_attr((string) $item->id) . '" />';
            submit_button('Delete', 'delete', 'submit', false);
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        echo '<hr />';
        echo '<h2>Recent Stock Movements</h2>';

        if (empty($logs)) {
            echo '<p>No stock movement logs yet.</p>';
            echo '</div>';
            return;
        }

        $operator_names = self::build_operator_map($logs);

        echo '<table class="widefat striped">';
        echo '<thead><tr><th>Time</th><th>Item</th><th>SKU</th><th>Action</th><th>Adjustment</th><th>New Quantity</th><th>Operator</th><th>Note</th></tr></thead>';
        echo '<tbody>';

        foreach ($logs as $log) {
            $operator = '-';
            if ((int) $log->user_id > 0 && isset($operator_names[(int) $log->user_id])) {
                $operator = $operator_names[(int) $log->user_id];
            }
            echo '<tr>';
            echo '<td>' . esc_html($log->created_at) . '</td>';
            echo '<td>' . esc_html($log->item_name) . '</td>';
            echo '<td>' . esc_html($log->sku) . '</td>';
            echo '<td>' . esc_html($log->action_type) . '</td>';
            echo '<td>' . esc_html((string) $log->adjustment) . '</td>';
            echo '<td>' . esc_html((string) $log->new_quantity) . '</td>';
            echo '<td>' . esc_html($operator) . '</td>';
            echo '<td>' . esc_html($log->note) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    public static function handle_add_item(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized request.');
        }

        check_admin_referer('mtm_add_item');

        $item_name = isset($_POST['item_name']) ? sanitize_text_field(wp_unslash($_POST['item_name'])) : '';
        $sku = isset($_POST['sku']) ? sanitize_text_field(wp_unslash($_POST['sku'])) : '';
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;
        $min_stock = isset($_POST['min_stock']) ? (int) $_POST['min_stock'] : 0;

        if ($item_name === '' || $sku === '' || $quantity < 0 || $min_stock < 0) {
            self::redirect_with_notice('error');
        }

        global $wpdb;
        $table_name = self::get_table_name();

        $inserted = $wpdb->insert(
            $table_name,
            [
                'item_name' => $item_name,
                'sku' => $sku,
                'quantity' => $quantity,
                'min_stock' => $min_stock,
            ],
            ['%s', '%s', '%d', '%d']
        );

        if ($inserted === false) {
            self::redirect_with_notice('error');
        }

        $item_id = (int) $wpdb->insert_id;
        self::log_movement($item_id, $sku, $item_name, 'create', $quantity, $quantity, 'Item created');

        self::redirect_with_notice('added');
    }

    public static function handle_adjust_stock(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized request.');
        }

        check_admin_referer('mtm_adjust_stock');

        $item_id = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;
        $adjustment = isset($_POST['adjustment']) ? (int) $_POST['adjustment'] : 0;

        if ($item_id <= 0 || $adjustment === 0) {
            self::redirect_with_notice('error');
        }

        global $wpdb;
        $table_name = self::get_table_name();

        $item_row = $wpdb->get_row($wpdb->prepare("SELECT sku, item_name, quantity FROM {$table_name} WHERE id = %d", $item_id));

        if ($item_row === null) {
            self::redirect_with_notice('error');
        }

        $current_qty = (int) $item_row->quantity;
        $new_qty = $current_qty + $adjustment;
        if ($new_qty < 0) {
            self::redirect_with_notice('error');
        }

        $updated = $wpdb->update(
            $table_name,
            ['quantity' => $new_qty],
            ['id' => $item_id],
            ['%d'],
            ['%d']
        );

        if ($updated === false) {
            self::redirect_with_notice('error');
        }

        $action_type = $adjustment > 0 ? 'stock_in' : 'stock_out';
        self::log_movement(
            $item_id,
            (string) $item_row->sku,
            (string) $item_row->item_name,
            $action_type,
            $adjustment,
            $new_qty,
            'Manual adjustment'
        );

        self::redirect_with_notice('adjusted');
    }

    public static function handle_delete_item(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized request.');
        }

        $item_id = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;
        if ($item_id <= 0) {
            self::redirect_with_notice('error');
        }

        check_admin_referer('mtm_delete_item_' . $item_id);

        global $wpdb;
        $table_name = self::get_table_name();
        $item_row = $wpdb->get_row($wpdb->prepare("SELECT sku, item_name, quantity FROM {$table_name} WHERE id = %d", $item_id));

        if ($item_row === null) {
            self::redirect_with_notice('error');
        }

        $deleted = $wpdb->delete(
            $table_name,
            ['id' => $item_id],
            ['%d']
        );

        if ($deleted === false) {
            self::redirect_with_notice('error');
        }

        self::log_movement(
            $item_id,
            (string) $item_row->sku,
            (string) $item_row->item_name,
            'delete',
            -1 * (int) $item_row->quantity,
            0,
            'Item deleted'
        );

        self::redirect_with_notice('deleted');
    }

    private static function get_table_name(): string
    {
        global $wpdb;

        return $wpdb->prefix . self::TABLE_SUFFIX;
    }

    private static function get_log_table_name(): string
    {
        global $wpdb;

        return $wpdb->prefix . self::LOG_TABLE_SUFFIX;
    }

    private static function log_movement(
        int $item_id,
        string $sku,
        string $item_name,
        string $action_type,
        int $adjustment,
        int $new_quantity,
        string $note
    ): void {
        global $wpdb;

        $wpdb->insert(
            self::get_log_table_name(),
            [
                'item_id' => $item_id,
                'sku' => $sku,
                'item_name' => $item_name,
                'action_type' => $action_type,
                'adjustment' => $adjustment,
                'new_quantity' => $new_quantity,
                'note' => $note,
                'user_id' => get_current_user_id(),
            ],
            ['%d', '%s', '%s', '%s', '%d', '%d', '%s', '%d']
        );
    }

    private static function build_operator_map(array $logs): array
    {
        $user_ids = [];

        foreach ($logs as $log) {
            $user_id = (int) $log->user_id;
            if ($user_id > 0) {
                $user_ids[$user_id] = $user_id;
            }
        }

        if (empty($user_ids)) {
            return [];
        }

        $users = get_users(
            [
                'include' => array_values($user_ids),
                'fields' => ['ID', 'display_name', 'user_login'],
                'number' => count($user_ids),
            ]
        );

        $map = [];
        foreach ($users as $user) {
            $name = $user->display_name !== '' ? $user->display_name : $user->user_login;
            $map[(int) $user->ID] = $name;
        }

        return $map;
    }

    private static function redirect_with_notice(string $code): void
    {
        $url = add_query_arg(
            ['page' => 'mtm-inventory', 'mtm_notice' => $code],
            admin_url('admin.php')
        );

        wp_safe_redirect($url);
        exit;
    }
}

Manutech_Inventory_Manager::init();

add_filter( 'woocommerce_product_editor_enabled', '__return_false' );
