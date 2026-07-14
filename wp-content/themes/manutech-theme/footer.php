<footer class="site-footer">
    <?php $profile = manutech_company_profile(); ?>
    <div class="container footer-grid">
        <div>
            <h3 class="footer-title"><?php echo esc_html($profile['company_name']); ?></h3>
            <p><?php echo esc_html($profile['company_intro']); ?></p>
        </div>
        <div>
            <h3 class="footer-title">联系方式</h3>
            <p>电话：<?php echo esc_html($profile['phone']); ?></p>
            <p>邮箱：<?php echo esc_html($profile['email']); ?></p>
            <p>地址：<?php echo esc_html($profile['hq_address']); ?></p>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
