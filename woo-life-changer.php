<?php
/**
 * Plugin Name: Woo Easy Life
 * Plugin URI: https://api.wpsalehub.com/get-metadata
 * Description: "Woo Easy Life" is a custom plugin designed to enhance WooCommerce functionality with features like bulk SMS, fraud detection, OTP validation, and much more.
 * Version: 1.1.4
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
            // Initialize WooCommerce session if available.
            add_action('woocommerce_init', [$this, 'initialize_wc_session']);

            // Load license key and configuration data.
            $this->load_license_key();
            $this->load_config_data();

            // Initialize various components of the plugin.
            $this->initialize_components();
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
            // Initialize core plugin classes.
            new WooEasyLife\Init\BootClass();
            new WooEasyLife\PluginLifecycleHandle();
            new WooEasyLife\API\API_Register();
            new WooEasyLife\Admin\Admin_Class_Register();
            new WooEasyLife\Frontend\Frontend_Class_Register();
        }
    }

    // Instantiate the main plugin class.
    new WooEasyLife();
endif;