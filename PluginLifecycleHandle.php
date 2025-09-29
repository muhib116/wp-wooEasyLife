<?php
namespace WooEasyLife;

class PluginLifecycleHandle {
    public $handleDBTable;
    public $initClass;
    
    /** @var string The key for storing the plugin's version in the wp_options table. */
    private const DB_VERSION_OPTION_KEY = __PREFIX . 'plugin_version';

    public function __construct() {
        // Standard plugin lifecycle hooks.
        register_activation_hook(WEL_PLUGIN_FILE, [__CLASS__, 'woo_easy_life_activation_function']);
        register_deactivation_hook(WEL_PLUGIN_FILE, [__CLASS__, 'woo_easy_life_deactivation_function']);
        
        // This hook now handles both plugin updates and our custom migrations.
        add_action('admin_init', [$this, 'handle_plugin_update_and_migrations']);

        // Initialize other classes.
        $this->handleDBTable = new \WooEasyLife\Admin\DBTable\HandleDBTable();
        $this->initClass = new \WooEasyLife\Init\InitClass();
    }

    /**
     * This function checks for version changes on every admin page load
     * and runs necessary migration tasks for updates.
     */
    public function handle_plugin_update_and_migrations() {
        // Don't run this for non-admins.
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        $current_file_version = self::get_current_plugin_version();
        $current_db_version = get_option(self::DB_VERSION_OPTION_KEY);

        // If the version in the database is different from the file, it's an update or first install.
        if (version_compare($current_file_version, $current_db_version, '>')) {
            
            // --- Run Migration Tasks Here ---
            $this->migrate_to_license_status_system($current_db_version);
            // You can add more migration functions here in the future.
            
            // After all migrations are done, update the version in the database.
            update_option(self::DB_VERSION_OPTION_KEY, $current_file_version);
        }
    }
    
    /**
     * One-time migration function to validate the existing license key and store its status.
     * This prevents features from being disabled for existing users after an update.
     *
     * @param string|false $from_version The version the user is updating from.
     */
    private function migrate_to_license_status_system($from_version) {
        // Let's assume the license status system was introduced in version '1.1.0'.
        // This migration should only run for users updating from a version older than that.
        // If $from_version is false, it's a new install, so the activation hook handles it.
        if ($from_version && version_compare($from_version, '1.1.0', '<')) {
            
            // Check if a license status is already set. If so, do nothing.
            if (get_option('woo_easy_life_license_status')) {
                return;
            }

            // Get the existing license key.
            $license_data = get_option(__PREFIX . 'license');
            $license_key = is_array($license_data) && !empty($license_data['key']) ? $license_data['key'] : '';

            if (empty($license_key)) {
                update_option('woo_easy_life_license_status', 'unauthenticated');
                return;
            }

            // Make a background API call to validate the key.
            $url = get_api_end_point("get-user");
            $response = wp_remote_get($url, [
                'headers'   => [
                    'Authorization' => 'Bearer ' . $license_key,
                    'origin'        => site_url()
                ],
                'timeout'   => 20,
                'sslverify' => false,
            ]);

            if (is_wp_error($response)) {
                // If API call fails, we can't be sure. Default to 'invalid'.
                // It will be re-validated the next time the user visits the Vue app.
                update_option('woo_easy_life_license_status', 'invalid');
                error_log('WooEasyLife Migration Error: API call to validate license failed. ' . $response->get_error_message());
                return;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $message = $body['message'] ?? '';

            if ($response_code === 200) {
                update_option('woo_easy_life_license_status', 'valid');
            } elseif (stripos($message, 'Expired') !== false) {
                update_option('woo_easy_life_license_status', 'expired');
            } else {
                update_option('woo_easy_life_license_status', 'invalid');
            }
        }
    }

    /**
     * Activation function.
     */
    public static function woo_easy_life_activation_function() {
        // Instantiate dependencies
        $handleDBTable = new \WooEasyLife\Admin\DBTable\HandleDBTable();
        $initClass = new \WooEasyLife\Init\InitClass();

        // Initialize required options
        if (empty(get_option(__PREFIX . 'license'))) update_option(__PREFIX . 'license', ['key' => ""]);
        
        // Create required database tables and settings
        $handleDBTable->create();
        $initClass->create_static_statuses();
        $initClass->save_default_config();

        // Store the current plugin version in the database upon activation.
        update_option(self::DB_VERSION_OPTION_KEY, self::get_current_plugin_version());
        
        // Also set a default license status for new installations.
        if (!get_option('woo_easy_life_license_status')) {
            update_option('woo_easy_life_license_status', 'unauthenticated');
        }
    }

    /**
     * Deactivation function.
     */
    public static function woo_easy_life_deactivation_function() {
        global $config_data;

        if ($config_data['clear_data_when_deactivate_plugin'] ?? false) {
            $handleDBTable = new Admin\DBTable\HandleDBTable();
            self::cleanPluginData($handleDBTable);
        }
    }

    /**
     * Clean up plugin data
     *
     * @param object $handleDBTable HandleDBTable instance
     */
    private static function cleanPluginData($handleDBTable) {
        // Delete plugin-specific options
        if (get_option(__PREFIX . 'sms_config') !== false) delete_option(__PREFIX . 'sms_config');
        if (get_option(__PREFIX . 'license') !== false) delete_option(__PREFIX . 'license');
        if (get_option(__PREFIX . 'config') !== false) delete_option(__PREFIX . 'config');
        if (get_option(__PREFIX . 'plugin_installed') !== false) delete_option(__PREFIX . 'plugin_installed');
        if (get_option(__PREFIX . 'custom_order_statuses') !== false) delete_option(__PREFIX . 'custom_order_statuses');
        if (get_option(__PREFIX . 'sales_target') !== false) delete_option(__PREFIX . 'sales_target');

        // Delete custom database tables
        $handleDBTable->delete();

        // Clean WooCommerce order metadata
        self::delete_wc_orders_meta_by_key('_courier_data');
        self::delete_wc_orders_meta_by_key('_status_history');
    }

    /**
     * Delete WooCommerce order metadata by key
     *
     * @param string $meta_key Meta key to delete
     */
    private static function delete_wc_orders_meta_by_key($meta_key) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_orders_meta';
        $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE meta_key = %s", $meta_key));
    }

    /**
     * Get the current plugin version from the plugin's main file.
     *
     * @return string|null Plugin version.
     */
    static function get_current_plugin_version() {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_data = get_plugin_data(WEL_PLUGIN_FILE);
        return $plugin_data['Version'] ?? '1.0.0'; // Fallback to 1.0.0
    }
}