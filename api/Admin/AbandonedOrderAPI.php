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
    
        $cutoff_time = strtotime('-25 minutes'); // 25 minutes ago
    
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
                $now = current_time('mysql'); // Get the current time in MySQL format
                $update_query = $wpdb->prepare(
                    "UPDATE {$this->table_name} 
                    SET 
                        status = 'abandoned', 
                        abandoned_at = %s, 
                        updated_at = %s 
                    WHERE id = %d",
                    $now, $now, $record->id
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
     * Log errors with context
     */
    private function log_error($message, $context = []) {
        $log_message = sprintf(
            '[AbandonedOrderAPI] %s | Context: %s | Time: %s',
            $message,
            json_encode($context),
            current_time('Y-m-d H:i:s')
        );
        
        error_log($log_message);
        
        // Also log to custom file
        $log_file = WP_CONTENT_DIR . '/uploads/woo-easy-life-logs/abandoned-order-api.log';
        $log_dir = dirname($log_file);
        
        if (!is_dir($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        if (is_writable($log_dir)) {
            file_put_contents($log_file, $log_message . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Get all abandoned orders with optional filters and pagination
     */
    public function get_all_abandoned_orders(WP_REST_Request $request) {
        global $wpdb;
        
        try {
            // Mark abandoned carts before fetching
            $this->mark_abandoned_carts();
            
            // Initialize query conditions
            $query_conditions = "1=1";
            $query_params = [];
            
            // Status filter with validation
            $status = $request->get_param('status');
            if (!empty($status)) {
                $status = sanitize_text_field($status);
                $valid_statuses = ['active', 'abandoned', 'confirmed', 'call-not-received', 'canceled'];
                
                if (!in_array(strtolower($status), array_map('strtolower', $valid_statuses))) {
                    $this->log_error('Invalid status filter', ['status' => $status]);
                    return new WP_REST_Response([
                        'status'  => 'error',
                        'message' => 'Invalid status. Valid options: ' . implode(', ', $valid_statuses),
                    ], 400);
                }
                
                $query_conditions .= " AND LOWER(status) = LOWER(%s)";
                $query_params[] = $status;
            }
            
            // Date filters with validation
            $start_date = $request->get_param('start_date');
            $end_date = $request->get_param('end_date');
            
            if (!empty($start_date) && !empty($end_date)) {
                $start_date = sanitize_text_field($start_date);
                $end_date = sanitize_text_field($end_date);
                
                // Validate date format
                if (!strtotime($start_date) || !strtotime($end_date)) {
                    $this->log_error('Invalid date format', [
                        'start_date' => $start_date,
                        'end_date' => $end_date
                    ]);
                    
                    return new WP_REST_Response([
                        'status'  => 'error',
                        'message' => 'Invalid date format. Use YYYY-MM-DD.',
                    ], 400);
                }
                
                // Validate date range
                if (strtotime($start_date) > strtotime($end_date)) {
                    return new WP_REST_Response([
                        'status'  => 'error',
                        'message' => 'Start date cannot be after end date.',
                    ], 400);
                }
                
                $query_conditions .= " AND abandoned_at BETWEEN %s AND %s";
                $query_params[] = $start_date . ' 00:00:00';
                $query_params[] = $end_date . ' 23:59:59';
            }
            
            // Pagination with validation
            $page = max(1, intval($request->get_param('page') ?? 1));
            $per_page = max(1, min(100, intval($request->get_param('per_page') ?? 10))); // Max 100 per page
            $offset = ($page - 1) * $per_page;
            
            // Get total count with error handling
            $total_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE $query_conditions",
                ...$query_params
            ));
            
            if ($wpdb->last_error) {
                $this->log_error('Database error getting total count', [
                    'error' => $wpdb->last_error,
                    'query' => $wpdb->last_query
                ]);
                
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'Database error occurred while counting records.',
                ], 500);
            }
            
            $total_count = intval($total_count);
            
            // Query the database with pagination
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$this->table_name} WHERE $query_conditions ORDER BY abandoned_at DESC LIMIT %d OFFSET %d",
                    ...array_merge($query_params, [$per_page, $offset])
                ),
                ARRAY_A
            );
            
            if ($wpdb->last_error) {
                $this->log_error('Database error getting results', [
                    'error' => $wpdb->last_error,
                    'query' => $wpdb->last_query
                ]);
                
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'Database error occurred while fetching records.',
                ], 500);
            }
            
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
                        'total_pages'  => max(1, ceil($total_count / $per_page)),
                    ],
                ], 200);
            }
            
            // Process results with error handling
            foreach ($results as &$result) {
                try {
                    // Get WooCommerce order data (both recent and lifetime)
                    $wc_data = $this->get_wc_order_data_by_abandoned_data($result);
                    
                    // Extract recent order data
                    $result['last_wc_order_current_status'] = $wc_data['recent_order']['last_wc_order_current_status'] ?? '';
                    $result['last_wc_order_at'] = $wc_data['recent_order']['last_wc_order_at'] ?? '';
                    $result['last_wc_order_id'] = $wc_data['recent_order']['last_wc_order_id'] ?? null;
                    
                    // Add lifetime orders data
                    $result['lifetime_orders'] = $wc_data['lifetime_orders'] ?? [];
                    
                    // Format dates with null checks
                    $result['created_at'] = !empty($result['created_at']) ? 
                        human_time_difference(strtotime($result['created_at'])) : '';
                    
                    $result['abandoned_at'] = !empty($result['abandoned_at']) ? 
                        human_time_difference(strtotime($result['abandoned_at'])) : '';
                    
                    $result['recovered_at'] = !empty($result['recovered_at']) ? 
                        human_time_difference(strtotime($result['recovered_at'])) : '';
                    
                    // Deserialize cart contents safely
                    if (isset($result['cart_contents'])) {
                        $unserialized = maybe_unserialize($result['cart_contents']);
                        $result['cart_contents'] = is_array($unserialized) ? $unserialized : [];
                    }
                    
                } catch (Exception $e) {
                    $this->log_error('Error processing result', [
                        'result_id' => $result['id'] ?? 'unknown',
                        'exception' => $e->getMessage()
                    ]);
                    
                    // Set default values on error
                    $result['last_wc_order_current_status'] = '';
                    $result['last_wc_order_at'] = '';
                    $result['last_wc_order_id'] = null;
                    $result['lifetime_orders'] = [];
                    $result['cart_contents'] = [];
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
                    'total_pages'  => max(1, ceil($total_count / $per_page)),
                ],
            ], 200);
            
        } catch (Exception $e) {
            $this->log_error('Exception in get_all_abandoned_orders', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_params' => $request->get_params()
            ]);
            
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'An unexpected error occurred while fetching abandoned orders.',
            ], 500);
        }
    }
    
    private function get_wc_order_data_by_abandoned_data($abandonedOrder) {
        try {
            $customer_phone = $abandonedOrder["customer_phone"] ?? '';
            $customer_email = $abandonedOrder["customer_email"] ?? '';

            // Validate input
            if (empty($customer_phone) && empty($customer_email)) {
                $this->log_error('No phone or email provided for WC order lookup', [
                    'abandoned_order_id' => $abandonedOrder['id'] ?? 'unknown'
                ]);
                
                return [
                    'recent_order' => [
                        'last_wc_order_current_status' => '',
                        'last_wc_order_at' => false,
                        'last_wc_order_id' => null
                    ],
                    'lifetime_orders' => []
                ];
            }

            // Get all available WooCommerce order statuses
            $all_statuses = array_keys(wc_get_order_statuses());
            
            // Base args for WooCommerce orders query
            $base_args = [
                'type' => 'shop_order',
                'status' => $all_statuses
            ];

            // Add search criteria
            if (!empty($customer_phone)) {
                $base_args['billing_phone'] = $customer_phone;
            } elseif (!empty($customer_email)) {
                $base_args['billing_email'] = $customer_email;
            }

            // Log search parameters
            $this->log_error('Searching for WC orders', [
                'search_phone' => $customer_phone,
                'search_email' => $customer_email,
                'abandoned_order_id' => $abandonedOrder['id'] ?? 'unknown'
            ]);

            // 1. GET RECENT ORDER (Most recent order)
            $recent_args = array_merge($base_args, [
                'limit' => 1,
                'orderby' => 'date',
                'order' => 'DESC'
            ]);

            $recent_orders = wc_get_orders($recent_args);
            $recent_order_data = [
                'last_wc_order_current_status' => '',
                'last_wc_order_at' => false,
                'last_wc_order_id' => null
            ];

            if (!empty($recent_orders)) {
                $recent_order = $recent_orders[0];
                
                if ($recent_order && is_object($recent_order)) {
                    $wc_order_date = $recent_order->get_date_created();
                    $order_status = $recent_order->get_status();
                    
                    $recent_order_data = [
                        'last_wc_order_current_status' => $order_status,
                        'last_wc_order_at' => $wc_order_date ? human_time_difference($wc_order_date->getTimestamp(), null, true) : false,
                        'last_wc_order_id' => $recent_order->get_id()
                    ];
                }
            }

            // 2. GET LIFETIME ORDERS GROUPED BY STATUS
            $lifetime_args = array_merge($base_args, [
                'limit' => -1, // Get all orders
                'orderby' => 'date',
                'order' => 'DESC'
            ]);

            $all_orders = wc_get_orders($lifetime_args);
            $lifetime_orders = [];
            
            if (!empty($all_orders)) {
                // Group orders by status
                $status_groups = [];
                
                foreach ($all_orders as $order) {
                    if (!$order || !is_object($order)) continue;
                    
                    $status = $order->get_status();
                    $order_date = $order->get_date_created();
                    $formatted_date = $order_date ? $order_date->format('M j, Y') : '';
                    
                    if (!isset($status_groups[$status])) {
                        $status_groups[$status] = [
                            'count' => 0,
                            'order_dates' => []
                        ];
                    }
                    
                    $status_groups[$status]['count']++;
                    if ($formatted_date) {
                        $status_groups[$status]['order_dates'][] = $formatted_date;
                    }
                }
                
                // Format the grouped data
                foreach ($status_groups as $status => $data) {
                    // Get status label from WooCommerce
                    $status_labels = wc_get_order_statuses();
                    $status_key = 'wc-' . $status;
                    $status_title = isset($status_labels[$status_key]) ? $status_labels[$status_key] : ucwords(str_replace('-', ' ', $status));
                    
                    // Limit to last 3 dates for display
                    $recent_dates = array_slice($data['order_dates'], 0, 3);
                    
                    $lifetime_orders[] = [
                        'title' => $status_title,
                        'status' => $status,
                        'count' => $data['count'],
                        'order_at' => implode(', ', $recent_dates),
                        'all_dates' => $data['order_dates'] // Include all dates if needed
                    ];
                }
                
                // Sort by count (descending)
                usort($lifetime_orders, function($a, $b) {
                    return $b['count'] - $a['count'];
                });
            }

            $results = [
                'recent_order' => $recent_order_data,
                'lifetime_orders' => $lifetime_orders
            ];

            // Log successful data retrieval
            $this->log_error('Successfully retrieved WC order data', [
                'abandoned_order_id' => $abandonedOrder['id'] ?? 'unknown',
                'recent_order_found' => !empty($recent_orders),
                'total_lifetime_orders' => count($all_orders),
                'status_groups' => count($lifetime_orders)
            ]);

            return $results;
            
        } catch (Exception $e) {
            $this->log_error('Exception in get_wc_order_data_by_abandoned_data', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'abandoned_order' => $abandonedOrder
            ]);
            
            return [
                'recent_order' => [
                    'last_wc_order_current_status' => '',
                    'last_wc_order_at' => false,
                    'last_wc_order_id' => null
                ],
                'lifetime_orders' => []
            ];
        }
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

        // Total Confirmed Amount (Sum of total_value where confirmed)
        $stats['confirmed_amount'] = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total_value) FROM $this->table_name WHERE LOWER(status) = LOWER(%s) AND abandoned_at BETWEEN %s AND %s",
            'confirmed', $start_date, $end_date
        ));
        $stats['confirmed_amount'] = $stats['confirmed_amount'] ?: 0;

    
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
        $recovered_at   = ($status === 'confirmed') ? current_time('mysql') : null; // Set recovered_at for "recovered" status
    
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
