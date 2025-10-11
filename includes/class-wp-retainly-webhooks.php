<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Retainly_Webhooks {
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_retainly_high_churn_risk', [$this, 'trigger_high_risk_webhook'], 10, 3);
    }
    
    public function trigger_high_risk_webhook($customer_id, $risk_score, $factors) {
        $webhook_url = get_option('wp_retainly_webhook_url');
        
        if (empty($webhook_url)) {
            return;
        }
        
        $customer = get_userdata($customer_id);
        if (!$customer) {
            return;
        }
        
        $payload = [
            'event' => 'high_churn_risk',
            'customer' => [
                'id' => $customer_id,
                'email' => $customer->user_email,
                'name' => $customer->display_name
            ],
            'risk_score' => $risk_score,
            'risk_factors' => $factors,
            'timestamp' => current_time('mysql')
        ];
        
        $args = [
            'body' => wp_json_encode($payload),
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'timeout' => 15
        ];
        
        $response = wp_remote_post($webhook_url, $args);
        
        if (is_wp_error($response)) {
            error_log('WP Retainly webhook failed: ' . $response->get_error_message());
        }
    }
}
