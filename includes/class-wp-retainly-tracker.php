<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Retainly_Tracker {
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('woocommerce_order_status_completed', [$this, 'track_order'], 10, 1);
        add_action('wp_login', [$this, 'track_login'], 10, 2);
        add_action('woocommerce_add_to_cart', [$this, 'track_cart_add'], 10, 6);
    }
    
    public function track_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $customer_id = $order->get_customer_id();
        if (!$customer_id) {
            return;
        }
        
        $this->save_event($customer_id, 'order_completed', [
            'order_id' => $order_id,
            'total' => $order->get_total(),
            'items' => $order->get_item_count(),
            'date' => current_time('mysql')
        ]);
    }
    
    public function track_login($user_login, $user) {
        $this->save_event($user->ID, 'login', [
            'date' => current_time('mysql')
        ]);
    }
    
    public function track_cart_add($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        if (!is_user_logged_in()) {
            return;
        }
        
        $this->save_event(get_current_user_id(), 'cart_add', [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'date' => current_time('mysql')
        ]);
    }
    
    private function save_event($customer_id, $event_type, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'retainly_events';
        
        $wpdb->insert($table, [
            'customer_id' => $customer_id,
            'event_type' => $event_type,
            'event_data' => maybe_serialize($data),
            'created_at' => current_time('mysql')
        ]);
    }
    
    public function get_customer_events($customer_id, $limit = 100) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'retainly_events';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE customer_id = %d ORDER BY created_at DESC LIMIT %d",
            $customer_id,
            $limit
        ));
    }
}
