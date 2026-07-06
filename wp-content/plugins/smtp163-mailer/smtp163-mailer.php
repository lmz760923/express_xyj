<?php
/*
Plugin Name: SMTP 163 Mailer
Description: Pure PHP SMTP plugin with admin setup UI, optimized for 163 mailbox.
Version: 1.0.0
Author: mz.li
*/

if (!defined('ABSPATH')) {
    exit;
}

const SMTP163_OPTION_KEY = 'smtp163_settings';
const SMTP163_LOG_OPTION_KEY = 'smtp163_mail_logs';

function smtp163_default_settings() {
    return array(
        'enabled' => 1,
        'host' => 'smtp.163.com',
        'port' => 465,
        'secure' => 'ssl',
        'auth' => 1,
        'username' => '',
        'password' => '',
        'from_email' => '',
        'from_name' => get_bloginfo('name'),
        'force_from' => 1,
        'timeout' => 15,
    );
}

function smtp163_get_settings() {
    $saved = get_option(SMTP163_OPTION_KEY, array());
    if (!is_array($saved)) {
        $saved = array();
    }

    return wp_parse_args($saved, smtp163_default_settings());
}

function smtp163_sanitize_settings($input) {
    $defaults = smtp163_default_settings();

    $out = array();
    $out['enabled'] = isset($input['enabled']) ? 1 : 0;
    $out['host'] = sanitize_text_field($input['host'] ?? $defaults['host']);
    $out['port'] = max(1, min(65535, intval($input['port'] ?? $defaults['port'])));

    $secure = sanitize_text_field($input['secure'] ?? $defaults['secure']);
    $allowed_secure = array('none', 'ssl', 'tls');
    $out['secure'] = in_array($secure, $allowed_secure, true) ? $secure : $defaults['secure'];

    $out['auth'] = isset($input['auth']) ? 1 : 0;
    $out['username'] = sanitize_email($input['username'] ?? '');

    // Password should keep original text to avoid breaking SMTP auth.
    $new_password = isset($input['password']) ? trim(wp_unslash($input['password'])) : '';
    $old_settings = smtp163_get_settings();
    if ($new_password === '') {
        $out['password'] = $old_settings['password'] ?? '';
    } else {
        $out['password'] = $new_password;
    }

    $out['from_email'] = sanitize_email($input['from_email'] ?? '');
    $out['from_name'] = sanitize_text_field($input['from_name'] ?? $defaults['from_name']);
    $out['force_from'] = isset($input['force_from']) ? 1 : 0;
    $out['timeout'] = max(5, min(120, intval($input['timeout'] ?? $defaults['timeout'])));

    if (empty($out['host'])) {
        $out['host'] = $defaults['host'];
    }

    if (empty($out['from_email']) && !empty($out['username'])) {
        $out['from_email'] = $out['username'];
    }

    return $out;
}

function smtp163_register_settings() {
    register_setting(
        'smtp163_settings_group',
        SMTP163_OPTION_KEY,
        array(
            'type' => 'array',
            'sanitize_callback' => 'smtp163_sanitize_settings',
            'default' => smtp163_default_settings(),
        )
    );
}
add_action('admin_init', 'smtp163_register_settings');

function smtp163_get_logs() {
    $logs = get_option(SMTP163_LOG_OPTION_KEY, array());
    if (!is_array($logs)) {
        return array();
    }

    return $logs;
}

function smtp163_add_log($level, $message, $context = array()) {
    $logs = smtp163_get_logs();

    array_unshift($logs, array(
        'time' => current_time('mysql'),
        'level' => sanitize_text_field($level),
        'message' => sanitize_text_field($message),
        'context' => wp_json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ));

    // Keep recent logs only.
    $logs = array_slice($logs, 0, 100);
    update_option(SMTP163_LOG_OPTION_KEY, $logs, false);
}

function smtp163_clear_logs() {
    update_option(SMTP163_LOG_OPTION_KEY, array(), false);
}

