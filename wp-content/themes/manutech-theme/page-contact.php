<?php
/*
Template Name: 联系我们
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manutech_form_submit'])) {
    $redirect_url = get_permalink();
    if (!is_string($redirect_url) || $redirect_url === '') {
        $redirect_url = manutech_get_page_url_by_slug('contact');
    }

    $nonce = isset($_POST['manutech_inquiry_nonce']) ? sanitize_text_field((string) wp_unslash($_POST['manutech_inquiry_nonce'])) : '';
    if ($nonce === '' || !wp_verify_nonce($nonce, 'manutech_submit_inquiry')) {
        wp_safe_redirect(add_query_arg('inquiry', 'invalid', $redirect_url));
        exit;
    }

    $honeypot = isset($_POST['website']) ? trim((string) wp_unslash($_POST['website'])) : '';
    if ($honeypot !== '') {
        wp_safe_redirect(add_query_arg('inquiry', 'success', $redirect_url));
        exit;
    }

    $name = isset($_POST['inquiry_name']) ? sanitize_text_field((string) wp_unslash($_POST['inquiry_name'])) : '';
    $phone = isset($_POST['inquiry_phone']) ? sanitize_text_field((string) wp_unslash($_POST['inquiry_phone'])) : '';
    $email = isset($_POST['inquiry_email']) ? sanitize_email((string) wp_unslash($_POST['inquiry_email'])) : '';
    $company = isset($_POST['inquiry_company']) ? sanitize_text_field((string) wp_unslash($_POST['inquiry_company'])) : '';
    $industry = isset($_POST['inquiry_industry']) ? sanitize_text_field((string) wp_unslash($_POST['inquiry_industry'])) : '';
    $capacity = isset($_POST['inquiry_capacity']) ? sanitize_text_field((string) wp_unslash($_POST['inquiry_capacity'])) : '';
    $timeline = isset($_POST['inquiry_timeline']) ? sanitize_text_field((string) wp_unslash($_POST['inquiry_timeline'])) : '';
    $message = isset($_POST['inquiry_message']) ? sanitize_textarea_field((string) wp_unslash($_POST['inquiry_message'])) : '';

    if ($name === '' || $message === '' || ($phone === '' && $email === '')) {
        wp_safe_redirect(add_query_arg('inquiry', 'invalid', $redirect_url));
        exit;
    }

    $payload = [
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'company' => $company,
        'industry' => $industry,
        'capacity' => $capacity,
        'timeline' => $timeline,
        'message' => $message,
        'ip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field((string) $_SERVER['REMOTE_ADDR']) : '',
        'ua' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field((string) $_SERVER['HTTP_USER_AGENT']) : '',
    ];

    manutech_log_inquiry($payload, 'received');

    if (!manutech_is_local_env()) {
        $profile = manutech_company_profile();
        $to = $profile['email'];
        $subject = '官网询盘 - ' . $name;
        $body = "姓名: {$name}\n电话: {$phone}\n邮箱: {$email}\n公司: {$company}\n行业: {$industry}\n目标产能: {$capacity}\n计划上线: {$timeline}\n\n需求描述:\n{$message}";
        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        if ($email !== '') {
            $headers[] = 'Reply-To: ' . $email;
        }

        $sent = wp_mail($to, $subject, $body, $headers);
        if (!$sent) {
            manutech_log_inquiry($payload, 'mail_failed');
            wp_safe_redirect(add_query_arg('inquiry', 'mailfail', $redirect_url));
            exit;
        }
    }

    wp_safe_redirect(add_query_arg('inquiry', 'success', $redirect_url));
    exit;
}

get_header();
$profile = manutech_company_profile();
$notice = manutech_get_contact_notice();
?>

<main>
    <section class="page-hero">
        <div class="container">
            <h1>联系我们</h1>
            <p>欢迎提交需求，我们将在 1 个工作日内安排工程师对接。</p>
        </div>
    </section>

    <section class="page-content">
        <div class="container">
            <div class="two-col">
                <article class="card">
                    <h3>商务咨询</h3>
                    <p>电话：<?php echo esc_html($profile['phone']); ?></p>
                    <p>邮箱：<?php echo esc_html($profile['email']); ?></p>
                    <p>服务时间：<?php echo esc_html($profile['service_hours']); ?></p>
                    <p>紧急故障支持：<?php echo esc_html($profile['emergency_support']); ?></p>
                </article>
                <article class="card">
                    <h3>快速需求登记</h3>
                    <?php if (manutech_is_local_env()) : ?>
                        <p class="muted">当前为本地调试环境：表单提交将保存到本地日志，不发送邮件。</p>
                    <?php endif; ?>
                    <?php if (!empty($notice)) : ?>
                        <div class="form-notice form-notice-<?php echo esc_attr($notice['type']); ?>">
                            <?php echo esc_html($notice['text']); ?>
                        </div>
                    <?php endif; ?>

                    <form class="manu-form" method="post" action="<?php echo esc_url(get_permalink()); ?>">
                        <input type="hidden" name="manutech_form_submit" value="1">
                        <?php wp_nonce_field('manutech_submit_inquiry', 'manutech_inquiry_nonce'); ?>

                        <div class="form-grid">
                            <p>
                                <label for="inquiry_name">姓名 *</label>
                                <input id="inquiry_name" name="inquiry_name" type="text" required>
                            </p>
                            <p>
                                <label for="inquiry_phone">电话（与邮箱至少填一项）</label>
                                <input id="inquiry_phone" name="inquiry_phone" type="text" placeholder="手机号或座机">
                            </p>
                            <p>
                                <label for="inquiry_email">邮箱（与电话至少填一项）</label>
                                <input id="inquiry_email" name="inquiry_email" type="email" placeholder="name@company.com">
                            </p>
                            <p>
                                <label for="inquiry_company">公司名称</label>
                                <input id="inquiry_company" name="inquiry_company" type="text">
                            </p>
                            <p>
                                <label for="inquiry_industry">所属行业</label>
                                <input id="inquiry_industry" name="inquiry_industry" type="text" placeholder="如：新能源、汽车零部件">
                            </p>
                            <p>
                                <label for="inquiry_capacity">目标产能</label>
                                <input id="inquiry_capacity" name="inquiry_capacity" type="text" placeholder="如：2000 件/天">
                            </p>
                            <p class="full">
                                <label for="inquiry_timeline">计划上线时间</label>
                                <input id="inquiry_timeline" name="inquiry_timeline" type="text" placeholder="如：2026 年 Q3">
                            </p>
                            <p class="full">
                                <label for="inquiry_message">需求描述 *</label>
                                <textarea id="inquiry_message" name="inquiry_message" rows="5" required placeholder="请描述当前痛点、工艺环节和希望达成的目标"></textarea>
                            </p>
                        </div>

                        <p class="website-trap" aria-hidden="true">
                            <label for="website">Website</label>
                            <input id="website" name="website" type="text" autocomplete="off" tabindex="-1">
                        </p>

                        <button class="btn btn-primary" type="submit">提交需求</button>
                    </form>
                </article>
            </div>

            <section class="section-block">
                <div class="grid grid-3">
                    <article class="card">
                        <h3>总部地址</h3>
                        <p><?php echo esc_html($profile['hq_address']); ?></p>
                        <p>可预约工厂参观与技术交流。</p>
                    </article>
                    <article class="card">
                        <h3>华南服务中心</h3>
                        <p><?php echo esc_html($profile['south_address']); ?></p>
                        <p>负责快速备件响应与驻场支持。</p>
                    </article>
                    <article class="card">
                        <h3>海外支持</h3>
                        <p>提供远程运维与英文技术文档支持。</p>
                        <p>可按项目安排现场工程师。</p>
                    </article>
                </div>
            </section>

            <section class="section-block">
                <h2 class="section-title">常见问题</h2>
                <div class="grid grid-3">
                    <article class="card"><h3>最短交付周期多久？</h3><p>标准单机设备通常 6-10 周，整线项目根据规模评估。</p></article>
                    <article class="card"><h3>是否支持分阶段改造？</h3><p>支持。可先从瓶颈工序切入，再逐步扩展至全线。</p></article>
                    <article class="card"><h3>是否提供培训与维保？</h3><p>提供操作培训、维护培训和年度巡检维保服务。</p></article>
                </div>
            </section>
        </div>
    </section>
</main>

<?php get_footer(); ?>
