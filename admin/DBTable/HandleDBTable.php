<?php
namespace WooEasyLife\Admin\DBTable;

if(!class_exists('HandleDBTable')) :
class HandleDBTable{
    public $fraudTable;
    public $smsConfigTable;
    public $blockListTable;
    public $smsHistoryTable;
    public $abandonCartTable;
    public $customerDataTable;

    public function __construct()
    {
        $this->fraudTable = new FraudTable();
        $this->smsConfigTable = new SMSConfigTable();
        $this->blockListTable = new BlockListTable();
        $this->smsHistoryTable = new SMSHistoryTable();
        $this->abandonCartTable = new AbandonCartTable();
        $this->customerDataTable = new CustomerDataTable();

        add_action('admin_notices', function(){
            $this->showAdminNoticeForMissingTable([
                $this->fraudTable->table_name,
                $this->smsConfigTable->table_name,
                $this->blockListTable->table_name,
                $this->smsHistoryTable->table_name,
                $this->abandonCartTable->table_name,
                $this->customerDataTable->table_name,
            ]);
        });
    }

    public function create() {
        $this->fraudTable->create();
        $this->smsConfigTable->create();
        $this->blockListTable->create();
        $this->smsHistoryTable->create();
        $this->abandonCartTable->create();
        $this->customerDataTable->create();
    }

    public function delete() {
        $this->fraudTable->delete();
        $this->smsConfigTable->delete();
        $this->blockListTable->delete();
        $this->smsHistoryTable->delete();
        $this->abandonCartTable->delete();
        $this->customerDataTable->delete();
    }

    public function showAdminNoticeForMissingTable($table_name_array) {
        global $wpdb;
    
        if (empty($table_name_array) || !is_array($table_name_array)) {
            return; // Exit if the input is invalid
        }
    
        foreach ($table_name_array as $table_name) {
            // Sanitize the table name to prevent SQL injection
            $safe_table_name = esc_sql($table_name);
    
            // Check if the table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$safe_table_name}'");
            
            if ($table_exists !== $safe_table_name) {
                $readable_table = str_replace( $wpdb->prefix . __PREFIX, '', $safe_table_name);
                printf(
                    '<div class="notice notice-error is-dismissible">
                        <p>%s</p>
                    </div>',
                    esc_html__('The table "' . $readable_table . '" was not created. Please deactivate and reactivate the "WooEasyLife" plugin.', 'wooeasylife')
                );
            }
        }
    }    
}
endif;