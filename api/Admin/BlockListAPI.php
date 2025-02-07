<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

class BlockListAPI extends WP_REST_Controller {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . __PREFIX . 'block_list';

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route(__API_NAMESPACE, '/block-list', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_all_blocked_entries'],
                'permission_callback' => api_permission_check(),
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_blocked_entry'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_block_list_schema(false),
            ],
        ]);
        register_rest_route(__API_NAMESPACE, '/bulk-entry', [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_blocked_entries_in_bulk'],
                'permission_callback' => api_permission_check(), // Adjust permissions as needed
                'args'                => $this->get_bulk_entry_schema(),
            ],
        ]);
        register_rest_route(__API_NAMESPACE, '/block-list/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_blocked_entry_by_id'],
                'permission_callback' => api_permission_check(),
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'update_blocked_entry'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_block_list_schema(true),
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [$this, 'delete_blocked_entry'],
                'permission_callback' => api_permission_check(),
            ],
        ]);
    }

    /**
     * Get all blocked entries
     */
    public function get_all_blocked_entries() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM {$this->table_name}", ARRAY_A);

        if (empty($results)) {
            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'No blocked entries found.',
                'data'    => [],
            ], 200);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Blocked entries retrieved successfully.',
            'data'    => $results,
        ], 200);
    }

    /**
     * Create a new blocked entry
     */
    public function create_blocked_entry(WP_REST_Request $request) {
        global $wpdb;

        $customer_id = sanitize_text_field($request->get_param('customer_id'));
        $type = sanitize_text_field($request->get_param('type'));
        $ip_phone_or_email = sanitize_text_field($request->get_param('ip_phone_or_email'));
        $created_at = current_time('mysql');
        $updated_at = current_time('mysql');

        // Check for uniqueness
        $existing_record = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE type = %s AND ip_phone_or_email = %s",
                $type,
                $ip_phone_or_email
            ),
            ARRAY_A
        );


        // update customer data start
        $this->update_customer_data(null, $customer_id);
        // update customer data end


        if ($existing_record) {

            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'This entry already exists in the block list.',
            ], 400);
        }

        // Insert the new blocked entry
        $inserted = $wpdb->insert(
            $this->table_name,
            [
                'customer_id' => $customer_id,
                'type'        => $type,
                'ip_phone_or_email' => $ip_phone_or_email,
                'created_at'  => $created_at,
                'updated_at'  => $updated_at,
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            ]
        );

        if ($inserted === false) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Failed to create blocked entry.',
            ], 500);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Blocked entry created successfully.',
            'data'    => [
                'id'          => $wpdb->insert_id,
                'type'        => $type,
                'ip_phone_or_email' => $ip_phone_or_email,
                'created_at'  => $created_at,
                'updated_at'  => $updated_at,
            ],
        ], 201);
    }

    /**
     * Create new blocked entries in bulk
     */
    public function create_blocked_entries_in_bulk(WP_REST_Request $request) {
        global $wpdb;
        $payload = $request->get_json_params(); // Get the JSON payload

        if (!is_array($payload) || empty($payload)) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Invalid or empty payload.',
            ], 400);
        }

        $responses = [];

        foreach ($payload as $entry) {
            if (!isset($entry['type'], $entry['ip_phone_or_email'])) {
                $responses[] = [
                    'status'  => 'error',
                    'message' => 'Missing type or ip_phone_or_email in entry.',
                    'entry'   => $entry,
                ];
                continue;
            }

            $customer_id = sanitize_text_field($entry['customer_id']);
            $type = sanitize_text_field($entry['type']);
            $ip_phone_or_email = sanitize_text_field($entry['ip_phone_or_email']);
            $created_at = current_time('mysql');
            $updated_at = current_time('mysql');

            // Check for uniqueness
            $existing_record = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$this->table_name} WHERE type = %s AND ip_phone_or_email = %s",
                    $type,
                    $ip_phone_or_email
                ),
                ARRAY_A
            );


            // update customer data start
            $this->update_customer_data($customer_id);
            // update customer data end


            if ($existing_record) {
                $responses[] = [
                    'status'  => 'error',
                    'message' => 'This entry already exists in the block list.',
                    'entry'   => $entry,
                ];
                continue;
            }

            // Insert the new blocked entry
            $inserted = $wpdb->insert(
                $this->table_name,
                [
                    'customer_id'        => $customer_id,
                    'type'        => $type,
                    'ip_phone_or_email' => $ip_phone_or_email,
                    'created_at'  => $created_at,
                    'updated_at'  => $updated_at,
                ],
                [
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                ]
            );

            if ($inserted === false) {
                $responses[] = [
                    'status'  => 'error',
                    'message' => 'Failed to create blocked entry.',
                    'entry'   => $entry,
                ];
                continue;
            }

            $responses[] = [
                'status'  => 'success',
                'message' => 'Blocked entry created successfully.',
                'data'    => [
                    'id'          => $wpdb->insert_id,
                    'customer_id' => $customer_id,
                    'type'        => $type,
                    'ip_phone_or_email' => $ip_phone_or_email,
                    'created_at'  => $created_at,
                    'updated_at'  => $updated_at,
                ],
            ];
        }

        return new WP_REST_Response($responses, 200);
    }


    /**
     * Get a blocked entry by ID
     */
    public function get_blocked_entry_by_id(WP_REST_Request $request) {
        global $wpdb;

        $id = $request->get_param('id');
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id),
            ARRAY_A
        );

        if (empty($result)) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Blocked entry not found.',
            ], 404);
        }

        $customer_id = $result['customer_id'];
        // update customer data start
        $this->update_customer_data($customer_id);
        // update customer data end

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Blocked entry retrieved successfully.',
            'data'    => $result,
        ], 200);
    }

    /**
     * Update a blocked entry by ID
     */
    public function update_blocked_entry(WP_REST_Request $request) {
        global $wpdb;

        $id = $request->get_param('id');
        $type = sanitize_text_field($request->get_param('type'));
        $ip_phone_or_email = sanitize_text_field($request->get_param('ip_phone_or_email'));
        $updated_at = current_time('mysql');

        // Check for unique combination of type and ip_phone_or_email
        $existing_entry = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE type = %s AND ip_phone_or_email = %s AND id != %d",
                $type,
                $ip_phone_or_email,
                $id
            )
        );

        if ($existing_entry) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'An entry with the same type and value already exists.',
            ], 400);
        }

        $updated = $wpdb->update(
            $this->table_name,
            [
                'type'        => $type,
                'ip_phone_or_email' => $ip_phone_or_email,
                'updated_at'  => $updated_at,
            ],
            ['id' => $id],
            [
                '%s',
                '%s',
                '%s',
            ],
            ['%d']
        );

        if ($updated === false) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Failed to update blocked entry.',
            ], 500);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Blocked entry updated successfully.',
            'data'    => [
                'id'          => $id,
                'type'        => $type,
                'ip_phone_or_email' => $ip_phone_or_email,
                'updated_at'  => $updated_at,
            ],
        ], 200);
    }

    /**
     * Delete a blocked entry by ID
     */
    public function delete_blocked_entry(WP_REST_Request $request) {
        global $wpdb;

        $id = $request->get_param('id');
        $customer_id = $this->get_blocked_customer_id($id);

        $deleted = $wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );

        if ($deleted === false) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Failed to delete blocked entry.',
            ], 500);
        }

        // update customer data start
        $this->update_customer_data($customer_id);
        // update customer data end

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Blocked entry deleted successfully.',
        ], 200);
    }

    private function update_customer_data($customer_id=null) {
        if($customer_id){
            $customerHandler = new \WooEasyLife\Frontend\CustomerHandler();
            return $customerHandler->recalculate_customer_data($customer_id);
        }
    }

    private function get_blocked_customer_id($id) {
        global $wpdb;
        // Fetch customer_id from the table using the provided ID
        $customer_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT customer_id FROM {$this->table_name} WHERE id = %d LIMIT 1",
                $id
            )
        );
    
        return $customer_id ?: null; // Return null if no record found
    }
    

    /**
     * Schema for block list input validation
     */
    private function get_block_list_schema($require_id = false) {
        $schema = [
            'customer_id' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'Type of the blocked entry custom customer data table id.',
            ],
            'type' => [
                'required'    => true,
                'type'        => 'string',
                'enum'        => ['ip', 'phone_number'],
                'description' => 'Type of the blocked entry (ip or phone_number).',
            ],
            'ip_phone_or_email' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'IP address or phone number to block.',
            ],
        ];

        if ($require_id) {
            $schema['id'] = [
                'required'    => true,
                'type'        => 'integer',
                'description' => 'Unique identifier for the blocked entry.',
            ];
        }

        return $schema;
    }
    private function get_bulk_entry_schema() {
        return [
            [
                'type'       => 'object',
                'properties' => [
                    'customer_id' => [
                        'required'    => true,
                        'type'        => 'string',
                        'description' => 'Type of entry to block.',
                    ],
                    'type' => [
                        'required'    => true,
                        'type'        => 'string',
                        'enum'        => ['phone_number', 'email', 'ip'],
                        'description' => 'Type of entry to block (phone_number or ip).',
                    ],
                    'ip_phone_or_email' => [
                        'required'    => true,
                        'type'        => 'string',
                        'description' => 'The phone number or IP address to block.',
                    ],
                ],
            ]
        ];
    }     
}