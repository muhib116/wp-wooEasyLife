<?php
namespace WooEasyLife\Admin\DBTable;

if (!class_exists('BlockListTable')) :
class BlockListTable {
    public $table_name = '';
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . __PREFIX . 'block_list';
    }

    /**
     * Create the block_list table
     */
    public function create() {
        global $wpdb;

        // Define table name
        $charset_collate = $wpdb->get_charset_collate();

        // SQL to create the table
        $sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer_id BIGINT UNSIGNED NOT NULL,
            type ENUM('ip', 'phone_number', 'email') NOT NULL,
            ip_phone_or_email VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";

        // Include the required file for dbDelta
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Execute the query
        dbDelta($sql);
    }

    /**
     * Drop the block_list table
     */
    public function delete() {
        global $wpdb;

        // Ensure the __PREFIX constant is defined
        if (!defined('__PREFIX')) {
            define('__PREFIX', 'woo_easy_life_');
        }

        // Optional: Uncomment the next line to delete the table on plugin deactivation
        $wpdb->query("DROP TABLE IF EXISTS $this->table_name");
    }
}
endif;