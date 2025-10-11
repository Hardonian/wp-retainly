<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Retainly {
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        require_once WP_RETAINLY_PLUGIN_DIR . 'includes/class-wp-retainly-tracker.php';
        require_once WP_RETAINLY_PLUGIN_DIR . 'includes/class-wp-retainly-predictor.php';
        require_once WP_RETAINLY_PLUGIN_DIR . 'includes/class-wp-retainly-webhooks.php';
        require_once WP_RETAINLY_PLUGIN_DIR . 'admin/class-wp-retainly-admin.php';
    }
    
    private function init_hooks() {
        add_action('init', [$this, 'load_textdomain']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Initialize components
        WP_Retainly_Tracker::instance();
        WP_Retainly_Predictor::instance();
        WP_Retainly_Webhooks::instance();
        WP_Retainly_Admin::instance();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain(
            'wp-retainly',
            false,
            dirname(WP_RETAINLY_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wp-retainly') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wp-retainly-admin',
            WP_RETAINLY_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WP_RETAINLY_VERSION
        );
        
        wp_enqueue_script(
            'wp-retainly-admin',
            WP_RETAINLY_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WP_RETAINLY_VERSION,
            true
        );
        
        wp_localize_script('wp-retainly-admin', 'wpRetainly', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp-retainly-nonce'),
            'version' => WP_RETAINLY_VERSION
        ]);
    }
}
