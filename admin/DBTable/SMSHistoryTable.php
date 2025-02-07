<?php
namespace WooEasyLife\Admin\DBTable;

if (!class_exists('SmsHistoryTable')) :
class SMSHistoryTable {
    public $table_name = '';
    public function __construct() {
        global $wpdb;

        $this->table_name = $wpdb->prefix . __PREFIX . 'sms_history';
    }

    /**
     * Create the sms_history table
     */
    public function create() {
        global $wpdb;

        // Define table name
        $charset_collate = $wpdb->get_charset_collate();

        // SQL to create the table
        $sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            phone_number TEXT NOT NULL,
            message TEXT NOT NULL,
            status VARCHAR(255) NOT NULL,
            error_message TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";

        // Include the required file for dbDelta
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Execute the query
        dbDelta($sql);
    }

    /**
     * Drop the sms_history table
     */
    public function delete() {
        global $wpdb;

        // Optional: Uncomment the next line to delete the table on plugin deactivation
        $wpdb->query("DROP TABLE IF EXISTS $this->table_name");
    }
}
endif;