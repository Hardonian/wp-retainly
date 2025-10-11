<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Retainly_Activator {
    public static function activate() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'retainly_events';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            customer_id bigint(20) NOT NULL,
            event_type varchar(50) NOT NULL,
            event_data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY customer_id (customer_id),
            KEY event_type (event_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // Schedule cron
        if (!wp_next_scheduled('wp_retainly_calculate_churn_risk')) {
            wp_schedule_event(time(), 'daily', 'wp_retainly_calculate_churn_risk');
        }
    }
}
