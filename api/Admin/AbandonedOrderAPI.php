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
    
        $cutoff_time = strtotime('-25 minutes'); // 25 minutes ago
        $cutoff_date = date('Y-m-d H:i:s', $cutoff_time);
    
        $query = $wpdb->prepare(
            "UPDATE {$this->table_name} 
            SET 
                status = 'abandoned', 
                abandoned_at = NOW(), 
                updated_at = NOW() 
            WHERE 
                status = 'active' 
                AND created_at < %s",
            $cutoff_date
        );
    
        $wpdb->query($query);
    }


    /**
     * Get all abandoned orders
     */
    public function get_all_abandoned_orders(WP_REST_Request $request) {
        global $wpdb;
        $this->mark_abandoned_carts();
    
        // Initialize query condition
        $query_conditions = "status != %s";
        $query_params = ['active'];
    
        // Add date range condition if start_date and end_date are defined
        $start_date = $request->get_param('start_date');
        $end_date = $request->get_param('end_date');
    
        if ($start_date && $end_date) {
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
    
        // Query the database
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE $query_conditions ORDER BY abandoned_at DESC",
                ...$query_params
            ),
            ARRAY_A
        );
    
        if (empty($results)) {
            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'No abandoned orders found.',
                'data'    => [],
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
