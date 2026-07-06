<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-widgets">
                <?php if (is_active_sidebar('footer-sidebar')): ?>
                    <?php dynamic_sidebar('footer-sidebar'); ?>
                <?php else: ?>
                    <div class="footer-widget">
                        <h4>联系我们</h4>
                        <p>地址：湖南省新化县新洋路54号</p>
                        <p>电话：15919731630</p>
                        <p>联系：李生</p>
                        <p>邮箱：15919731630@163.com</p>
                    </div>
                    
                    <div class="footer-widget">
                        <h4>快速链接</h4>
                        <?php wp_nav_menu(array(
                            'theme_location' => 'footer-menu',
                            'menu_class' => 'footer-menu',
                            'container' => false
                        )); ?>
                    </div>
                    
                    <div class="footer-widget">
                        <h4>关注我们</h4>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-weixin"></i>15919731630</a>
                            <a href="#"><i class="fab fa-weibo"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. 版权所有.</p>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>