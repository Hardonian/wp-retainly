<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Retainly_Admin {
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('WP Retainly', 'wp-retainly'),
            __('Retainly', 'wp-retainly'),
            'manage_woocommerce',
            'wp-retainly',
            [$this, 'render_dashboard_page'],
            'dashicons-chart-line',
            56
        );
        
        add_submenu_page(
            'wp-retainly',
            __('Settings', 'wp-retainly'),
            __('Settings', 'wp-retainly'),
            'manage_options',
            'wp-retainly-settings',
            [$this, 'render_settings_page']
        );
    }
    
    public function register_settings() {
        register_setting('wp_retainly_settings', 'wp_retainly_webhook_url');
        register_setting('wp_retainly_settings', 'wp_retainly_risk_threshold');
    }
    
    public function render_dashboard_page() {
        $predictor = WP_Retainly_Predictor::instance();
        
        $high_risk_customers = get_users([
            'meta_key' => '_retainly_churn_risk',
            'meta_value' => 0.7,
            'meta_compare' => '>=',
            'number' => 20
        ]);
        
        include WP_RETAINLY_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    public function render_settings_page() {
        include WP_RETAINLY_PLUGIN_DIR . 'admin/views/settings.php';
    }
}
