<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

class AbandonedOrderAPI extends WP_REST_Controller {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . __PREFIX . 'abandon_cart';
        
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route(__API_NAMESPACE, '/abandoned-orders', [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'get_all_abandoned_orders'],
                'permission_callback' => api_permission_check(),
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_abandoned_order'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_abandoned_order_schema(false),
            ],
        ]);

        register_rest_route(__API_NAMESPACE, '/abandoned-dashboard-data', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_abandoned_dashboard_data'],
                'permission_callback' => api_permission_check(),
            ]
        ]); 

        register_rest_route(__API_NAMESPACE, '/abandoned-orders/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_abandoned_order_by_id'],
                'permission_callback' => api_permission_check(),
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'update_abandoned_order'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_abandoned_order_schema(true),
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [$this, 'delete_abandoned_order'],
                'permission_callback' => api_permission_check(),
            ],
        ]);
    }

    /**
     * check for abandoned able carts and make it abandon
     */
    private function mark_abandoned_carts() {
        global $wpdb;
    
        $cutoff_time = strtotime('-120 minutes'); // 25 minutes ago
    
        // for testing when development environment start
        $server_ip = $_SERVER['SERVER_ADDR'];
        if ($server_ip === '127.0.0.1' || $server_ip === '::1') {
            $cutoff_time = strtotime('-1 minutes'); // 1 minute ago
        }
        // for testing when development environment end
    
        $cutoff_date = date('Y-m-d H:i:s', $cutoff_time);
    
        // Fetch all records matching the condition
        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
            WHERE status = 'active'
            AND created_at < %s",
            $cutoff_date
        );
    
        $records = $wpdb->get_results($query);
    
        // If records found, update them one by one
        $balance_cut_data = [];
        if (!empty($records)) {
            foreach ($records as $record) {
                $update_query = $wpdb->prepare(
                    "UPDATE {$this->table_name} 
                    SET 
                        status = 'abandoned', 
                        abandoned_at = NOW(), 
                        updated_at = NOW() 
                    WHERE id = %d",
                    $record->id
                );
    
                $wpdb->query($update_query);
    
                // // Call balance_cut function after each update
                $balance_cut_data[] = $this->balance_cut($record);
            }
        }

        return $balance_cut_data;
    }

    private function balance_cut($record) {
        global $license_key;
    
        $url = get_api_end_point("package-order-use");
        $cart_contents = $record->cart_contents;
        $total_value = $record->total_value;
        
        // Encode data properly for API request
        $data = json_encode([
            'order_count' => 1,
            'use_details' => [[
                "from" => "missing_order",
                "cart_contents" => $cart_contents,
                "total_value" => $total_value
            ]]
        ]);
    
        $headers = [
            'Authorization' => 'Bearer ' . $license_key,
            'Content-Type'  => 'application/json', // JSON format
            'origin' => site_url()
        ];
    
        // Use wp_remote_post for HTTP requests
        $response = wp_remote_post($url, [
            'method'      => 'POST',
            'body'        => $data,
            'headers'     => $headers,
            'timeout'     => 45,
            'sslverify'   => false,
        ]);

        error_log(json_encode($response));
        
        // Decode and return the response
        $response_body = wp_remote_retrieve_body($response);
        return $response_body;
    }
    
    /**
     * Get all abandoned orders with optional filters (status, start_date, end_date) and pagination
     */
    public function get_all_abandoned_orders(WP_REST_Request $request) {
        global $wpdb;
        $this->mark_abandoned_carts(); // Ensure abandoned carts are marked before fetching
    
        // Initialize query conditions
        $query_conditions = "1=1"; // Always true, allowing optional conditions
        $query_params = [];
    
        // Get status filter (optional) - Case Insensitive Search
        $status = $request->get_param('status');
        if (!empty($status)) {
            $status = sanitize_text_field($status);
            $query_conditions .= " AND LOWER(status) = LOWER(%s)";
            $query_params[] = $status;
        }
    
        // Get date filters (optional)
        $start_date = $request->get_param('start_date');
        $end_date = $request->get_param('end_date');
    
        if (!empty($start_date) && !empty($end_date)) {
            $start_date = sanitize_text_field($start_date);
            $end_date = sanitize_text_field($end_date);
    
            // Validate date format
            if (!strtotime($start_date) || !strtotime($end_date)) {
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'Invalid date format. Use YYYY-MM-DD.',
                ], 400);
            }
    
            $query_conditions .= " AND abandoned_at BETWEEN %s AND %s";
            $query_params[] = $start_date . ' 00:00:00';
            $query_params[] = $end_date . ' 23:59:59';
        }
    
        // Get pagination parameters
        $page = max(1, intval($request->get_param('page') ?? 1));
        $per_page = max(1, intval($request->get_param('per_page') ?? 10));
        $offset = ($page - 1) * $per_page;
    
        // Get total count of filtered abandoned orders
        $total_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE $query_conditions",
            ...$query_params
        ));
    
        // Query the database with pagination
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE $query_conditions ORDER BY abandoned_at DESC LIMIT %d OFFSET %d",
                ...array_merge($query_params, [$per_page, $offset])
            ),
            ARRAY_A
        );
    
        // If no results found
        if (empty($results)) {
            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'No abandoned orders found.',
                'data'    => [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page'     => $per_page,
                    'total_count'  => $total_count,
                    'total_pages'  => ceil($total_count / $per_page),
                ],
            ], 200);
        }
    
        // Deserialize cart_contents for each result
        foreach ($results as &$result) {
            if (isset($result['cart_contents'])) {
                $result['cart_contents'] = maybe_unserialize($result['cart_contents']);
            }
        }
    
        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Abandoned orders retrieved successfully.',
            'data'    => $results,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $per_page,
                'total_count'  => $total_count,
                'total_pages'  => ceil($total_count / $per_page),
            ],
        ], 200);
    }
    

    public function get_abandoned_dashboard_data(WP_REST_Request $request) {
        global $wpdb;
    
        $start_date = $request->get_param('start_date');
        $end_date = $request->get_param('end_date');
    
        // Default date range (last 7 days)
        if (!$start_date) {
            $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
        }
        if (!$end_date) {
            $end_date = current_time('mysql');
        }
    
        // Query for different statistics
        $stats = [];
    
        // Total Abandoned Orders (case-insensitive search)
        $stats['total_abandoned_orders'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name WHERE LOWER(status) = LOWER(%s) AND abandoned_at BETWEEN %s AND %s",
            'abandoned', $start_date, $end_date
        ));
    
        // Total Remaining Abandoned Orders (Not recovered)
        $stats['total_remaining_abandoned'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name WHERE LOWER(status) = LOWER(%s) AND recovered_at IS NULL AND abandoned_at BETWEEN %s AND %s",
            'abandoned', $start_date, $end_date
        ));
    
        // Total Lost Amount (Sum of total_value where abandoned)
        $stats['lost_amount'] = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total_value) FROM $this->table_name WHERE LOWER(status) = LOWER(%s) AND abandoned_at BETWEEN %s AND %s",
            'abandoned', $start_date, $end_date
        ));
        $stats['lost_amount'] = $stats['lost_amount'] ?: 0;
    
        // Total Recovered Orders
        $stats['total_recovered_orders'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name WHERE LOWER(status) = LOWER(%s) AND recovered_at BETWEEN %s AND %s",
            'recovered', $start_date, $end_date
        ));
    
        // Total Recovered Amount
        $stats['recovered_amount'] = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total_value) FROM $this->table_name WHERE LOWER(status) = LOWER(%s) AND recovered_at BETWEEN %s AND %s",
            'recovered', $start_date, $end_date
        ));
        $stats['recovered_amount'] = $stats['recovered_amount'] ?: 0;
    
        // Total Active Carts (Not yet abandoned)
        $stats['total_active_carts'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name WHERE LOWER(status) = LOWER(%s) AND created_at BETWEEN %s AND %s",
            'active', $start_date, $end_date
        ));
    
        // Total Confirmed Orders (if applicable)
        $stats['total_confirmed_orders'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name WHERE LOWER(status) = LOWER(%s) AND created_at BETWEEN %s AND %s",
            'confirmed', $start_date, $end_date
        ));
    
        // Total call not received Orders (if applicable)
        $stats['total_call_not_received_orders'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name WHERE LOWER(status) = LOWER(%s) AND created_at BETWEEN %s AND %s",
            'call-not-received', $start_date, $end_date
        ));
    
        // Average Cart Value (for abandoned orders)
        $stats['average_cart_value'] = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(total_value) FROM $this->table_name WHERE LOWER(status) = LOWER(%s) AND abandoned_at BETWEEN %s AND %s",
            'abandoned', $start_date, $end_date
        ));
        $stats['average_cart_value'] = $stats['average_cart_value'] ?: 0;
    
        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Abandoned dashboard data retrieved successfully.',
            'data'    => $stats
        ], 200);
    }
    
    

    /**
     * Create a new abandoned order
     */
    public function create_abandoned_order(WP_REST_Request $request) {
        global $wpdb;

        $customer_email = sanitize_email($request->get_param('customer_email'));
        $cart_contents = maybe_serialize($request->get_param('cart_contents'));
        $total_value   = floatval($request->get_param('total_value'));
        $abandoned_at  = current_time('mysql');
        $updated_at    = current_time('mysql');

        // Insert the new abandoned order
        $inserted = $wpdb->insert(
            $this->table_name,
            [
                'customer_email' => $customer_email,
                'cart_contents'  => $cart_contents,
                'total_value'    => $total_value,
                'abandoned_at'   => $abandoned_at,
                'updated_at'     => $updated_at,
            ],
            [
                '%s',
                '%s',
                '%f',
                '%s',
                '%s',
            ]
        );

        if ($inserted === false) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Failed to create abandoned order.',
            ], 500);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Abandoned order created successfully.',
            'data'    => [
                'id'             => $wpdb->insert_id,
                'customer_email' => $customer_email,
                'cart_contents'  => maybe_unserialize($cart_contents),
                'total_value'    => $total_value,
                'abandoned_at'   => $abandoned_at,
                'updated_at'     => $updated_at,
            ],
        ], 201);
    }

    /**
     * Get an abandoned order by ID
     */
    public function get_abandoned_order_by_id(WP_REST_Request $request) {
        global $wpdb;

        $id = $request->get_param('id');
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id),
            ARRAY_A
        );

        if (empty($result)) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Abandoned order not found.',
            ], 404);
        }

        // Deserialize cart_contents
        if (isset($result['cart_contents'])) {
            $result['cart_contents'] = maybe_unserialize($result['cart_contents']);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Abandoned order retrieved successfully.',
            'data'    => $result,
        ], 200);
    }

    /**
     * Update an abandoned order by ID
     */
    public function update_abandoned_order(WP_REST_Request $request) {
        global $wpdb;
    
        $id             = $request->get_param('id');
        $customer_email = sanitize_email($request->get_param('customer_email')) ?? '';
        $cart_contents  = maybe_serialize($request->get_param('cart_contents'));
        $total_value    = floatval($request->get_param('total_value'));
        $status         = sanitize_text_field($request->get_param('status')); // Get status from request
        $updated_at     = current_time('mysql');
        $recovered_at   = ($status === 'recovered') ? current_time('mysql') : null; // Set recovered_at for "recovered" status
    
        $updated = $wpdb->update(
            $this->table_name,
            [
                'customer_email' => $customer_email,
                'cart_contents'  => $cart_contents,
                'total_value'    => $total_value,
                'status'         => $status, // Update status
                'recovered_at'   => $recovered_at, // Update recovered_at
                'updated_at'     => $updated_at,
            ],
            ['id' => $id],
            [
                '%s', // customer_email
                '%s', // cart_contents
                '%f', // total_value
                '%s', // status
                '%s', // recovered_at (NULL or datetime)
                '%s', // updated_at
            ],
            ['%d'] // ID
        );
    
        if ($updated === false) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Failed to update abandoned order.',
            ], 500);
        }
    
        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Abandoned order updated successfully.',
            'data'    => [
                'id'             => $id,
                'customer_email' => $customer_email,
                'cart_contents'  => maybe_unserialize($cart_contents),
                'total_value'    => $total_value,
                'status'         => $status,
                'recovered_at'   => $recovered_at,
                'updated_at'     => $updated_at,
            ],
        ], 200);
    }
    

    /**
     * Delete an abandoned order by ID
     */
    public function delete_abandoned_order(WP_REST_Request $request) {
        global $wpdb;

        $id = $request->get_param('id');
        $deleted = $wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );

        if ($deleted === false) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Failed to delete abandoned order.',
            ], 500);
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Abandoned order deleted successfully.',
        ], 200);
    }

    /**
     * Schema for abandoned order input validation
     */
    private function get_abandoned_order_schema($require_id = false) {
        $schema = [];

        if ($require_id) {
            $schema['id'] = [
                'required'    => true,
                'type'        => 'integer',
                'description' => 'Unique identifier for the abandoned order.',
            ];
        }

        return $schema;
    }
}
