<?php
namespace WooEasyLife\CRUD;

class FraudCustomerTable {
    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . __PREFIX .'fraud_customers';
    }

    /**
     * Create a new fraud record.
     */
    public function create($data) {
        global $wpdb;
        
        $inserted = $wpdb->insert(
            $this->table_name,
            [
                'customer_id' => $data['customer_id'],
                'report'      => json_encode($data['report']),
                'created_at'  => current_time('mysql')
            ],
            [
                '%s',
                '%s',
                '%s'
            ]
        );
    
        if ($inserted === false) {
            echo ("Database Insertion Failed: " . $wpdb->last_error);
            return false;
        }
    
        echo $wpdb->insert_id;
        return $wpdb->insert_id;
    }

    public function create_or_update($data) {
        global $wpdb;
    
        // Check if the record already exists
        $existing_record = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $this->table_name WHERE customer_id = %s",
                $data['customer_id']
            )
        );

        if ($existing_record) {
            // Update the existing record
            $updated = $wpdb->update(
                $this->table_name,
                [
                    'report'      => json_encode($data['report']),
                    'updated_at'  => current_time('mysql'), // Ensure the table has an `updated_at` column
                ],
                ['customer_id' => $data['customer_id']],
                [
                    '%s',
                    '%s',
                ],
                ['%s']
            );
            
            if ($updated === false) {
                echo ("Database Update Failed: " . $wpdb->last_error);
                return false;
            }
    
            return $existing_record; // Return the ID of the updated record
        } else {
            // Insert a new record
            $inserted = $wpdb->insert(
                $this->table_name,
                [
                    'customer_id' => $data['customer_id'],
                    'report'      => json_encode($data['report']),
                    'created_at'  => current_time('mysql')
                ],
                [
                    '%s',
                    '%s',
                    '%s'
                ]
            );
    
            if ($inserted === false) {
                echo ("Database Insertion Failed: " . $wpdb->last_error);
                return false;
            }
    
            return $wpdb->insert_id; // Return the ID of the newly inserted record
        }
    }
    
    

    /**
     * Read a fraud record by customer ID.
     */
    public function get_fraud_data_by_customer_id($customer_id) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE customer_id = %s",
                $customer_id
            ),
            ARRAY_A
        );
    }

    /**
     * Update a fraud record by customer ID.
     */
    public function update($customer_id, $data) {
        global $wpdb;

        return $wpdb->update(
            $this->table_name,
            [
                'report'     => json_encode($data['report']),
                'blocked_at' => isset($data['blocked_at']) ? $data['blocked_at'] : current_time('mysql')
            ],
            ['customer_id' => $customer_id],
            [
                '%s',
                '%s'
            ],
            ['%s']
        );
    }

    /**
     * Delete a fraud record by customer ID.
     */
    public function delete($customer_id) {
        global $wpdb;

        return $wpdb->delete(
            $this->table_name,
            ['customer_id' => $customer_id],
            ['%s']
        );
    }

    /**
     * Get all fraud records.
     */
    public function get_all() {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM $this->table_name", ARRAY_A);
    }

    /**
     * Check if a fraud record exists by customer ID.
     */
    public function exists($customer_id) {
        global $wpdb;

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $this->table_name WHERE customer_id = %s",
                $customer_id
            )
        );

        return $count > 0;
    }
}