function smtp163_connection_hint($reason) {
    $map = array(
        'config' => '配置无效：请检查 SMTP 主机和端口。',
        'dns' => '域名解析失败：请检查 SMTP 主机名是否正确。',
        'timeout' => '连接超时：请检查服务器防火墙或端口是否放行。',
        'refused' => '连接被拒绝：端口可能未开放或被服务端拒绝。',
        'ssl' => 'SSL/TLS 握手失败：请检查加密方式与端口是否匹配。',
        'starttls' => 'STARTTLS 升级失败：请尝试 SSL(465) 或检查服务器 TLS 支持。',
        'unknown' => '未知错误：请查看日志中的 errno/error 详情。',
    );

    return $map[$reason] ?? $map['unknown'];
}

function smtp163_detect_conn_reason($errno, $errstr) {
    $text = strtolower((string) $errstr);

    if ($errno === 0 && $text === '') {
        return 'unknown';
    }

    if (strpos($text, 'getaddrinfo') !== false || strpos($text, 'name or service not known') !== false) {
        return 'dns';
    }

    if (strpos($text, 'timed out') !== false || intval($errno) === 110) {
        return 'timeout';
    }

    if (strpos($text, 'refused') !== false || intval($errno) === 111 || intval($errno) === 10061) {
        return 'refused';
    }

    if (strpos($text, 'ssl') !== false || strpos($text, 'tls') !== false || strpos($text, 'certificate') !== false) {
        return 'ssl';
    }

    return 'unknown';
}

function smtp163_read_smtp_response($fp) {
    $text = '';
    $code = 0;

    for ($i = 0; $i < 8; $i++) {
        $line = fgets($fp, 512);
        if ($line === false) {
            break;
        }

        $text .= $line;
        if (preg_match('/^(\d{3})([\s-])/', $line, $m)) {
            $code = intval($m[1]);
            if ($m[2] === ' ') {
                break;
            }
        } else {
            break;
        }
    }

    return array($code, trim($text));
}

function smtp163_admin_menu() {
    add_options_page(
        'SMTP 163 Setup',
        'SMTP 163',
        'manage_options',
        'smtp163-mailer',
        'smtp163_render_admin_page'
    );
}
add_action('admin_menu', 'smtp163_admin_menu');

