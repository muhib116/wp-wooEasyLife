<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

class SMSConfigAPI extends WP_REST_Controller {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . __PREFIX . 'sms_config';

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route(__API_NAMESPACE, '/sms-config', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_all_sms_configs'],
                'permission_callback' => api_permission_check(),
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_sms_config'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_sms_config_schema(false),
            ],
        ]);

        register_rest_route(__API_NAMESPACE, '/sms-config/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_sms_config_by_id'],
                'permission_callback' => api_permission_check(),
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'update_sms_config'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_sms_config_schema(true),
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [$this, 'delete_sms_config'],
                'permission_callback' => api_permission_check(),
            ],
        ]);

        // Route to get all WooCommerce statuses
        register_rest_route(__API_NAMESPACE, '/woo-statuses', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_all_woocommerce_statuses'],
                'permission_callback' => api_permission_check(),
            ],
        ]);
    }

    /**
     * Get all SMS configurations
     */
    public function get_all_sms_configs() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM {$this->table_name}", ARRAY_A);

        if (empty($results)) {
            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'No SMS configurations found.',
                'data'    => [],
            ], 200);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'SMS configurations retrieved successfully.',
            'data'    => $results,
        ], 200);
    }

    /**
     * Create a new SMS configuration
     */
    public function create_sms_config(WP_REST_Request $request) {
        global $wpdb;
    
        $status = sanitize_text_field($request->get_param('status'));
        $message = sanitize_text_field($request->get_param('message') ?? '');
        $message_for = sanitize_text_field($request->get_param('message_for') ?? 'customer');
        $phone_number = sanitize_text_field($request->get_param('phone_number') ?? '');
        $is_active = (int)($request->get_param('is_active') ?? 1);
        $created_at = current_time('mysql');
        $updated_at = current_time('mysql');
    
        // Validate message_for value
        if (!in_array($message_for, ['admin', 'customer'])) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Invalid value for message_for. Allowed values are admin or customer.',
            ], 400);
        }
    
        // Check for uniqueness of status and message_for
        $existing_record = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE status = %s AND message_for = %s",
                $status,
                $message_for
            ),
            ARRAY_A
        );
    
        if ($existing_record) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'A configuration with this status and receiver already exists.',
            ], 400);
        }
    
        // Insert the new SMS configuration
        $inserted = $wpdb->insert(
            $this->table_name,
            [
                'status'      => $status,
                'message'     => $message,
                'message_for' => $message_for,
                'phone_number' => $phone_number,
                'is_active'   => $is_active,
                'created_at'  => $created_at,
                'updated_at'  => $updated_at,
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
            ]
        );
    
        if ($inserted === false) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Failed to create SMS configuration.',
            ], 500);
        }
    
        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'SMS configuration created successfully.',
            'data'    => [
                'id'          => $wpdb->insert_id,
                'status'      => $status,
                'message'     => $message,
                'message_for' => $message_for,
                'phone_number' => $phone_number,
                'is_active'   => $is_active,
                'created_at'  => $created_at,
                'updated_at'  => $updated_at,
            ],
        ], 201);
    }
    

    /**
     * Update an SMS configuration by ID
     */
    public function update_sms_config(WP_REST_Request $request) {
        global $wpdb;

        $id = $request->get_param('id');
        $status = sanitize_text_field($request->get_param('status'));
        $message = sanitize_text_field($request->get_param('message') ?? '');
        $message_for = sanitize_text_field($request->get_param('message_for') ?? 'customer');
        $phone_number = sanitize_text_field($request->get_param('phone_number') ?? 'customer');
        $is_active = (int)($request->get_param('is_active') ?? 1);
        $updated_at = current_time('mysql');

        if (!in_array($message_for, ['admin', 'customer'])) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Invalid value for message_for. Allowed values are admin or customer.',
            ], 400);
        }


        // Check for unique combination of status and message_for
        $existing_entry = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE status = %s AND message_for = %s AND id != %d",
                $status,
                $message_for,
                $id
            )
        );

        if ($existing_entry) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'An entry with the same status and receiver already exists.',
            ], 400);
        }

        $updated = $wpdb->update(
            $this->table_name,
            [
                'status'      => $status,
                'message'     => $message,
                'message_for' => $message_for,
                'phone_number' => $phone_number,
                'is_active'   => $is_active,
                'updated_at'  => $updated_at,
            ],
            ['id' => $id],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
            ],
            ['%d']
        );

        if ($updated === false) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Failed to update SMS configuration.',
            ], 500);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'SMS configuration updated successfully.',
            'data'    => [
                'id'          => $id,
                'status'      => $status,
                'message'     => $message,
                'message_for' => $message_for,
                'phone_number' => $phone_number,
                'is_active'   => $is_active,
                'updated_at'  => $updated_at,
            ],
        ], 200);
    }
    
    /**
     * Get all WooCommerce statuses
     */
    public function get_all_woocommerce_statuses(WP_REST_Request $request) {
        $statuses = wc_get_order_statuses();

        if (empty($statuses)) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'No WooCommerce statuses found.',
            ], 404);
        }

        $response = [];
        foreach($statuses as $key => $value) {
            $response[] = [
                'title' => $value,
                'slug' => $key
            ];
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'WooCommerce statuses retrieved successfully.',
            'data'    => $response,
        ], 200);
    }

    /**
     * Delete an SMS configuration by ID.
     *
     * @param WP_REST_Request $request The API request containing the ID.
     * @return WP_REST_Response The response indicating the result of the deletion.
     */
    public function delete_sms_config(WP_REST_Request $request) {
        global $wpdb;

        // Retrieve the ID from the request
        $id = (int) $request->get_param('id');

        // Perform the deletion
        $deleted = $wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );

        if ($deleted === false) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Failed to delete SMS configuration.',
            ], 500);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'SMS configuration deleted successfully.',
        ], 200);
    }


    /**
     * Schema for SMS configuration input validation
     */
    private function get_sms_config_schema($require_id = false) {
        $schema = [
            'status' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'Status of the SMS configuration.',
            ],
            'message' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'Message content for the SMS configuration.',
            ],
            'phone_number' => [
                'required'    => false,
                'type'        => 'string',
                'description' => 'Phone number of the admin',
            ],
            'message_for' => [
                'required'    => false,
                'type'        => 'string',
                'default'     => 'customer',
                'enum'        => ['admin', 'customer'],
                'description' => 'Target audience for the message (admin or customer).',
            ],
            'is_active' => [
                'required'    => true,
                'type'        => 'boolean',
                'default'     => true,
                'description' => 'Active status of the SMS configuration.',
            ],
        ];

        if ($require_id) {
            $schema['id'] = [
                'required'    => true,
                'type'        => 'integer',
                'description' => 'Unique identifier for the SMS configuration.',
            ];
        }

        return $schema;
    }
}