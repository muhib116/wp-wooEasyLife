<?php
namespace WooEasyLife;

class PluginLifecycleHandle {
    public $handleDBTable;
    public $initClass;

    public function __construct() {
        // Ensure the lifecycle hooks are registered properly
        register_activation_hook(WEL_PLUGIN_FILE, [__CLASS__, 'woo_easy_life_activation_function']);
        register_deactivation_hook(WEL_PLUGIN_FILE, [__CLASS__, 'woo_easy_life_deactivation_function']);
        register_uninstall_hook(WEL_PLUGIN_FILE, [__CLASS__, 'woo_easy_life_uninstall_function']);

        // Hook for runtime updates
        add_action('init', [$this, 'updatePlugin']);

        // Initialize other classes
        $this->handleDBTable = new Admin\DBTable\HandleDBTable();
        $this->initClass = new Init\InitClass();
    }

    /**
     * Activation function
     */
    public static function woo_easy_life_activation_function() {
        // Instantiate dependencies
        $handleDBTable = new Admin\DBTable\HandleDBTable();
        $initClass = new Init\InitClass();

        // Initialize required options
        if (empty(get_option(__PREFIX . 'license'))) update_option(__PREFIX . 'license', ['key' => ""]);
        if (empty(get_option(__PREFIX . 'plugin_installed'))) update_option(__PREFIX . 'plugin_installed', true);
        if (empty(get_option(__PREFIX . '_courier_data'))) update_option(__PREFIX . '_courier_data', true);

        // Create required database tables and settings
        $handleDBTable->create();
        $initClass->create_static_statuses();
        $initClass->save_default_config();
    }

    /**
     * Deactivation function
     */
    public static function woo_easy_life_deactivation_function() {
        global $config_data;

        if ($config_data['clear_data_when_deactivate_plugin'] ?? false) {
            $handleDBTable = new Admin\DBTable\HandleDBTable();
            self::cleanPluginData($handleDBTable);
        }
    }

    /**
     * Uninstall function
     */
    public static function woo_easy_life_uninstall_function() {
        $handleDBTable = new Admin\DBTable\HandleDBTable();
        self::cleanPluginData($handleDBTable);
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
     * Handle plugin updates
     */
    public function updatePlugin() {
        global $license_key;
        new Init\UpdatePlugin($this->get_current_plugin_version(), $license_key);
    }

    /**
     * Get the current plugin version
     *
     * @return string|null Plugin version
     */
    private function get_current_plugin_version() {
        $plugin_file = WEL_PLUGIN_FILE;

        if (file_exists($plugin_file)) {
            $plugin_data = get_file_data($plugin_file, ['Version' => 'Version']);
            return $plugin_data['Version'] ?? null;
        }

        return null;
    }
}