function smtp163_render_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = smtp163_get_settings();
    $masked_password = empty($settings['password']) ? '' : '********';
    ?>
    <div class="wrap">
        <h1>SMTP 163 Setup</h1>
        <p>针对 163 邮箱默认使用 smtp.163.com + SSL(465)。密码请填写 163 邮箱授权码，不是登录密码。</p>

        <?php if (isset($_GET['smtp163_test']) && $_GET['smtp163_test'] === 'ok') : ?>
            <div class="notice notice-success is-dismissible"><p>测试邮件发送成功。</p></div>
        <?php elseif (isset($_GET['smtp163_test']) && $_GET['smtp163_test'] === 'fail') : ?>
            <div class="notice notice-error"><p>测试邮件发送失败，请检查 SMTP 配置。</p></div>
        <?php endif; ?>

        <?php if (isset($_GET['smtp163_conn']) && $_GET['smtp163_conn'] === 'ok') : ?>
            <div class="notice notice-success is-dismissible"><p>SMTP 连接测试成功（仅连接，不发信）。</p></div>
        <?php elseif (isset($_GET['smtp163_conn']) && $_GET['smtp163_conn'] === 'fail') : ?>
            <?php $reason = sanitize_text_field($_GET['smtp163_conn_reason'] ?? 'unknown'); ?>
            <div class="notice notice-error"><p>SMTP 连接测试失败：<?php echo esc_html(smtp163_connection_hint($reason)); ?></p></div>
        <?php endif; ?>

        <?php if (isset($_GET['smtp163_log']) && $_GET['smtp163_log'] === 'cleared') : ?>
            <div class="notice notice-success is-dismissible"><p>邮件日志已清空。</p></div>
        <?php endif; ?>

        <?php if (isset($_GET['smtp163_preset']) && $_GET['smtp163_preset'] === 'applied') : ?>
            <div class="notice notice-success is-dismissible"><p>已应用 163 推荐配置（smtp.163.com / 465 / SSL）。</p></div>
        <?php endif; ?>

        <form method="post" action="options.php" autocomplete="off">
            <?php settings_fields('smtp163_settings_group'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">启用 SMTP</th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr(SMTP163_OPTION_KEY); ?>[enabled]" value="1" <?php checked(1, intval($settings['enabled'])); ?> />
                            使用 SMTP 发送邮件
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">SMTP 主机</th>
                    <td>
                        <input type="text" class="regular-text" name="<?php echo esc_attr(SMTP163_OPTION_KEY); ?>[host]" value="<?php echo esc_attr($settings['host']); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">端口</th>
                    <td>
                        <input type="number" min="1" max="65535" name="<?php echo esc_attr(SMTP163_OPTION_KEY); ?>[port]" value="<?php echo esc_attr($settings['port']); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">加密方式</th>
                    <td>
                        <select name="<?php echo esc_attr(SMTP163_OPTION_KEY); ?>[secure]">
                            <option value="ssl" <?php selected($settings['secure'], 'ssl'); ?>>SSL</option>
                            <option value="tls" <?php selected($settings['secure'], 'tls'); ?>>TLS</option>
                            <option value="none" <?php selected($settings['secure'], 'none'); ?>>无</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">SMTP 认证</th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr(SMTP163_OPTION_KEY); ?>[auth]" value="1" <?php checked(1, intval($settings['auth'])); ?> />
                            需要账号密码认证
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">163 邮箱账号</th>
                    <td>
                        <input type="email" class="regular-text" name="<?php echo esc_attr(SMTP163_OPTION_KEY); ?>[username]" value="<?php echo esc_attr($settings['username']); ?>" placeholder="name@163.com" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">授权码</th>
                    <td>
                        <input type="password" class="regular-text" name="<?php echo esc_attr(SMTP163_OPTION_KEY); ?>[password]" value="" placeholder="<?php echo esc_attr($masked_password); ?>" />
                        <p class="description">留空表示保持原有授权码不变。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">发件人邮箱</th>
                    <td>
                        <input type="email" class="regular-text" name="<?php echo esc_attr(SMTP163_OPTION_KEY); ?>[from_email]" value="<?php echo esc_attr($settings['from_email']); ?>" placeholder="name@163.com" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">发件人名称</th>
                    <td>
                        <input type="text" class="regular-text" name="<?php echo esc_attr(SMTP163_OPTION_KEY); ?>[from_name]" value="<?php echo esc_attr($settings['from_name']); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">强制覆盖发件人</th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr(SMTP163_OPTION_KEY); ?>[force_from]" value="1" <?php checked(1, intval($settings['force_from'])); ?> />
                            覆盖所有 wp_mail() 的 From 信息
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">超时(秒)</th>
                    <td>
                        <input type="number" min="5" max="120" name="<?php echo esc_attr(SMTP163_OPTION_KEY); ?>[timeout]" value="<?php echo esc_attr($settings['timeout']); ?>" />
                    </td>
                </tr>
            </table>

            <?php submit_button('保存配置'); ?>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:8px;">
            <?php wp_nonce_field('smtp163_apply_163_preset', 'smtp163_apply_163_preset_nonce'); ?>
            <input type="hidden" name="action" value="smtp163_apply_163_preset" />
            <?php submit_button('一键应用 163 推荐配置', 'secondary', 'submit', false); ?>
        </form>

        <hr />

        <h2>发送测试邮件</h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('smtp163_send_test_mail', 'smtp163_test_nonce'); ?>
            <input type="hidden" name="action" value="smtp163_send_test_mail" />
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">收件人邮箱</th>
                    <td>
                        <input type="email" class="regular-text" name="smtp163_test_to" value="<?php echo esc_attr(get_option('admin_email')); ?>" required />
                    </td>
                </tr>
            </table>
            <?php submit_button('发送测试邮件', 'secondary'); ?>
        </form>

        <h2>SMTP 连接测试</h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('smtp163_test_connection', 'smtp163_conn_nonce'); ?>
            <input type="hidden" name="action" value="smtp163_test_connection" />
            <p>此测试仅验证 SMTP 服务器连通性和端口可用性，不会发送邮件。</p>
            <?php submit_button('测试 SMTP 连接', 'secondary'); ?>
        </form>

        <hr />

        <h2>最近邮件日志</h2>
        <?php $logs = smtp163_get_logs(); ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:12px;">
            <?php wp_nonce_field('smtp163_clear_logs', 'smtp163_clear_logs_nonce'); ?>
            <input type="hidden" name="action" value="smtp163_clear_logs" />
            <?php submit_button('清空日志', 'delete', 'submit', false); ?>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:12px;">
            <?php wp_nonce_field('smtp163_export_logs', 'smtp163_export_logs_nonce'); ?>
            <input type="hidden" name="action" value="smtp163_export_logs" />
            <?php submit_button('导出日志 CSV', 'secondary', 'submit', false); ?>
        </form>

        <?php if (empty($logs)) : ?>
            <p>暂无日志。</p>
        <?php else : ?>
            <table class="widefat striped" style="max-width:1200px;">
                <thead>
                    <tr>
                        <th style="width:180px;">时间</th>
                        <th style="width:100px;">级别</th>
                        <th>消息</th>
                        <th>上下文</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) : ?>
                        <tr>
                            <td><?php echo esc_html($log['time'] ?? ''); ?></td>
                            <td><?php echo esc_html(strtoupper($log['level'] ?? 'info')); ?></td>
                            <td><?php echo esc_html($log['message'] ?? ''); ?></td>
                            <td><code><?php echo esc_html($log['context'] ?? ''); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

