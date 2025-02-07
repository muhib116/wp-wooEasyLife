<?php

namespace WooEasyLife\API\Admin;

use WooEasyLife\Admin\HandlePastNewOrders;

class OrderListAPI
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Registers the routes for the custom endpoint.
     */
    public function register_routes()
    {
        $handlePastNewOrders = new HandlePastNewOrders();

        register_rest_route(
            __API_NAMESPACE, // Namespace and version.
            '/orders',         // Endpoint: /orders
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_orders'],
                'permission_callback' => api_permission_check(), // Allow public access (modify as needed).
                'args'                => [
                    'status' => [
                        'required' => false,
                        'type'     => 'string',
                        'default'  => 'any',
                        'description' => 'Filter orders by status (e.g., processing, completed).',
                    ],
                    'per_page' => [
                        'required' => false,
                        'type'     => 'integer',
                        'default'  => 10,
                        'description' => 'Number of orders per page.',
                    ],
                    'page' => [
                        'required' => false,
                        'type'     => 'integer',
                        'default'  => 1,
                        'description' => 'Page number for pagination.',
                    ],
                ],
            ]
        );

        register_rest_route(
            __API_NAMESPACE, // Namespace and version.
            '/status-with-counts',         // Endpoint: /status-with-counts
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_order_status_with_counts'],
                'permission_callback' => api_permission_check(), // Allow public access (modify as needed).
            ]
        );

        register_rest_route(
            __API_NAMESPACE, 
            '/save-order-notes', 
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'save_order_notes'],
                'permission_callback' => api_permission_check(), // Or add your permission logic here
            ]
        );

        register_rest_route(
            __API_NAMESPACE, 
            '/orders/change-status',
            [
                'methods'  => 'POST',
                'callback' => [$this, 'change_order_status'], // Ensure this function exists and is callable
                'permission_callback' => api_permission_check(), // Ensure this exists and returns true/false
            ]
        );

        register_rest_route(
            __API_NAMESPACE, 
            '/check-fraud-customer',
            [
                'methods'  => 'POST',
                'callback' => [$this, 'check_fraud_customer'], // Ensure this function exists and is callable
                'permission_callback' => api_permission_check()
            ]
        );

        register_rest_route(
            __API_NAMESPACE, 
            '/update-courier-data',
            [
                'methods'  => 'POST',
                'callback' => [$this, 'update_courier_data'], // Ensure this function exists and is callable
                'permission_callback' => api_permission_check(),
            ]
        );

        register_rest_route(
            __API_NAMESPACE, 
            '/include-past-new-orders-to-wel-plugin',
            [
                'methods'  => 'PUT',
                'callback' => [$handlePastNewOrders, 'include_past_new_orders_to_wel_plugin'], // Ensure this function exists and is callable
                'permission_callback' => api_permission_check(),
            ]
        );
        register_rest_route(
            __API_NAMESPACE, 
            '/include-missing-new-orders-for-balance-cut-failed',
            [
                'methods'  => 'PUT',
                'callback' => [$handlePastNewOrders, 'include_missing_new_orders_for_balance_cut_issue'], // Ensure this function exists and is callable
                'permission_callback' => api_permission_check(),
            ]
        );
    }

    /**
     * Retrieves the list of WooCommerce orders.
     *
     * @param WP_REST_Request $request The API request.
     * @return WP_REST_Response The response with order data.
     */
    public function get_orders($request)
    {
        $customerHandler = new \WooEasyLife\Frontend\CustomerHandler();
        $status   = $request->get_param('status');
        $per_page = intval($request->get_param('per_page'));
        $page     = intval($request->get_param('page'));
        $billing_phone = normalize_phone_number($request->get_param('billing_phone'));
        $search   = $request->get_param('search'); // Get search parameter
        $total_new_orders_not_handled_by_wel_plugin = $this->get_total_new_orders_not_handled_by_wel_plugin();
        $total_new_order_handled_by_wel_but_balance_cut_failed = $this->get_total_new_order_handled_by_wel_but_balance_cut_failed();
    
        // Use WooCommerce Order Query to fetch orders with pagination and search
        $args = array_merge([
            'status'        => $status,
            'limit'         => $per_page,
            'page'          => $page,
            'billing_phone' => $billing_phone,
            'type'          => 'shop_order',
            'paginate'      => true, // Enable pagination
            'orderby' => 'id',
            'order'   => 'DESC', // Descending order
        ], getMetaDataOfOrderForArgs());
    
        // Add search conditions
        if (!empty($search)) {
            $args['s'] = $search;
        }
    
        $query = wc_get_orders($args);
        if (empty($query->orders)) {
            return rest_ensure_response([
                'message' => 'No orders found.',
                'data'    => [],
                'total'   => 0,
                'pages'   => 0,
            ]);
        }
    
        // Total records and total pages
        $total_orders = $query->total;
        $total_pages  = $query->max_num_pages;
    
        // Prepare the order data
        $data = [];
        global $wpdb;
        foreach ($query->orders as $order) 
        {
            $product_info = getProductInfo($order);
            $customer_ip = $order->get_meta('_customer_ip_address', true);
            $total_order_per_customer_for_current_order_status = get_total_orders_by_billing_phone_or_email_and_status($order);
    
            // Fetch fraud data from the custom table
            $_billing_phone = $order->get_billing_phone();
            $_billing_email = $order->get_billing_email();
            $fraud_data = customer_courier_fraud_data($order);
    
            $ip_block_listed = get_block_data_by_type($customer_ip, 'ip');
            $phone_block_listed = get_block_data_by_type(normalize_phone_number($_billing_phone), 'phone_number');
            $email_block_listed = get_block_data_by_type($_billing_email, 'email');
            $discount_total = $order->get_discount_total(); // Total discount amount
            $discount_tax = $order->get_discount_tax(); // Discount tax, if any
            $applied_coupons = $order->get_coupon_codes(); // Array of coupon codes
            $order_notes = get_order_notes($order);
            $created_via = $order->get_meta('_created_via', true);
            $courier_data = get_courier_data_from_order($order);
            $is_repeat_customer = is_repeat_customer($order);
            $customer_custom_data = $customerHandler->handle_customer_data(null, $order);

            $data[] = [
                'id'            => $order->get_id(),
                'status'        => $order->get_status(),
                'total'         => $order->get_total(),
                'is_wel_order_handled' => $order->get_meta('is_wel_order_handled', true),
                'is_wel_balance_cut'   => $order->get_meta('is_wel_balance_cut', true),
                'customer_custom_data' => $customer_custom_data,
                'total_new_orders_not_handled_by_wel_plugin' => $total_new_orders_not_handled_by_wel_plugin,
                'total_new_order_handled_by_wel_but_balance_cut_failed' => $total_new_order_handled_by_wel_but_balance_cut_failed,
                'total_order_per_customer_for_current_order_status' => $total_order_per_customer_for_current_order_status,
                'date_created'  => $order->get_date_created() ? $order->get_date_created()->date('M j, Y \a\t g:i A') : null,
                'customer_id'   => $order->get_customer_id(),
                'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'shipping_cost' => $order->get_shipping_total(),
                'shipping_methods' => get_order_shipping_methods($order),
                'order_source'     => get_order_source($order),
                'created_via' => $created_via,
                'customer_ip'   => $customer_ip,
                'phone_block_listed' => $phone_block_listed,
                'email_block_listed' => $email_block_listed,
                'ip_block_listed' => $ip_block_listed,
                'discount_total' => $discount_total,
                'discount_tax' => $discount_tax,
                'order_notes' => $order_notes,
                'courier_data' => $courier_data,
                'repeat_customer' => $is_repeat_customer,
                'currency_symbol' => get_woocommerce_currency_symbol($order->get_currency()),
                'applied_coupons' => $applied_coupons,
                'payment_method' => $order->get_payment_method(),
                'payment_method_title' => $order->get_payment_method_title(),
                'transaction_id' => $order->get_transaction_id() ?: '',
                'product_price' => wc_price($product_info['total_price']),
                'product_info' => $product_info,
                'billing_address' => [
                    'type' => 'billing',
                    'order_id'   => $order->get_id(),
                    'first_name' => $order->get_billing_first_name(),
                    'last_name'  => $order->get_billing_last_name(),
                    'company'    => $order->get_billing_company(),
                    'address_1'  => $order->get_billing_address_1(),
                    'address_2'  => $order->get_billing_address_2(),
                    'city'       => $order->get_billing_city(),
                    'state'      => $order->get_billing_state(),
                    'postcode'   => $order->get_billing_postcode(),
                    'country'    => $order->get_billing_country(),
                    'email'      => $order->get_billing_email(),
                    'phone'      => $_billing_phone,
                    'transaction_id' => $order->get_transaction_id() ?: '',
                ],
                'shipping_address' => [
                    'type' => 'shipping',
                    'order_id'   => $order->get_id(),
                    'first_name' => $order->get_shipping_first_name(),
                    'last_name'  => $order->get_shipping_last_name(),
                    'company'    => $order->get_shipping_company(),
                    'address_1'  => $order->get_shipping_address_1(),
                    'address_2'  => $order->get_shipping_address_2(),
                    'city'       => $order->get_shipping_city(),
                    'state'      => $order->get_shipping_state(),
                    'postcode'   => $order->get_shipping_postcode(),
                    'country'    => $order->get_shipping_country(),
                    'customer_note' => $order->get_customer_note()
                ],
                'customer_report' => $fraud_data ? @json_decode($fraud_data['report'], true)['report'] : null
            ];
        }
    
        return new \WP_REST_Response([
            'status' => 'success',
            'data'   => $data,
            'total'  => $total_orders,
            'pages'  => $total_pages,
        ], 200);
    }

    private function get_total_new_orders_not_handled_by_wel_plugin() {
        $order_query = new \WC_Order_Query([
            'status'    => ['wc-processing'],
            'limit'     => -1,
            'type'      => 'shop_order',
            'return'    => 'ids', // Only retrieve order IDs
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key'     => 'is_wel_order_handled',
                        'compare' => 'NOT EXISTS', // Meta key does not exist
                    ]
                ]
        ]);
        
        $order_ids = $order_query->get_orders();
        $order_count = count($order_ids);
        
        return $order_count;        
    }

    private function get_total_new_order_handled_by_wel_but_balance_cut_failed() {
        $order_query = new \WC_Order_Query([
            'status'    => ['wc-processing'],
            'limit'     => -1,
            'type'      => 'shop_order',
            'return'    => 'ids', // Only retrieve order IDs
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'is_wel_order_handled',
                    'value'   => '1', // Checking if it's explicitly set to "true" (1)
                    'compare' => '='
                ],
                'relation' => 'AND', // Either it's explicitly false (0), empty, or does not exist
                [
                    'key'     => 'is_wel_balance_cut',
                    'value'   => "0",
                    'compare' => '='
                ]
            ]

        ]);
        
        $order_ids = $order_query->get_orders();
        $order_count = count($order_ids);
        
        return $order_count;        
    }
    
    public function get_order_status_with_counts()
    {
        $statuses = wc_get_order_statuses(); // Retrieve all order statuses
        foreach ($statuses as $status_key => $status_label) {
            // Query orders by status
            $args = array_merge([
                'status' => str_replace('wc-', '', $status_key), // Remove 'wc-' prefix for the query
                'limit'  => -1,
                'type'   => 'shop_order',
                'return' => 'ids',
                
            ], getMetaDataOfOrderForArgs());

            $orders = wc_get_orders($args);
            $order_count = count($orders);

            if ($order_count > 0) {
                $order_counts[] = [
                    "title" => $status_label,
                    "slug" => str_replace('wc-', '', $status_key),
                    "count" => $order_count
                ]; // Count orders per status
            }
        }

        return new \WP_REST_Response([
            'status' => 'success',
            'data'   => $order_counts
        ], 200);
    }

    public function save_order_notes($request) {
        global $wpdb;
        $params = $request->get_json_params();
    
        // Validate the payload
        if (!isset($params['order_id'])) {
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'Missing order_id in the payload.',
            ], 400);
        }
    
        $order_id = intval($params['order_id']);
        $customer_note = isset($params['customer_note']) ? sanitize_text_field($params['customer_note']) : '';
        $courier_note = isset($params['courier_note']) ? sanitize_text_field($params['courier_note']) : '';
        $invoice_note = isset($params['invoice_note']) ? sanitize_text_field($params['invoice_note']) : '';
    
        // Check if the order exists
        $order = wc_get_order($order_id);
        if (!$order) {
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'Order not found.',
            ], 404);
        }
    
        // Define the table name
        $table_name = $wpdb->prefix . 'wc_orders_meta';
    
        // Insert or update the notes in the `wp_wc_orders_meta` table
        $notes = [
            ['meta_key' => 'customer_note', 'meta_value' => $customer_note],
            ['meta_key' => 'courier_note', 'meta_value' => $courier_note],
            ['meta_key' => 'invoice_note', 'meta_value' => $invoice_note],
        ];
    
        foreach ($notes as $note) {
            if (!empty($note['meta_value'])) {
                // Check if the record already exists
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE order_id = %d AND meta_key = %s",
                    $order_id,
                    $note['meta_key']
                ));
    
                if ($existing) {
                    // Update the existing record
                    $wpdb->update(
                        $table_name,
                        ['meta_value' => $note['meta_value']],
                        ['order_id' => $order_id, 'meta_key' => $note['meta_key']],
                        ['%s'],
                        ['%d', '%s']
                    );
                } else {
                    // Insert a new record
                    $wpdb->insert(
                        $table_name,
                        [
                            'order_id'   => $order_id,
                            'meta_key'   => $note['meta_key'],
                            'meta_value' => $note['meta_value'],
                        ],
                        ['%d', '%s', '%s']
                    );
                }
            }
        }
    
        return new \WP_REST_Response([
            'status' => 'success',
            'message' => 'Order notes saved successfully.',
            'data' => [
                'order_id' => $order_id,
                'customer_note' => $customer_note,
                'courier_note' => $courier_note,
                'invoice_note' => $invoice_note,
            ],
        ], 200);
    } 
    
    public function change_order_status(\WP_REST_Request $request) {
        // Get the payload from the request
        $payload = $request->get_json_params();
    
        // Validate the payload
        if (empty($payload) || !is_array($payload)) {
            return new \WP_REST_Response([
                'status'  => 'error',
                'message' => 'Invalid or empty payload.',
            ], 400);
        }
    
        $responses = []; // To store the responses for each order
    
        foreach ($payload as $entry) {
            // Validate each entry in the payload
            if (empty($entry['order_id']) || empty($entry['new_status'])) {
                $responses[] = [
                    'status'  => 'error',
                    'message' => 'Missing order_id or new_status in entry.',
                    'entry'   => $entry,
                ];
                continue;
            }
    
            $order_id = intval($entry['order_id']);
            $new_status = sanitize_text_field($entry['new_status']);
    
            // Get the order by ID
            $order = wc_get_order($order_id);
    
            if (!$order) {
                $responses[] = [
                    'status'  => 'error',
                    'message' => 'Order not found.',
                    'order_id' => $order_id,
                ];
                continue;
            }
    
            // Update the order status
            try {
                $order->update_status($new_status, 'Status updated via API', true);
                if($new_status == 'wc-completed'){
                    $customerHandler = new \WooEasyLife\Frontend\CustomerHandler();
                    $customerHandler->handle_customer_data($order_id, $order);
                }

                $responses[] = [
                    'status'  => 'success',
                    'message' => 'Order status updated successfully.',
                    'order_id' => $order_id,
                    'new_status' => $new_status,
                ];
            } catch (\Exception $e) {
                $responses[] = [
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                    'order_id' => $order_id,
                ];
            }
        }
    
        return new \WP_REST_Response([
            'status' => 'success',
            'data'   => $responses,
        ], 200);
    }

    public function check_fraud_customer(\WP_REST_Request $request) {
        // Get the payload from the request
        $payload = $request->get_json_params();

        // Validate the payload
        if (empty($payload) || !is_array($payload)) {
            return new \WP_REST_Response([
                'status'  => 'error',
                'message' => 'Invalid or empty payload.',
            ], 400);
        }
    
        return new \WP_REST_Response([
            'status'  => 'success',
            'message' => 'Success',
            'data' => getCustomerFraudData($payload)
        ], 200);
    }

    public function update_courier_data(\WP_REST_Request $request) {
        // Get the payload from the request
        $data = $request->get_json_params();
        $order_id = $data['order_id'];
        $courier_data = $data['courier_data'];
        
        if (!empty($courier_data)) {
            $response = update_courier_data_for_order($order_id, $courier_data);

            if($response){
                return new \WP_REST_Response([
                    'status'  => 'success',
                    'message' => 'Success',
                    'data' => [
                        "order_id" => $order_id,
                        "courier_data" => $courier_data
                    ]
                ], 200);
            }
        }
    } 
}


