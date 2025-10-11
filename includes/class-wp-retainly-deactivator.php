<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Retainly_Deactivator {
    public static function deactivate() {
        wp_clear_scheduled_hook('wp_retainly_calculate_churn_risk');
    }
}