function smtp163_send_test_mail() {
    if (!current_user_can('manage_options')) {
        wp_die('Permission denied.');
    }

    check_admin_referer('smtp163_send_test_mail', 'smtp163_test_nonce');

    $to = isset($_POST['smtp163_test_to']) ? sanitize_email(wp_unslash($_POST['smtp163_test_to'])) : '';
    if (!is_email($to)) {
        wp_safe_redirect(add_query_arg(array('page' => 'smtp163-mailer', 'smtp163_test' => 'fail'), admin_url('options-general.php')));
        exit;
    }

    $subject = 'SMTP 163 Test Mail - ' . wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $message = "This is a test email sent by SMTP 163 Mailer plugin.\n\nTime: " . current_time('mysql');

    $sent = wp_mail($to, $subject, $message);

    wp_safe_redirect(add_query_arg(array(
        'page' => 'smtp163-mailer',
        'smtp163_test' => $sent ? 'ok' : 'fail',
    ), admin_url('options-general.php')));
    exit;
}
add_action('admin_post_smtp163_send_test_mail', 'smtp163_send_test_mail');

function smtp163_apply_163_preset() {
    if (!current_user_can('manage_options')) {
        wp_die('Permission denied.');
    }

    check_admin_referer('smtp163_apply_163_preset', 'smtp163_apply_163_preset_nonce');

    $settings = smtp163_get_settings();
    $settings['host'] = 'smtp.163.com';
    $settings['port'] = 465;
    $settings['secure'] = 'ssl';
    $settings['auth'] = 1;
    $settings['timeout'] = 15;
    $settings['force_from'] = 1;

    if (empty($settings['from_email']) && !empty($settings['username'])) {
        $settings['from_email'] = $settings['username'];
    }

    update_option(SMTP163_OPTION_KEY, smtp163_sanitize_settings($settings));
    smtp163_add_log('info', 'Applied 163 SMTP preset', array('host' => 'smtp.163.com', 'port' => 465, 'secure' => 'ssl'));

    wp_safe_redirect(add_query_arg(array('page' => 'smtp163-mailer', 'smtp163_preset' => 'applied'), admin_url('options-general.php')));
    exit;
}
add_action('admin_post_smtp163_apply_163_preset', 'smtp163_apply_163_preset');

