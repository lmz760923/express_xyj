<?php
/*
Plugin Name: 用户数据收集插件

Description: 收集用户名、邮箱等数据并保存到数据库
Version: 1.0.0
Author: mz.li
License: GPL v2 or later
Text Domain: user-data-collector
*/

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

class UserDataCollector {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'user_submissions';
        
        // 注册激活和卸载钩子
        register_activation_hook(__FILE__, array($this, 'create_table'));
        register_uninstall_hook(__FILE__, array('UserDataCollector', 'delete_table'));
        
        // 添加短代码
        add_shortcode('user_data_form', array($this, 'display_form'));
    
        // 后台CSV导出
        add_action('admin_post_udc_export_csv', array($this, 'export_to_csv'));
        
        // 添加管理菜单
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // 加载文本域
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // 注册AJAX处理
        add_action('wp_ajax_submit_user_form', array($this, 'ajax_handle_form'));
        add_action('wp_ajax_nopriv_submit_user_form', array($this, 'ajax_handle_form'));
    }
    
    // 加载文本域
    public function load_textdomain() {
        load_plugin_textdomain('user-data-collector', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    // 创建数据库表
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            company varchar(100) DEFAULT NULL,
            message text DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // 添加示例数据（可选）
        $this->insert_sample_data();
    }
    
    // 插入示例数据
    private function insert_sample_data() {
        global $wpdb;
        
        $sample_data = array(
            array(
                'name' => '张三',
                'email' => 'zhangsan@example.com',
                'phone' => '13800138000',
                'company' => '示例公司',
                'message' => '这是一个示例提交'
            ),
            array(
                'name' => '李四',
                'email' => 'lisi@example.com',
                'phone' => '13900139000',
                'company' => '测试公司',
                'message' => '另一个示例'
            )
        );
        
        foreach ($sample_data as $data) {
            $wpdb->insert(
                $this->table_name,
                $data
            );
        }
    }
    
    // 显示表单
    public function display_form($atts) {
        // 获取短代码属性
        $atts = shortcode_atts(array(
            'show_phone' => 'true',
            'show_company' => 'true',
            'show_message' => 'true',
            'submit_text' => __('提交表单', 'user-data-collector')
        ), $atts, 'user_data_form');
        
            
        // 生成表单HTML
        ob_start();
        ?>
        <div class="user-data-form-container">
            
            <form id="user-data-form">
                <?php wp_nonce_field('submit_user_data_action', 'user_data_nonce'); ?>
                <input type="hidden" name="action" value="submit_user_data">
                
                <div class="form-group">
                    <label for="name"><?php _e('姓名 *', 'user-data-collector'); ?></label>
                    <input type="text" id="name" name="name" required 
                           placeholder="<?php esc_attr_e('请输入您的姓名', 'user-data-collector'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email"><?php _e('邮箱 *', 'user-data-collector'); ?></label>
                    <input type="email" id="email" name="email" required 
                           placeholder="<?php esc_attr_e('请输入您的邮箱', 'user-data-collector'); ?>">
                </div>
                
                <?php if ($atts['show_phone'] === 'true') : ?>
                <div class="form-group">
                    <label for="phone"><?php _e('电话', 'user-data-collector'); ?></label>
                    <input type="tel" id="phone" name="phone" 
                           placeholder="<?php esc_attr_e('请输入您的电话', 'user-data-collector'); ?>">
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_company'] === 'true') : ?>
                <div class="form-group">
                    <label for="company"><?php _e('公司', 'user-data-collector'); ?></label>
                    <input type="text" id="company" name="company" 
                           placeholder="<?php esc_attr_e('请输入您的公司名称', 'user-data-collector'); ?>">
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_message'] === 'true') : ?>
                <div class="form-group">
                    <label for="message"><?php _e('留言', 'user-data-collector'); ?></label>
                    <textarea id="message" name="message" rows="4" 
                              placeholder="<?php esc_attr_e('请输入您的留言', 'user-data-collector'); ?>"></textarea>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <button type="submit" class="submit-btn"><?php echo esc_html($atts['submit_text']); ?></button>
                </div>
            </form>
        </div>
        
        <style>
        .user-data-form-container {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #0073aa;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,115,170,0.2);
        }
        .submit-btn {
            background: #0073aa;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .submit-btn:hover {
            background: #005a87;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // AJAX表单提交
            $('#user-data-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                var submitBtn = $(this).find('.submit-btn');
                
                // 显示加载状态
                submitBtn.prop('disabled', true).text('<?php _e("提交中...", "user-data-collector"); ?>');
                
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    data: formData + '&action=submit_user_form',
                    success: function(response) {
                        if (response.success) {
                            // 清空表单
                            $('#user-data-form')[0].reset();
                            // 显示成功消息
                            $('.user-data-form-container').prepend(
                                '<div class="success-message">' + response.data.message + '</div>'
                            );
                            // 3秒后移除消息
                            setTimeout(function() {
                                $('.success-message').fadeOut();
                            }, 3000);
                        } else {
                            // 显示错误消息
                            $('.user-data-form-container').prepend(
                                '<div class="error-message">' + response.data.message + '</div>'
                            );
                            // 3秒后移除消息
                            setTimeout(function() {
                                $('.error-message').fadeOut();
                            }, 3000);
                        }
                        submitBtn.prop('disabled', false).text('<?php echo esc_js($atts['submit_text']); ?>');
                    },
                    error: function() {
                        $('.user-data-form-container').prepend(
                            '<div class="error-message"><?php _e("网络错误，请重试。", "user-data-collector"); ?></div>'
                        );
                        // 3秒后移除消息
                            setTimeout(function() {
                                $('.error-message').fadeOut();
                            }, 3000);
                        submitBtn.prop('disabled', false).text('<?php echo esc_js($atts['submit_text']); ?>');
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    
    // AJAX处理表单
    public function ajax_handle_form() {
        // 验证nonce
        if (!isset($_POST['user_data_nonce']) || 
            !wp_verify_nonce($_POST['user_data_nonce'], 'submit_user_data_action')) {
            wp_send_json_error(array('message' => __('安全验证失败', 'user-data-collector')));
        }
        
        // 验证数据
        if (empty($_POST['name']) || empty($_POST['email'])) {
            wp_send_json_error(array('message' => __('姓名和邮箱为必填项', 'user-data-collector')));
        }
        
        // 验证邮箱格式
        if (!is_email($_POST['email'])) {
            wp_send_json_error(array('message' => __('邮箱格式不正确', 'user-data-collector')));
        }
        
        // 清理数据
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $company = isset($_POST['company']) ? sanitize_text_field($_POST['company']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
        
        // 获取用户IP和浏览器信息
        $ip_address = $this->get_user_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        
        // 保存到数据库
        $result = $this->save_submission(array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'company' => $company,
            'message' => $message,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent
        ));
        
        if ($result) {
            // 发送通知邮件
            $this->send_notification_email($name, $email, $phone, $company, $message);
            
            wp_send_json_success(array('message' => __('表单提交成功！', 'user-data-collector')));
        } else {
            wp_send_json_error(array('message' => __('提交失败，请重试。', 'user-data-collector')));
        }
    }
    
    // 保存提交数据
    private function save_submission($data) {
        global $wpdb;
        
        // 检查邮箱是否已存在
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE email = %s",
            $data['email']
        ));
        
        if ($existing) {
            // 如果已存在，更新记录
            return $wpdb->update(
                $this->table_name,
                $data,
                array('id' => $existing)
            );
        } else {
            // 如果不存在，插入新记录
            return $wpdb->insert(
                $this->table_name,
                $data
            );
        }
    }
    
    // 获取用户IP
    private function get_user_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field($ip);
    }
    
    // 发送通知邮件
    private function send_notification_email($name, $email, $phone, $company, $message) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf(__('新表单提交 - %s', 'user-data-collector'), $site_name);
        
        $body = __('您收到一个新的表单提交：', 'user-data-collector') . " ";
        $body .= __('姓名：', 'user-data-collector') . $name . " ";
        $body .= __('邮箱：', 'user-data-collector') . $email . " ";
        
        if ($phone) {
            $body .= __('电话：', 'user-data-collector') . $phone . " ";
        }
        
        if ($company) {
            $body .= __('公司：', 'user-data-collector') . $company . " ";
        }
        
        if ($message) {
            $body .= __('留言：', 'user-data-collector') . " " . $message . " ";
        }
        
        $body .= " " . __('提交时间：', 'user-data-collector') . current_time('mysql');
        
        wp_mail($admin_email, $subject, $body);
    }
    
    // 添加管理菜单
    public function add_admin_menu() {
        add_menu_page(
            __('用户提交数据', 'user-data-collector'),
            __('用户数据', 'user-data-collector'),
            'manage_options',
            'user-submissions',
            array($this, 'display_admin_page'),
            'dashicons-list-view',
            30
        );
    }
    
    // 显示管理页面
    public function display_admin_page() {
        global $wpdb;
        
        // 处理删除请求
        if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
            $this->delete_submission(intval($_GET['id']));
        }
        
        // 获取所有提交
        $submissions = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY created_at DESC");
        
        ?>
        <div class="wrap">
            <h1><?php _e('用户提交数据', 'user-data-collector'); ?></h1>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=udc_export_csv'), 'user_data_export_csv')); ?>" class="button">
                        <?php _e('导出为CSV', 'user-data-collector'); ?>
                    </a>
                </div>
                <br class="clear">
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'user-data-collector'); ?></th>
                        <th><?php _e('姓名', 'user-data-collector'); ?></th>
                        <th><?php _e('邮箱', 'user-data-collector'); ?></th>
                        <th><?php _e('电话', 'user-data-collector'); ?></th>
                        <th><?php _e('公司', 'user-data-collector'); ?></th>
                        <th><?php _e('留言', 'user-data-collector'); ?></th>
                        <th><?php _e('IP地址', 'user-data-collector'); ?></th>
                        <th><?php _e('提交时间', 'user-data-collector'); ?></th>
                        <th><?php _e('操作', 'user-data-collector'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($submissions) : ?>
                        <?php foreach ($submissions as $submission) : ?>
                        <tr>
                            <td><?php echo esc_html($submission->id); ?></td>
                            <td><?php echo esc_html($submission->name); ?></td>
                            <td><?php echo esc_html($submission->email); ?></td>
                            <td><?php echo esc_html($submission->phone); ?></td>
                            <td><?php echo esc_html($submission->company); ?></td>
                            <td><?php echo esc_html(wp_trim_words($submission->message, 10)); ?></td>
                            <td><?php echo esc_html($submission->ip_address); ?></td>
                            <td><?php echo esc_html($submission->created_at); ?></td>
                            <td>
                                <a href="<?php echo add_query_arg(array('action' => 'delete', 'id' => $submission->id)); ?>" 
                                   class="button button-small" 
                                   onclick="return confirm('<?php _e('确定要删除这条记录吗？', 'user-data-collector'); ?>')">
                                    <?php _e('删除', 'user-data-collector'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="9"><?php _e('暂无数据', 'user-data-collector'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <style>
        .wp-list-table th, .wp-list-table td {
            padding: 10px;
        }
        </style>
        <?php
    }
    
    // 删除提交
    private function delete_submission($id) {
        global $wpdb;
        return $wpdb->delete($this->table_name, array('id' => $id));
    }
    
    // 导出为CSV
    public function export_to_csv() {
        global $wpdb;

        // 安全校验：仅管理员可导出，且必须通过nonce验证
        if (!current_user_can('manage_options')) {
            wp_die(__('您没有权限执行此操作', 'user-data-collector'));
        }

        check_admin_referer('user_data_export_csv');

        if (ob_get_length()) {
            ob_clean();
        }
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=user-submissions-' . date('Y-m-d') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');

        if (!$output) {
            wp_die(__('导出失败：无法创建输出流', 'user-data-collector'));
        }
        
        // 添加BOM以支持Excel中的中文
        fwrite($output, "\xEF\xBB\xBF");
        
        // 写入表头
        fputcsv($output, array(
            __('ID', 'user-data-collector'),
            __('姓名', 'user-data-collector'),
            __('邮箱', 'user-data-collector'),
            __('电话', 'user-data-collector'),
            __('公司', 'user-data-collector'),
            __('留言', 'user-data-collector'),
            __('IP地址', 'user-data-collector'),
            __('提交时间', 'user-data-collector')
        ));
        
        // 获取数据
        $submissions = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY created_at DESC");
        
        // 写入数据
        foreach ($submissions as $submission) {
            fputcsv($output, array(
                $submission->id,
                $submission->name,
                $submission->email,
                $submission->phone,
                $submission->company,
                $submission->message,
                $submission->ip_address,
                $submission->created_at
            ));
        }
        
        fclose($output);
        exit;
    }
    
    // 删除表（卸载时）
    public static function delete_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_submissions';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}

// 初始化插件
new UserDataCollector();