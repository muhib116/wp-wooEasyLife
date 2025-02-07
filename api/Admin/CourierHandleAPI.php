<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class CourierHandleAPI extends WP_REST_Controller
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes()
    {
        register_rest_route(__API_NAMESPACE, '/courier-data', [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'store_courier_data'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_courier_data_schema(),
            ],
        ]);
        // Route for bulk storing courier data
        register_rest_route(__API_NAMESPACE, '/courier-data/bulk', [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'bulk_store_courier_data'],
                'permission_callback' => api_permission_check(),
            ],
        ]);

        register_rest_route(__API_NAMESPACE, '/courier-data/(?P<order_id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_courier_data'],
                'permission_callback' => api_permission_check(),
                'args'                => [
                    'order_id' => [
                        'required'    => true,
                        'type'        => 'integer',
                        'description' => 'The ID of the order to retrieve courier data for.',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Store courier data for an order
     */
    public function store_courier_data(WP_REST_Request $request)
    {
        global $wpdb;
    
        // Get and validate order ID
        $order_id = $request->get_param('order_id');
        $order = wc_get_order($order_id);
        if (!$order) {
            return new WP_Error('invalid_order', 'The provided order ID is invalid.', ['status' => 404]);
        }
    
        // Prepare courier data
        $courier_data = [
            'tracking_code'       => sanitize_text_field($request->get_param('tracking_code')),
            'invoice'             => sanitize_text_field($request->get_param('invoice')),
            'partner'             => sanitize_text_field($request->get_param('partner')),
            'consignment_id'      => sanitize_text_field($request->get_param('consignment_id')),
            'status'              => sanitize_text_field($request->get_param('status')),
            'parcel_tracking_link' => esc_url_raw($request->get_param('parcel_tracking_link')),
            'created_at'          => current_time('mysql'),
            'updated_at'          => current_time('mysql'),
        ];
    
        // Validate required fields
        foreach (['tracking_code', 'invoice', 'partner', 'consignment_id', 'status'] as $field) {
            if (empty($courier_data[$field])) {
                return new WP_Error('missing_data', ucfirst(str_replace('_', ' ', $field)) . ' is required.', ['status' => 400]);
            }
        }
    
        // Insert courier data into wp_wc_orders_meta table
        $table_name = $wpdb->prefix . 'wc_orders_meta';
        $result = $wpdb->insert(
            $table_name,
            [
                'order_id' => $order_id,
                'meta_key' => '_courier_data',
                'meta_value' => maybe_serialize($courier_data),
            ],
            [
                '%d', // order_id
                '%s', // meta_key
                '%s', // meta_value
            ]
        );
    
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to store courier data in the database.', ['status' => 500]);
        }
    
        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Courier data saved successfully.',
            'data'    => $courier_data,
        ], 200);
    }

    /**
     * Store courier data for bulk order
     */
    public function bulk_store_courier_data(WP_REST_Request $request)
    {
        global $wpdb;

        // Get payload
        $payload = $request->get_json_params();

        // Check if payload is valid and not empty
        if (!is_array($payload) || empty($payload)) {
            return new WP_Error('invalid_payload', 'The payload is invalid or empty.', ['status' => 400]);
        }

        $table_name = $wpdb->prefix . 'wc_orders_meta';
        $responses = [];
        $current_time = current_time('mysql');

        foreach ($payload as $data) {
            // Validate order ID
            $order_id = isset($data['order_id']) ? intval($data['order_id']) : null;
            $order = wc_get_order($order_id);

            if (!$order) {
                $responses[] = [
                    'order_id' => $order_id,
                    'status'   => 'error',
                    'message'  => 'Invalid order ID.',
                ];
                continue;
            }

            // update status to courier entry
            $order->update_status('wc-courier-entry', 'Status updated via API', true);

            // Prepare courier data
            $courier_data = [
                'tracking_code'       => sanitize_text_field($data['tracking_code'] ?? 'not-available'),
                'invoice'             => sanitize_text_field($data['invoice'] ?? ''),
                'partner'             => sanitize_text_field($data['partner'] ?? ''),
                'consignment_id'      => sanitize_text_field($data['consignment_id'] ?? 'not-available'),
                'status'              => sanitize_text_field($data['status'] ?? ''),
                'parcel_tracking_link' => esc_url_raw($data['parcel_tracking_link'] ?? ''),
                'created_at'          => $current_time,
                'updated_at'          => $current_time,
            ];

            // Validate required fields
            foreach (['tracking_code', 'invoice', 'partner', 'consignment_id', 'status'] as $field) {
                if (empty($courier_data[$field])) {
                    $responses[] = [
                        'order_id' => $order_id,
                        'status'   => 'error',
                        'message'  => ucfirst(str_replace('_', ' ', $field)) . ' is required.',
                    ];
                    continue 2;
                }
            }

            // Insert courier data into wp_wc_orders_meta table
            $result = $wpdb->insert(
                $table_name,
                [
                    'order_id' => $order_id,
                    'meta_key' => '_courier_data',
                    'meta_value' => maybe_serialize($courier_data),
                ],
                [
                    '%d', // order_id
                    '%s', // meta_key
                    '%s', // meta_value
                ]
            );

            if ($result === false) {
                $responses[] = [
                    'order_id' => $order_id,
                    'status'   => 'error',
                    'message'  => 'Failed to store courier data in the database.',
                ];
            } else {
                $responses[] = [
                    'order_id' => $order_id,
                    'status'   => 'success',
                    'message'  => 'Courier data saved successfully.',
                    'data'     => $courier_data,
                ];
            }
        }

        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Bulk courier data processing completed.',
            'responses' => $responses,
        ], 200);
    }

    

    /**
     * Retrieve courier data for an order
     */
    public function get_courier_data(WP_REST_Request $request)
    {
        $order_id = $request->get_param('order_id');

        // Validate the order
        $order = wc_get_order($order_id);
        if (!$order) {
            return new WP_Error('invalid_order', 'The provided order ID is invalid.', ['status' => 404]);
        }

        // Retrieve courier data from order meta
        $courier_data = get_post_meta($order_id, '_courier_data', true);

        if (empty($courier_data)) {
            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'No courier data found for this order.',
                'data'    => [],
            ], 200);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Courier data retrieved successfully.',
            'data'    => $courier_data,
        ], 200);
    }

    /**
     * Define schema for storing courier data
     */
    private function get_courier_data_schema()
    {
        return [
            'order_id' => [
                'required'    => true,
                'type'        => 'integer',
                'description' => 'The ID of the order to associate courier data with.',
            ],
            'tracking_code' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'The tracking code for the courier.',
            ],
            'invoice' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'Invoice number for the courier shipment.',
            ],
            'partner' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'The courier partner handling the order.',
            ],
            'consignment_id' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'The consignment ID provided by the courier.',
            ],
            'status' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'The current status of the courier.',
            ],
            'parcel_tracking_link' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'The URL for tracking the parcel.',
                'format'      => 'url',
            ],
            'created_at' => [
                'required'    => false,
                'type'        => 'string',
                'description' => 'The timestamp when the courier data was created.',
                'format'      => 'date-time',
            ],
            'updated_at' => [
                'required'    => false,
                'type'        => 'string',
                'description' => 'The timestamp when the courier data was last updated.',
                'format'      => 'date-time',
            ],
        ];
    }

    /**
     * Schema for bulk courier data
     */
    private function get_bulk_courier_data_schema()
    {
        return [
            'required'    => true,
            'type'        => 'array',
            'description' => 'Array of courier data objects to store in bulk.',
            'items'       => [
                'type'       => 'object',
                'properties' => $this->get_courier_data_schema(),
            ],
        ];
    }
}