<?php
/**
 * Plugin Name: Woo Easy Life
 * Plugin URI: https://api.wpsalehub.com/get-metadata
 * Description: "Woo Easy Life" is a custom plugin designed to enhance WooCommerce functionality with features like bulk SMS, fraud detection, OTP validation, and much more.
 * Version: 1.0.5
 * Author: Muhibbullah Ansary
 * Author URI: https://wpsalehub.com
 * Text Domain: woo-easy-life
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit('Invalid request.');
}

// Define constants.
define('__PREFIX', 'woo_easy_life_');
define('__API_NAMESPACE', 'wooeasylife/v1');
define('WEL_PLUGIN_FILE', __FILE__);
define('WEL_PLUGIN_DIR', __DIR__);
$icon_url = plugin_dir_url(__FILE__) . 'assets/wooEasyLifeIcon.svg'; // Path to your SVG    

$current_version = null;
// Global variables for license and configuration data.
global $config_data, $license_key;

// Autoload dependencies.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Show admin notice if composer dependencies are not installed.
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error is-dismissible">
                <p>' . esc_html__('Woo Easy Life: Missing composer dependencies. Please run "composer install" to install required libraries.', 'woo-easy-life') . '</p>
              </div>';
    });
    return; // Exit early if dependencies are missing.
}

// Main plugin class.
if (!class_exists('WooEasyLife')) :
    class WooEasyLife
    {
        public function __construct()
        {
            add_action('init', [$this, 'custom_set_timezone']);
            // Initialize WooCommerce session if available.
            add_filter( 'http_request_timeout', [$this, 'increase_http_request_timeout'] );
            add_action('woocommerce_init', [$this, 'initialize_wc_session']);
            add_filter('admin_footer_text', [$this, 'custom_modify_admin_footer']);
            add_filter('update_footer', [$this, 'custom_modify_footer_version'], 9999);
            add_filter('woocommerce_order_is_editable', function($editable, $order) {
                return true; // Force all orders to be editable
            }, 10, 2);

            // Load license key and configuration data.
            $this->load_license_key();
            $this->load_config_data();

            // Initialize various components of the plugin.
            $this->initialize_components();
        }
        public function increase_http_request_timeout( $timeout ) {
            return 60; // Set to 60 seconds
        }
        public function custom_set_timezone() {
            update_option('timezone_string', 'Asia/Dhaka');
            date_default_timezone_set('Asia/Dhaka'); // Change to your desired timezone
        }
        
        public function custom_modify_admin_footer() {
            echo '<span style="color: #f97315; margin-left: 16px">Thank you for using WooEasyLife</span>'; // Change this text
        }


        // Modify the right-side footer version
        public function custom_modify_footer_version() {
            global $current_version;
            return "Version $current_version"; // Change this text
        }


        /**
         * Initialize WooCommerce session.
         */
        public function initialize_wc_session()
        {
            if (WC()->session) {
                WC()->session->set_customer_session_cookie(true);
            }
        }

        /**
         * Load license key into the global variable.
         */
        private function load_license_key()
        {
            global $license_key;
            $license_key = get_option(__PREFIX . 'license');
            $license_key = is_string($license_key) ? json_decode($license_key, true) : $license_key;
            $license_key = $license_key['key'] ?? null;
        }

        /**
         * Load configuration data into the global variable.
         */
        private function load_config_data()
        {
            global $config_data;
            $config_data = get_option(__PREFIX . 'config');
            $config_data = is_string($config_data) ? json_decode($config_data, true) : $config_data;

            // Ensure $config_data is an array.
            if (!is_array($config_data)) {
                $config_data = [];
            }
        }

        /**
         * Initialize plugin components.
         */
        private function initialize_components()
        {
            global $current_version;

            // Initialize core plugin classes.
            new WooEasyLife\Init\BootClass();
            new WooEasyLife\API\API_Register();
            new WooEasyLife\Admin\Admin_Class_Register();
            new WooEasyLife\Frontend\Frontend_Class_Register();
            $lifeCycleObj = new WooEasyLife\PluginLifecycleHandle();

            $current_version = $lifeCycleObj->get_current_plugin_version();
        }
    }

    // Instantiate the main plugin class.
    new WooEasyLife();


    /**
     * plugins has bug, that generating status 'wc-complete', but we need to change
     * prev all this status to 'wc-completed' so this code.
     */
    function update_woocommerce_order_status_wc_function() {
        // Get all orders with 'wc-complete' status
        $args = array(
            'status' => 'wc-complete',
            'limit'  => -1, // Get all matching orders
            'type'   => 'shop_order',
        );
    
        $orders = wc_get_orders($args);
    
        if (!empty($orders)) {
            foreach ($orders as $order) {
                // Update order status to 'wc-completed'
                $order->update_status('wc-completed', __('Order status updated from complete to completed.', 'woocommerce'));
            }
    
            // return count($orders) . " orders updated successfully!";
        }
    }
    // Hook into WooCommerce Admin
    add_action('admin_init', 'update_woocommerce_order_status_wc_function');
    
endif;