function getProductInfo($order)
{
    $productInfo = [
        'total_price' => 0,
        'product_info' => []
    ];
    if ($order) {
        foreach ($order->get_items() as $item_id => $item) {
            // Get product details
            $product = $item->get_product(); // Get the product object
            $product_image_url = wp_get_attachment_url($product->get_image_id()); // Get the featured image URL

            if ($product) {
                $product_total = $item->get_total(); // Total for the line item (quantity * price)
                $productInfo["total_price"] = (int)$productInfo["total_price"] += (int)$product_total;
                $productInfo["product_info"][] = [
                    'product_name' => $product->get_name(),
                    'product_price' => $product->get_price(),
                    'product_total' => $product_total,
                    'product_quantity' => $item->get_quantity(),
                    'product_image' => $product_image_url
                ];
            }
        }
    }
    return $productInfo;
}

function get_order_notes($order) {
    global $wpdb;

    // Ensure $order is a valid WC_Order object
    if (!$order instanceof \WC_Order) {
        return [
            'status' => 'error',
            'message' => 'Invalid order object.',
        ];
    }

    $order_id = $order->get_id();

    // Define table name
    $table_name = $wpdb->prefix . 'wc_orders_meta';

    // Fetch notes from the custom table
    $notes = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT meta_key, meta_value FROM $table_name WHERE order_id = %d AND meta_key IN ('customer_note', 'courier_note', 'invoice_note')",
            $order_id
        ),
        OBJECT_K
    );

    // Extract notes into an array
    $customer_note = isset($notes['customer_note']) ? $notes['customer_note']->meta_value :  esc_html($order->get_customer_note());
    $courier_note = isset($notes['courier_note']) ? $notes['courier_note']->meta_value : '';
    $invoice_note = isset($notes['invoice_note']) ? $notes['invoice_note']->meta_value : '';

    return [
        'customer_note' => $customer_note,
        'courier_note' => $courier_note,
        'invoice_note' => $invoice_note,
    ];
}