function smtp163_test_connection() {
    if (!current_user_can('manage_options')) {
        wp_die('Permission denied.');
    }

    check_admin_referer('smtp163_test_connection', 'smtp163_conn_nonce');

    $settings = smtp163_get_settings();
    $host = trim((string) $settings['host']);
    $port = intval($settings['port']);
    $secure = (string) $settings['secure'];
    $timeout = intval($settings['timeout']);

    if ($host === '' || $port < 1) {
        smtp163_add_log('error', 'SMTP connection test failed', array('reason' => 'Invalid host or port'));
        wp_safe_redirect(add_query_arg(array('page' => 'smtp163-mailer', 'smtp163_conn' => 'fail', 'smtp163_conn_reason' => 'config'), admin_url('options-general.php')));
        exit;
    }

    $transport = $secure === 'ssl' ? 'ssl://' : 'tcp://';
    $target = $transport . $host . ':' . $port;

    $errno = 0;
    $errstr = '';
    $fp = @stream_socket_client($target, $errno, $errstr, max(1, $timeout));

    if (is_resource($fp)) {
        stream_set_timeout($fp, max(1, $timeout));

        // For STARTTLS mode, verify protocol upgrade instead of plain TCP only.
        if ($secure === 'tls') {
            smtp163_read_smtp_response($fp);
            fwrite($fp, "EHLO localhost\r\n");
            smtp163_read_smtp_response($fp);

            fwrite($fp, "STARTTLS\r\n");
            list($starttls_code, $starttls_resp) = smtp163_read_smtp_response($fp);
            if ($starttls_code !== 220) {
                fclose($fp);
                smtp163_add_log('error', 'SMTP connection test failed', array(
                    'target' => $target,
                    'secure' => $secure,
                    'reason' => 'starttls',
                    'response' => $starttls_resp,
                ));
                wp_safe_redirect(add_query_arg(array('page' => 'smtp163-mailer', 'smtp163_conn' => 'fail', 'smtp163_conn_reason' => 'starttls'), admin_url('options-general.php')));
                exit;
            }

            $crypto_ok = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($crypto_ok !== true) {
                fclose($fp);
                smtp163_add_log('error', 'SMTP connection test failed', array(
                    'target' => $target,
                    'secure' => $secure,
                    'reason' => 'ssl',
                    'response' => $starttls_resp,
                ));
                wp_safe_redirect(add_query_arg(array('page' => 'smtp163-mailer', 'smtp163_conn' => 'fail', 'smtp163_conn_reason' => 'ssl'), admin_url('options-general.php')));
                exit;
            }
        }

        fclose($fp);
        smtp163_add_log('info', 'SMTP connection test success', array(
            'target' => $target,
            'secure' => $secure,
        ));
        wp_safe_redirect(add_query_arg(array('page' => 'smtp163-mailer', 'smtp163_conn' => 'ok'), admin_url('options-general.php')));
        exit;
    }

    $reason = smtp163_detect_conn_reason($errno, $errstr);
    smtp163_add_log('error', 'SMTP connection test failed', array(
        'target' => $target,
        'secure' => $secure,
        'reason' => $reason,
        'errno' => $errno,
        'error' => $errstr,
    ));

    wp_safe_redirect(add_query_arg(array('page' => 'smtp163-mailer', 'smtp163_conn' => 'fail', 'smtp163_conn_reason' => $reason), admin_url('options-general.php')));
    exit;
}
add_action('admin_post_smtp163_test_connection', 'smtp163_test_connection');

