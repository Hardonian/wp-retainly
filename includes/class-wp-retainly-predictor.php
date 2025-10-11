<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Retainly_Predictor {
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_retainly_calculate_churn_risk', [$this, 'calculate_all_risks']);
    }
    
    public function calculate_churn_risk($customer_id) {
        $tracker = WP_Retainly_Tracker::instance();
        $events = $tracker->get_customer_events($customer_id, 100);
        
        if (empty($events)) {
            return 0.5; // Unknown risk
        }
        
        $risk_score = 0;
        $factors = [];
        
        // Factor 1: Days since last order
        $last_order = $this->get_last_order_date($customer_id);
        if ($last_order) {
            $days_since = (time() - strtotime($last_order)) / DAY_IN_SECONDS;
            if ($days_since > 90) {
                $risk_score += 0.3;
                $factors[] = 'No order in 90+ days';
            } elseif ($days_since > 60) {
                $risk_score += 0.2;
                $factors[] = 'No order in 60+ days';
            }
        }
        
        // Factor 2: Order frequency decline
        $recent_orders = $this->count_orders($customer_id, 60);
        $older_orders = $this->count_orders($customer_id, 120, 60);
        
        if ($older_orders > 0 && $recent_orders < ($older_orders * 0.5)) {
            $risk_score += 0.25;
            $factors[] = 'Order frequency declined 50%+';
        }
        
        // Factor 3: No recent engagement
        $last_login = $this->get_last_login_date($customer_id);
        if ($last_login) {
            $days_since_login = (time() - strtotime($last_login)) / DAY_IN_SECONDS;
            if ($days_since_login > 30) {
                $risk_score += 0.15;
                $factors[] = 'No login in 30+ days';
            }
        }
        
        // Factor 4: Abandoned carts
        $abandoned_carts = $this->count_abandoned_carts($customer_id);
        if ($abandoned_carts > 2) {
            $risk_score += 0.1;
            $factors[] = '3+ abandoned carts';
        }
        
        $risk_score = min(1.0, $risk_score);
        
        // Save risk score
        update_user_meta($customer_id, '_retainly_churn_risk', $risk_score);
        update_user_meta($customer_id, '_retainly_risk_factors', $factors);
        update_user_meta($customer_id, '_retainly_risk_updated', current_time('mysql'));
        
        // Trigger webhook if high risk
        if ($risk_score >= 0.7) {
            do_action('wp_retainly_high_churn_risk', $customer_id, $risk_score, $factors);
        }
        
        return $risk_score;
    }
    
    public function calculate_all_risks() {
        $customers = get_users(['role' => 'customer', 'number' => 100]);
        
        foreach ($customers as $customer) {
            $this->calculate_churn_risk($customer->ID);
        }
    }
    
    private function get_last_order_date($customer_id) {
        $orders = wc_get_orders([
            'customer_id' => $customer_id,
            'limit' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'objects'
        ]);
        
        return !empty($orders) ? $orders[0]->get_date_created()->format('Y-m-d H:i:s') : null;
    }
    
    private function count_orders($customer_id, $days, $offset_days = 0) {
        $date_after = date('Y-m-d', strtotime("-{$days} days -{$offset_days} days"));
        $date_before = $offset_days > 0 ? date('Y-m-d', strtotime("-{$offset_days} days")) : date('Y-m-d');
        
        $orders = wc_get_orders([
            'customer_id' => $customer_id,
            'date_created' => $date_after . '...' . $date_before,
            'return' => 'ids'
        ]);
        
        return count($orders);
    }
    
    private function get_last_login_date($customer_id) {
        return get_user_meta($customer_id, '_retainly_last_login', true);
    }
    
    private function count_abandoned_carts($customer_id) {
        // Simplified - would integrate with cart abandonment tracking
        return 0;
    }
}
