<?php
/**
 * Plugin Name: WP Retainly
 * Plugin URI: https://github.com/shardie-github/wp-retainly
 * Description: Predict & reduce churn in WooCommerce shops using AI-driven behavior signals
 * Version: 2.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Hardonia Industries
 * Author URI: https://hardonia.store
 * License: Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain: wp-retainly
 * Domain Path: /languages
 * WC requires at least: 7.0
 * WC tested up to: 9.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('WP_RETAINLY_VERSION', '2.0.0');
define('WP_RETAINLY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_RETAINLY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_RETAINLY_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check WooCommerce dependency
add_action('plugins_loaded', 'wp_retainly_check_dependencies');

function wp_retainly_check_dependencies() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wp_retainly_wc_missing_notice');
        return;
    }
    
    // Load plugin
    require_once WP_RETAINLY_PLUGIN_DIR . 'includes/class-wp-retainly.php';
    
    // Initialize
    WP_Retainly::instance();
}

function wp_retainly_wc_missing_notice() {
    echo '<div class="error"><p>';
    echo esc_html__('WP Retainly requires WooCommerce to be installed and activated.', 'wp-retainly');
    echo '</p></div>';
}

// Activation hook
register_activation_hook(__FILE__, 'wp_retainly_activate');

function wp_retainly_activate() {
    require_once WP_RETAINLY_PLUGIN_DIR . 'includes/class-wp-retainly-activator.php';
    WP_Retainly_Activator::activate();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wp_retainly_deactivate');

function wp_retainly_deactivate() {
    require_once WP_RETAINLY_PLUGIN_DIR . 'includes/class-wp-retainly-deactivator.php';
    WP_Retainly_Deactivator::deactivate();
}
