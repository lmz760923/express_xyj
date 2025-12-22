<div class="wrap">
    <h1>虎皮椒支付设置</h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('hupijiao_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">API地址</th>
                <td>
                    <input type="url" name="hupijiao_api_url" 
                           value="<?php echo esc_attr(get_option('hupijiao_api_url')); ?>" 
                           class="regular-text">
                    <p class="description">虎皮椒支付API地址，如：https://api.xunhupay.com/payment</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">App ID</th>
                <td>
                    <input type="text" name="hupijiao_app_id" 
                           value="<?php echo esc_attr(get_option('hupijiao_app_id')); ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row">App Secret</th>
                <td>
                    <input type="password" name="hupijiao_app_secret" 
                           value="<?php echo esc_attr(get_option('hupijiao_app_secret')); ?>" 
                           class="regular-text">
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>
