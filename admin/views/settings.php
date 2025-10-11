<?php if (!defined('ABSPATH')) { exit; } ?>

<div class="wrap">
    <h1><?php echo esc_html__('WP Retainly Settings', 'wp-retainly'); ?></h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('wp_retainly_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="wp_retainly_webhook_url">
                        <?php echo esc_html__('Webhook URL', 'wp-retainly'); ?>
                    </label>
                </th>
                <td>
                    <input type="url" 
                           id="wp_retainly_webhook_url"
                           name="wp_retainly_webhook_url"
                           value="<?php echo esc_attr(get_option('wp_retainly_webhook_url')); ?>"
                           class="regular-text" />
                    <p class="description">
                        <?php echo esc_html__('URL to send high churn risk notifications', 'wp-retainly'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>