function get_order_shipping_methods($order) {
    // Validate the order object
    if (!$order instanceof \WC_Order) {
        return [
            'status' => 'error',
            'message' => 'Invalid order object or ID.',
        ];
    }

    // Initialize the result array
    $shipping_methods_data = [];

    // Get the shipping methods for the order
    $shipping_methods = $order->get_shipping_methods();

    foreach ($shipping_methods as $shipping_method) {
        $shipping_methods_data[] = $shipping_method->get_method_title();
    }

    return $shipping_methods_data;
}

/**
 * Check if a customer is a repeat customer by customer ID.
 *
 * @param WC_Order $order The WooCommerce order object.
 * @return bool True if the customer is a repeat customer, false otherwise.
 */
/**
 * Check if a customer is a repeat customer based on their billing phone.
 *
 * @param int $order_id The ID of the WooCommerce order.
 * @return bool True if the customer is a repeat customer, false otherwise.
 */
function is_repeat_customer($order) {
    $phone = $order->get_billing_phone();
    $email = $order->get_billing_email();
    $orders = [];
    $my_orders = [
        'total_completed_order' => 0,
        'total_processing_order' => 0
    ];

    if(!empty($phone)) {
        $orders = get_orders_by_billing_phone_or_email_and_status($phone, null, ['processing', 'completed']);
    } else if(!empty($email)) {
        $orders = get_orders_by_billing_phone_or_email_and_status(null, $email, ['processing', 'completed']);
    }


    $isRepeatCustomer = false;

    foreach ($orders as $order) {
        $status = $order->get_status();

        // Count orders by status
        if (isset($my_orders["total_{$status}_order"])) {
            $my_orders["total_{$status}_order"]++;
        }
    }

    // Determine if the customer is a repeat customer
    if ($my_orders['total_completed_order'] >= 2 || 
        ($my_orders['total_processing_order'] >= 1 && $my_orders['total_completed_order'] == 1)) {
        $isRepeatCustomer = true;
    }

    return $isRepeatCustomer;
}