function smtp163_handle_clear_logs() {
    if (!current_user_can('manage_options')) {
        wp_die('Permission denied.');
    }

    check_admin_referer('smtp163_clear_logs', 'smtp163_clear_logs_nonce');
    smtp163_clear_logs();

    wp_safe_redirect(add_query_arg(array('page' => 'smtp163-mailer', 'smtp163_log' => 'cleared'), admin_url('options-general.php')));
    exit;
}
add_action('admin_post_smtp163_clear_logs', 'smtp163_handle_clear_logs');

function smtp163_export_logs_csv() {
    if (!current_user_can('manage_options')) {
        wp_die('Permission denied.');
    }

    check_admin_referer('smtp163_export_logs', 'smtp163_export_logs_nonce');
    $logs = smtp163_get_logs();

    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=smtp163-logs-' . gmdate('Y-m-d-His') . '.csv');

    $out = fopen('php://output', 'w');
    if ($out === false) {
        wp_die('Unable to export logs.');
    }

    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, array('time', 'level', 'message', 'context'));

    foreach ($logs as $log) {
        fputcsv($out, array(
            $log['time'] ?? '',
            $log['level'] ?? '',
            $log['message'] ?? '',
            $log['context'] ?? '',
        ));
    }

    fclose($out);
    exit;
}
add_action('admin_post_smtp163_export_logs', 'smtp163_export_logs_csv');

function smtp163_configure_phpmailer($phpmailer) {
    $settings = smtp163_get_settings();

    if (empty($settings['enabled'])) {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host = $settings['host'];
    $phpmailer->Port = intval($settings['port']);
    $phpmailer->SMTPAuth = !empty($settings['auth']);
    $phpmailer->Timeout = intval($settings['timeout']);

    if ($settings['secure'] === 'ssl' || $settings['secure'] === 'tls') {
        $phpmailer->SMTPSecure = $settings['secure'];
    } else {
        $phpmailer->SMTPSecure = '';
    }

    $phpmailer->Username = $settings['username'];
    $phpmailer->Password = $settings['password'];

    if (!empty($settings['from_email'])) {
        $phpmailer->setFrom($settings['from_email'], $settings['from_name'], false);
    }
}
add_action('phpmailer_init', 'smtp163_configure_phpmailer');

function smtp163_capture_mail_error($wp_error) {
    $context = array();
    if ($wp_error instanceof WP_Error) {
        $context['codes'] = $wp_error->get_error_codes();
        $context['messages'] = $wp_error->get_error_messages();
        $context['data'] = $wp_error->get_error_data();
    }

    smtp163_add_log('error', 'wp_mail failed', $context);
}
add_action('wp_mail_failed', 'smtp163_capture_mail_error');

function smtp163_filter_wp_mail_from($from_email) {
    $settings = smtp163_get_settings();
    if (!empty($settings['enabled']) && !empty($settings['force_from']) && !empty($settings['from_email'])) {
        return $settings['from_email'];
    }

    return $from_email;
}
add_filter('wp_mail_from', 'smtp163_filter_wp_mail_from');

function smtp163_filter_wp_mail_from_name($from_name) {
    $settings = smtp163_get_settings();
    if (!empty($settings['enabled']) && !empty($settings['force_from']) && !empty($settings['from_name'])) {
        return $settings['from_name'];
    }

    return $from_name;
}
add_filter('wp_mail_from_name', 'smtp163_filter_wp_mail_from_name');

function smtp163_activate_plugin() {
    $existing = get_option(SMTP163_OPTION_KEY, null);
    if (!is_array($existing)) {
        add_option(SMTP163_OPTION_KEY, smtp163_default_settings());
    }

    if (!is_array(get_option(SMTP163_LOG_OPTION_KEY, null))) {
        add_option(SMTP163_LOG_OPTION_KEY, array());
    }
}
register_activation_hook(__FILE__, 'smtp163_activate_plugin');
