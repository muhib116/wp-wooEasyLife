<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

class SMSHistoryAPI extends WP_REST_Controller {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . __PREFIX . 'sms_history';

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route(__API_NAMESPACE, '/sms-history', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_all_sms_history'],
                'permission_callback' => api_permission_check(),
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_sms_history'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_sms_history_schema(false),
            ],
        ]);

        register_rest_route(__API_NAMESPACE, '/sms-history/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_sms_history_by_id'],
                'permission_callback' => api_permission_check(),
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [$this, 'delete_sms_history'],
                'permission_callback' => api_permission_check(),
            ],
        ]);
    }

    /**
     * Get all SMS history
     */
    public function get_all_sms_history() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM {$this->table_name}", ARRAY_A);

        if (empty($results)) {
            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'No SMS history found.',
                'data'    => [],
            ], 200);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'SMS history retrieved successfully.',
            'data'    => $results,
        ], 200);
    }

    /**
     * Create a new SMS history record
     */
    public function create_sms_history(WP_REST_Request $request) {
        global $wpdb;

        $phone_number = sanitize_text_field($request->get_param('phone_number'));
        $message = sanitize_textarea_field($request->get_param('message'));
        $status = sanitize_text_field($request->get_param('status'));
        $error_message = sanitize_textarea_field($request->get_param('error_message') ?? null);
        $created_at = current_time('mysql');
        $updated_at = current_time('mysql');

        // Insert the SMS history record
        $inserted = $wpdb->insert(
            $this->table_name,
            [
                'phone_number'  => $phone_number,
                'message'       => $message,
                'status'        => $status,
                'error_message' => $error_message,
                'created_at'    => $created_at,
                'updated_at'    => $updated_at,
            ],
            [
                '%s',
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
                'message' => 'Failed to create SMS history.',
            ], 500);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'SMS history created successfully.',
            'data'    => [
                'id'            => $wpdb->insert_id,
                'phone_number'  => $phone_number,
                'message'       => $message,
                'status'        => $status,
                'error_message' => $error_message,
                'created_at'    => $created_at,
                'updated_at'    => $updated_at,
            ],
        ], 201);
    }

    /**
     * Get SMS history by ID
     */
    public function get_sms_history_by_id(WP_REST_Request $request) {
        global $wpdb;

        $id = (int) $request->get_param('id');
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$result) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'SMS history not found.',
            ], 404);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'SMS history retrieved successfully.',
            'data'    => $result,
        ], 200);
    }

    /**
     * Delete an SMS history record by ID
     */
    public function delete_sms_history(WP_REST_Request $request) {
        global $wpdb;

        $id = (int) $request->get_param('id');

        $deleted = $wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );

        if ($deleted === false) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Failed to delete SMS history.',
            ], 500);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'SMS history deleted successfully.',
        ], 200);
    }

    /**
     * Schema for SMS history input validation
     */
    private function get_sms_history_schema($require_id = false) {
        $schema = [
            'phone_number' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'Phone number to which the SMS was sent.',
            ],
            'message' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'Content of the SMS message.',
            ],
            'status' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'Status of the SMS message.',
            ],
            'error_message' => [
                'required'    => false,
                'type'        => 'string',
                'description' => 'Error message if the SMS failed.',
            ],
        ];

        if ($require_id) {
            $schema['id'] = [
                'required'    => true,
                'type'        => 'integer',
                'description' => 'Unique identifier for the SMS history record.',
            ];
        }

        return $schema;
    }
}