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
            '/update-order-shipping-method',
            [
                'methods'             => 'POST', // Use POST for modifying data
                'callback'            => [$this, 'update_order_shipping_method'],
                'permission_callback' => api_permission_check(),
                'args'                => [
                    'order_id' => [
                        'required'    => true,
                        'type'        => 'integer',
                        'description' => 'The ID of the order to update shipping method.',
                    ],
                    'shipping_instance_id' => [
                        'required'    => true,
                        'type'        => 'integer',
                        'description' => 'New shipping instance ID to apply.',
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
            '/mark-as-done-undone', 
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'mark_as_done_undone'],
                'permission_callback' => api_permission_check(), // Or add your permission logic here
            ]
        );
        register_rest_route(
            __API_NAMESPACE, 
            '/toggle-as-follow-unfollow', 
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'toggle_as_follow_unfollow'],
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
            '/update-or-add-product-to-order',
            [
                'methods'  => 'POST',
                'callback' => [$this, 'update_or_add_product_to_order'], // Ensure this function exists and is callable
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

        register_rest_route(
            __API_NAMESPACE, 
            '/orders/update-total',
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'update_order_total'], // <-- New method call
                'permission_callback' => api_permission_check(),
                'args' => [
                    'order_id' => ['required' => true, 'type' => 'integer'],
                    // Total should be treated as a number (float/double)
                    'new_total' => ['required' => true, 'type' => 'number'], 
                ]
            ]
        );

        register_rest_route(
            __API_NAMESPACE, 
            '/orders/change-status-bulk',
            [
                'methods'  => 'POST',
                'callback' => [$this, 'change_order_status_bulk'],
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
    
        $is_done_filter = $request->get_param('is_done');
        $need_follow_filter = $request->get_param('need_follow');

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

        // --- Start: Meta Query Construction ---
        $meta_query = $args['meta_query'] ?? [];
        
        // --- 1. 'Mark as Done' filter (woo_easy_is_done) ---
        if ($is_done_filter == '1') {
            // Filter for: Marked as Done (value = 1)
            $meta_query[] = [
                'key'     => 'woo_easy_is_done',
                'value'   => '1',
                'compare' => '=',
                'type'    => 'NUMERIC',
            ];
        } elseif ($is_done_filter == '0') {
            // Filter for: Marked as Undone (NOT EXISTS OR value = 0)
            $meta_query[] = [
                'relation' => 'OR',
                [
                    'key'     => 'woo_easy_is_done',
                    'compare' => 'NOT EXISTS', 
                ],
                [
                    'key'     => 'woo_easy_is_done',
                    'value'   => '0',
                    'compare' => '=',
                    'type'    => 'NUMERIC',
                ]
            ];
        }
        
        // --- 2. 'Need Follow' filter (woo_easy_need_follow) ---
        if ($need_follow_filter == '1') {
            // Filter for: Needs Follow Up (value = 1)
            $meta_query[] = [
                'key'     => 'woo_easy_need_follow',
                'value'   => '1',
                'compare' => '=',
                'type'    => 'NUMERIC',
            ];
        } elseif ($need_follow_filter == '0') {
            // Filter for: Does NOT need Follow Up (NOT EXISTS OR value = 0)
            $meta_query[] = [
                'relation' => 'OR',
                [
                    'key'     => 'woo_easy_need_follow',
                    'compare' => 'NOT EXISTS', // Items that were never flagged
                ],
                [
                    'key'     => 'woo_easy_need_follow',
                    'value'   => '0',
                    'compare' => '=',
                    'type'    => 'NUMERIC',
                ]
            ];
        }
        
        // --- Final Meta Query Application ---
        if (!empty($meta_query)) {
            // If there were multiple filters, ensure they are combined with 'AND'
            if (count($meta_query) > 1 && !isset($meta_query['relation'])) {
                // If multiple top-level meta queries, make the relation 'AND'
                $args['meta_query'] = ['relation' => 'AND', ...$meta_query];
            } else {
                $args['meta_query'] = $meta_query;
            }
        }
        // --- End: Meta Query Construction ---
    
        // Add search conditions
        if (!empty($search)) {
            // First, attempt to search using WooCommerce's default order search functionality
            $args['s'] = $search;
            $query = wc_get_orders($args);
        
            // If no matching orders are found, attempt a meta query search in '_courier_data'
            if (empty($query->orders)) {
                $args = array_merge($args, [
                    's' => '', // Reset the default search to prevent conflicts
                    'meta_query' => [
                        [
                            'key'     => '_courier_data', // Search within the '_courier_data' meta field
                            'value'   => $search, // The user-provided search term
                            'compare' => 'LIKE' // Match any part of the field value
                        ]
                    ]
                ]);
        
                // Run the query again with the updated parameters
                $query = wc_get_orders($args);
            }
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
            $customer_device_token = $order->get_meta('_wel_device_token', true);
            $total_order_per_customer_for_current_order_status = get_total_orders_by_billing_phone_or_email_and_status($order);
    
            // Fetch fraud data from the custom table
            $_billing_phone = $order->get_billing_phone();
            $_billing_email = $order->get_billing_email();
            $fraud_data = customer_courier_fraud_data($order);
    
            $ip_block_listed = get_block_data_by_type($customer_ip, 'ip');
            $device_block_listed = get_block_data_by_type($customer_device_token, 'device_token');
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
            $parcel_weight = get_order_total_weight($order);
            $referrer_url = get_post_meta($order_id, '_referrer_url', true);
            //get order note of cod modification
            $cod_modification_note = get_order_cod_modification_note($order);
            $customFieldData = get_only_cartflows_custom_fields_data($order->get_id());
            
            $data[] = [
                'id'            => $order->get_id(),
                'status'        => $order->get_status(),
                'sub_total'     => $order->get_subtotal(),
                'total'         => $order->get_total(), //after cutting discount
                'referrer_url'    => $referrer_url,
                // 👉 get_total() কী কী অন্তর্ভুক্ত করে?
                // ✅ পণ্যের মূল্য (Product Price)
                // ✅ শিপিং চার্জ (Shipping Cost)
                // ✅ ট্যাক্স (Tax) - যদি থাকে
                // ✅ ডিসকাউন্ট বাদ দিয়ে (After Discount) চূড়ান্ত মূল্য
                'parcel_weight'        => $parcel_weight,
                'site_logo' => get_site_logo_url(),
                'is_wel_order_handled' => $order->get_meta('is_wel_order_handled', true),
                'is_wel_balance_cut'   => $order->get_meta('is_wel_balance_cut', true),
                'is_done'     => $order->get_meta('woo_easy_is_done', true),
                'need_follow' => $order->get_meta('woo_easy_need_follow', true),
                'customer_custom_data' => $customer_custom_data,
                'total_new_orders_not_handled_by_wel_plugin' => $total_new_orders_not_handled_by_wel_plugin,
                'total_new_order_handled_by_wel_but_balance_cut_failed' => $total_new_order_handled_by_wel_but_balance_cut_failed,
                'total_order_per_customer_for_current_order_status' => $total_order_per_customer_for_current_order_status,
                'date_created'  => $order->get_date_created() ? human_time_difference($order->get_date_created()) : null,
                'customer_id'   => $order->get_customer_id(),
                'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'shipping_cost' => $order->get_shipping_total(),
                'shipping_methods' => get_order_shipping_methods($order),
                'order_source'     => get_order_source($order),
                'created_via' => $created_via,
                'customer_ip'   => $customer_ip,
                'customFieldData' => $customFieldData,
                'customer_device_token'   => $customer_device_token,
                'phone_block_listed' => $phone_block_listed,
                'email_block_listed' => $email_block_listed,
                'ip_block_listed' => $ip_block_listed,
                'device_block_listed' => $device_block_listed,
                'discount_total' => $discount_total,
                'discount_tax' => $discount_tax,
                'order_notes' => $order_notes,
                'cod_modification_note' => $cod_modification_note,
                'courier_data' => $courier_data,
                'repeat_customer' => $is_repeat_customer,
                'currency_symbol' => get_woocommerce_currency_symbol($order->get_currency()),
                'applied_coupons' => $applied_coupons,
                'payment_method' => $order->get_payment_method(),
                'payment_method_title' => $order->get_payment_method_title(),
                'transaction_id' => $order->get_transaction_id() ?: '',
                'product_price' => $product_info['total_price'],
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
    
    /**
     * Updates an order's shipping method by looking across all shipping zones.
     * This handles cases where the intended method instance ID might be in a different zone 
     * or is a Default Zone method which often lacks a proper instance_id.
     * 
     * @param \WP_REST_Request $request
     * @return bool|\WP_Error
     */
    public function update_order_shipping_method(\WP_REST_Request $request) {
        // Get the payload from the request
        $data = $request->get_json_params();
        $order_id = $data['order_id'];
        $new_shipping_instance_id = $data['shipping_instance_id'];
        
        // Load the WooCommerce order object
        $order = wc_get_order($order_id);
        if (!$order) {
            return new \WP_Error('invalid_order', 'Invalid order ID.');
        }

        // --- 1. Find the target shipping method across all zones/default zone ---
        $chosen_method = null;
        $shipping_methods = [];

        // Get methods from all configured zones
        $zones = \WC_Shipping_Zones::get_zones();
        foreach ($zones as $zone) {
            $zone_obj = new \WC_Shipping_Zone($zone['id']);
            // The 'get_shipping_methods()' retrieves all methods configured for this zone.
            $shipping_methods = array_merge($shipping_methods, $zone_obj->get_shipping_methods(false));
        }

        // Get methods from the "Rest of the World" (Default) zone
        $default_zone = new \WC_Shipping_Zone(0);
        $shipping_methods = array_merge($shipping_methods, $default_zone->get_shipping_methods(false));
        
        // Find the method that matches the passed instance_id (or method_id if instance_id is missing/0)
        foreach ($shipping_methods as $method) {
            // Check for a direct match using instance_id
            if ($method->instance_id == $new_shipping_instance_id) {
                $chosen_method = $method;
                break;
            }
            
            // Fallback for methods without instance_id (like default free_shipping or local_pickup in Zone 0)
            // We assume if the passed ID is actually the method ID AND the instance_id is 0/missing, it's the target.
            // This relies on the client passing the method_id when the instance_id is not available.
            if (
                ($method->instance_id === 0 || empty($method->instance_id)) && 
                ($method->id === (string)$new_shipping_instance_id)
            ) {
                 // Use a temporary identifier to ensure this case is handled
                 $chosen_method = $method;
                 break;
            }
        }
        
        if (!$chosen_method) {
            return new \WP_Error('shipping_not_found', 'Selected shipping method instance is not available. Check method settings or zone configuration.');
        }

        // --- 2. Remove existing shipping items ---
        foreach ($order->get_items('shipping') as $item_id => $shipping_item) {
            $order->remove_item($item_id);
        }
    
        // --- 3. Add new shipping method ---
        $shipping_item = new \WC_Order_Item_Shipping();
        
        // Get the cost: First try to get it from the instance settings, otherwise fallback to method cost property.
        $method_cost = $chosen_method->instance_settings['cost'] ?? $chosen_method->cost ?? 0;

        $shipping_item->set_method_title($chosen_method->get_title());
        $shipping_item->set_method_id($chosen_method->id);
        $shipping_item->set_instance_id($chosen_method->instance_id);
        $shipping_item->set_total(floatval($method_cost));
        $order->add_item($shipping_item);
        
        // --- 4. Recalculate totals and save ---
        $order->calculate_totals();
        $order->save();
    
        return new \WP_REST_Response(['message' => 'Order shipping method updated successfully'], 200);
    }


    public function update_or_add_product_to_order(\WP_REST_Request $request) {
        // Get the payload from the request
        $data = $request->get_json_params();
        $order_id = $data['order_id'];
        $product_id = $data['product_id'];
        $quantity = $data['quantity'];
    
        // Load WooCommerce order object
        $order = wc_get_order($order_id);
        if (!$order) {
            return new \WP_Error('invalid_order', 'Invalid order ID.');
        }
    
        // Store existing applied coupons
        $applied_coupons = $order->get_coupon_codes();
    
        // Check if product exists and update/remove accordingly
        $product_exists = $this->update_existing_product($order, $product_id, $quantity);
        
        // If product not found, add new product
        if (!$product_exists && $quantity > 0) {
            $this->add_new_product_to_order($order, $product_id, $quantity);
        }
    
        // Apply coupons after updating the order
        $this->reapply_coupons($order, $applied_coupons);
        
        // Fetch updated product list
        $updated_items = getProductInfo($order);
        
        return $updated_items;
    }
    
    /**
     * Updates existing product in order or removes it if quantity is 0.
     */
    private function update_existing_product($order, $product_id, $quantity) {
        foreach ($order->get_items() as $item_id => $item) {
            if ($item->get_product_id() == $product_id) {
                if ($quantity == 0) {
                    $order->remove_item($item_id);
                } else {
                    $product = wc_get_product($product_id);
                    if ($product) {
                        $regular_price = $product->get_regular_price();
                        $sale_price = $product->get_sale_price() ?: $regular_price;
                        $subtotal = $regular_price * $quantity;
                        $discounted_total = $sale_price * $quantity;
                        
                        // Update item
                        $item->set_quantity($quantity);
                        $item->set_subtotal($subtotal);
                        $item->set_total($discounted_total);
                        $item->save();
                    }
                }
                return true; // Product found and updated
            }
        }
        return false; // Product not found in order
    }
    
    /**
     * Adds a new product to the order if it does not exist.
     */
    private function add_new_product_to_order($order, $product_id, $quantity) {
        $product = wc_get_product($product_id);
        if (!$product) {
            return new \WP_Error('invalid_product', 'Invalid product ID.');
        }
    
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price() ?: $regular_price;
        $subtotal = $regular_price * $quantity;
        $discounted_total = $sale_price * $quantity;
        
        // Create new order item
        $item = new \WC_Order_Item_Product();
        $item->set_product_id($product_id);
        $item->set_quantity($quantity);
        $item->set_subtotal($subtotal);
        $item->set_total($discounted_total);
        $item->save();
    
        // Add item to order
        $order->add_item($item);
    }
    
    private function reapply_coupons($order, $applied_coupons) {
        // Remove all existing coupons
        foreach ($order->get_items('coupon') as $coupon_item_id => $coupon_item) {
            $order->remove_item($coupon_item_id);
        }
    
        // Recalculate totals before applying coupon
        $order->calculate_totals();
    
        // Apply coupons BEFORE adding shipping
        foreach ($applied_coupons as $coupon_code) {
            $order->apply_coupon($coupon_code);
        }
    
        // Recalculate totals again after applying the coupon
        $order->calculate_totals();
        $order->save();
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
            $order_count = count($orders ?? []);

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

    public function mark_as_done_undone($request) {
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
        $is_done = isset($params['is_done']) ? intval($params['is_done']) : 0;
    
        // Retrieve the order
        $order = wc_get_order($order_id);
        if (!$order) {
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'Order not found.',
            ], 404);
        }
    
        // Update the order meta
        $order->update_meta_data('woo_easy_is_done', $is_done);
        $order->save_meta_data(); // Ensure the changes are saved
    
        return new \WP_REST_Response([
            'status' => 'success',
            'message' => 'Order updated successfully.',
            'order_id' => $order_id,
            'is_done' => $is_done,
        ], 200);
    }

    public function toggle_as_follow_unfollow($request) {
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
        $need_follow = isset($params['need_follow']) ? intval($params['need_follow']) : 0;
    
        // Retrieve the order
        $order = wc_get_order($order_id);
        if (!$order) {
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'Order not found.',
            ], 404);
        }
    
        // Update the order meta
        $order->update_meta_data('woo_easy_need_follow', $need_follow);
        $order->save_meta_data(); // Ensure the changes are saved
    
        return new \WP_REST_Response([
            'status' => 'success',
            'message' => 'Order updated successfully.',
            'order_id' => $order_id,
            'need_follow' => $need_follow,
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
            $new_status = $entry['new_status'];
            
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

    public function change_order_status_bulk(\WP_REST_Request $request) {
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

    /**
     * Updates the order total amount manually. Used primarily for COD adjustments.
     * This skips WooCommerce's automatic calculation, allowing manual total set.
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function update_order_total(\WP_REST_Request $request) {
        $order_id = $request->get_param('order_id');
        $new_total = floatval($request->get_param('new_total'));
        
        // Validate order ID
        if (empty($order_id)) {
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'Order ID is required.',
            ], 400);
        }
        
        // Validate new total
        if ($new_total <= 0) {
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'New total must be greater than 0.',
            ], 400);
        }
        
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'Order not found.',
            ], 404);
        }
        
        // Get the current total before modification
        $calculated_total = $order->get_total();
        
        // Check if the total is actually changing
        if ($calculated_total == $new_total) {
            return new \WP_REST_Response([
                'status' => 'success',
                'message' => 'No changes needed. Order total is already set to ' . wc_price($new_total, ['currency' => $order->get_currency()]),
                'data' => [
                    'order_id' => $order_id,
                    'total' => $new_total,
                    'modified' => false
                ]
            ], 200);
        }
        
        try {
            // 1. Update order total
            $order->set_total($new_total);
            
            // 2. Add order note to track manual modification
            $note_message = sprintf(
                'Order Total (COD) manually updated from %s to %s via WEL plugin.',
                wc_price($calculated_total, ['currency' => $order->get_currency()]),
                wc_price($new_total, ['currency' => $order->get_currency()])
            );
            
            $order->add_order_note(
                $note_message,
                0,    // Not a customer note
                true  // Is a system note
            );
            
            // 3. Add meta to track manual modification
            $order->update_meta_data('_cod_amount_modified', true);
            $order->update_meta_data('_original_total', $calculated_total);
            $order->update_meta_data('_modified_total', $new_total);
            $order->update_meta_data('_cod_modification_date', current_time('mysql'));
            $order->update_meta_data('_cod_modified_by', get_current_user_id());
            
            // 4. Save the order
            $order->save();
            
            // Log the action
            error_log(sprintf(
                'Order #%d total updated from %s to %s by user #%d',
                $order_id,
                $calculated_total,
                $new_total,
                get_current_user_id()
            ));
            
            return new \WP_REST_Response([
                'status' => 'success',
                'message' => sprintf(
                    'Order Total (COD) updated from %s to %s',
                    wc_price($calculated_total, ['currency' => $order->get_currency()]),
                    wc_price($new_total, ['currency' => $order->get_currency()])
                ),
                'data' => [
                    'order_id' => $order_id,
                    'original_total' => $calculated_total,
                    'new_total' => $new_total,
                    'modified' => true,
                    'modified_by' => get_current_user_id(),
                    'modified_at' => current_time('mysql')
                ]
            ], 200);
            
        } catch (\Exception $e) {
            error_log('Order total update error: ' . $e->getMessage());
            
            return new \WP_REST_Response([
                'status' => 'error',
                'message' => 'Failed to update order total: ' . $e->getMessage(),
            ], 500);
        }
    }
}


function getProductInfo($order)
{
    $productInfo = [
        'total_price' => 0,
        'total_price_after_cut_discount' => 0,
        'product_info' => []
    ];
    if ($order) {
        foreach ($order->get_items() as $item_id => $item) {
            // Get product details
            $product = $item->get_product(); // Get the product object

            // Safely handle missing or invalid products
            if (!$product || !is_object($product) || !$product->get_id()) {
                // Optionally, you can log or collect info about missing products here
                continue;
            }

            $product_image_url = wp_get_attachment_url($product->get_image_id()); // Get the featured image URL
            $product_total = $item->get_total(); // Total for the line item (quantity * price)
            $productInfo["total_price"] += (int)($product->get_price() * $item->get_quantity());
            $productInfo["total_price_after_cut_discount"] += (int)$product_total;
            $productInfo["product_info"][] = [
                'id' => $product->get_id(),
                'product_name' => $product->get_name(),
                'product_price' => $product->get_price(),
                'product_total' => ($product->get_price() * $item->get_quantity()),
                'product_quantity' => $item->get_quantity(),
                'product_image' => $product_image_url
            ];
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

/**
 * Get the COD modification note(s) for an order.
 *
 * @param \WC_Order $order
 * @return string|null  The latest COD modification note, or null if not found.
 */
function get_order_cod_modification_note($order) {
    if (!$order instanceof \WC_Order) {
        return null;
    }

    // Get all order notes (private notes, not customer notes)
    $args = [
        'order_id' => $order->get_id(),
        'type'     => 'internal', // Only internal/system notes
        'orderby'  => 'date_created',
        'order'    => 'DESC',
    ];
    $notes = wc_get_order_notes($args);

    // Search for COD modification notes
    foreach ($notes as $note) {
        if (
            strpos($note->content, 'Order Total (COD) manually updated from') !== false &&
            strpos($note->content, 'via WEL plugin.') !== false
        ) {
            return $note->content;
        }
    }

    // If not found, return null
    return null;
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

    return $shipping_methods_data; // Return all shipping methods, not just the first one
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
