<?php
namespace WooEasyLife\Frontend;

class TrackAbandonCart {
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_my_ajax_script'] );    
        
        // AJAX handler for logged-in users
        add_action( 'wp_ajax_update_abandoned_data', [$this, 'update_wc_session_for_abandoned_order'] );

        // AJAX handler for non-logged-in users
        add_action( 'wp_ajax_nopriv_update_abandoned_data', [$this, 'update_wc_session_for_abandoned_order'] );

        add_action( 'wp_ajax_wc_ajax_update_order_review', 'update_wc_session_for_abandoned_order', 10, 0 );
        add_action( 'wp_ajax_nopriv_wc_ajax_update_order_review', 'update_wc_session_for_abandoned_order', 10, 0 );
        

        // work when change checkout page data
        add_action('woocommerce_cart_updated', [$this, 'store_abandoned_cart_data']);
        
        //fire when reload the checkout page
        add_action('woocommerce_checkout_update_order_review', [$this, 'store_abandoned_cart_data']);

        // add_action('woocommerce_thankyou', [$this, 'deleteAbandonedOrderIfOrderProcessedSuccessfully'], 10, 1);
        add_action('woocommerce_order_status_changed', [$this, 'deleteAbandonedOrderIfOrderProcessedSuccessfully'], 10, 3);

        // abandoned marked from abandonedOrderAPI.php
    }

    public function enqueue_my_ajax_script() {
        // --- 1. Enqueue AbandonedOrder.js (Existing) ---
        wp_enqueue_script( 
            'woo-easy-life-abandon-script', // Handlename changed slightly for clarity
            plugins_url('includes/checkoutPage/AbandonedOrder.js', __DIR__), 
            [], // Added dependency on jQuery if your JS uses it
            null, 
            true 
        );
        
        // --- 2. Enqueue wel_app.js (NEW) ---
        wp_enqueue_script( 
            'woo-easy-life-app-script', // New unique handle name
            plugins_url('includes/checkoutPage/wel_app.js', __DIR__), 
            [], // No dependencies
            null, 
            true 
        );
    
        // Localize script to pass the AJAX URL
        wp_localize_script( 'woo-easy-life-abandon-script', 'woo_easy_life_ajax_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'wc_session_id' => ( class_exists( 'WooCommerce' ) && WC()->session ) ? WC()->session->get_customer_id() : ''
        ));    

        $this->enqueue_checkout_scripts();
    }

    public function enqueue_checkout_scripts() {
        if (is_checkout()) {
            // Enqueue FingerprintJS library (from a CDN or local package)
            wp_enqueue_script('fingerprintjs', 'https://cdn.jsdelivr.net/npm/@fingerprintjs/fingerprintjs@4/dist/fp.min.js', [], '4.0.0', true);

            // Enqueue your custom device tracking script
            wp_enqueue_script('wel-device-tracker', plugins_url('js/device-tracker.js', __FILE__), ['jquery', 'fingerprintjs'], '1.0', true);
        }
    }


    public function update_wc_session_for_abandoned_order() {
        if ( !class_exists( 'WC_Session_Handler' ) ) {
            wp_send_json_error( 'WooCommerce session handler not available.' );
            return;
        }
        
        // Retrieve data sent via AJAX
        $billing_first_name = isset( $_POST['billing_first_name'] ) ? sanitize_text_field( $_POST['billing_first_name'] ) : '';
        $billing_last_name = isset( $_POST['billing_last_name'] ) ? sanitize_text_field( $_POST['billing_last_name'] ) : '';
        $billing_phone = isset( $_POST['billing_phone'] ) ? sanitize_text_field( $_POST['billing_phone'] ) : '';
        $billing_email = isset( $_POST['billing_email'] ) ? sanitize_text_field( $_POST['billing_email'] ) : '';
        
        // Update the WooCommerce session
        WC()->session->set( 'billing_first_name', $billing_first_name );
        WC()->session->set( 'billing_last_name', $billing_last_name );
        WC()->session->set( 'billing_phone', $billing_phone );
        WC()->session->set( 'billing_email', $billing_email );
    
        $this->store_abandoned_cart_data();
        // Respond with session ID and any custom data received
        // wp_send_json_success( array( 
        //     'session_id' => 'response 1 goes here',
        //     'login_id' => 'response 2 goes here'
        // ));
    }
    

    public function get_shipping_method_title($shipping_method) {
        // Get all shipping zones
        $shipping_zones = \WC_Shipping_Zones::get_zones();
    
        foreach ($shipping_zones as $zone) {
            foreach ($zone['shipping_methods'] as $method) {
                if ($method->id === $shipping_method) {
                    return $method->get_title();
                }
            }
        }
    
        // Check default shipping methods (like "local_pickup" or "free_shipping")
        $default_methods = WC()->shipping()->get_shipping_methods();
    
        if (isset($default_methods[$shipping_method])) {
            return $default_methods[$shipping_method]->get_method_title();
        }
    
        return 'Unknown Shipping Method';
    }
    
    function get_selected_shipping_data() {
        // Get selected shipping method from session
        $chosen_shipping = WC()->session->get('chosen_shipping_methods')[0] ?? '';
    
        if (!$chosen_shipping) {
            return (object) [
                'title'  => '',
                'method' => '',
                'id'     => '',
            ];
        }
    
        // Extract method and ID
        list($shipping_method, $shipping_id) = explode(':', $chosen_shipping);
    
        // Return shipping data as an object
        return (object) [
            'title'  => $this->get_shipping_method_title($shipping_method),
            'method' => $shipping_method,
            'id'     => $shipping_id,
        ];
    }
    
    public function store_abandoned_cart_data() {
        global $wpdb;
    
        // **Step 1: Get customer details from WooCommerce session**
        $customer_phone = normalize_phone_number(WC()->session->get('billing_phone'));
        $billing_email = WC()->session->get('billing_email');
        $customer_email = !empty($billing_email) ? strtolower($billing_email) : ''; // Normalize email case
    
        // Validate phone or fallback to email
        if (!empty($customer_phone) && validate_BD_phoneNumber($customer_phone)) {
            $identifier = $customer_phone; // Use valid phone
        } elseif (!empty($customer_email) && filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
            $identifier = $customer_email; // Use valid email
        } else {
            return; // Exit if both phone & email are invalid
        }
    
        // **Step 2: Retrieve cart products from WooCommerce**
        $cart = WC()->cart->get_cart();
        if (empty($cart)) {
            return; // Exit if the cart is empty
        }
    
        // Get product IDs from the cart
        $cart_product_ids = array_map(function ($cart_item) {
            return $cart_item['product_id'];
        }, $cart);
    
        // **Step 3: Check for an existing order with `wc-processing` status**
        $args = [
            'status' => ['wc-processing', 'wc-confirmed', 'wc-on-hold', 'wc-pending'],
            'type'   => 'shop_order',
            'limit'  => 1,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ];

        if(!empty($customer_phone)) {
            $args['billing_phone'] = $identifier;
        }else if(!empty($customer_email)) {
            $args['billing_email'] = $identifier;
        }

        $existing_orders = wc_get_orders($args);
        $existing_order = !empty($existing_orders) ? $existing_orders[0] : null;
    
        
        // **Step 4: If no order exists, store the abandoned cart**
        if (!$existing_order) {
            $this->store_abandoned_cart();
            return;
        }
    
        // **Step 5: If order exists, compare cart and ordered products**
        $ordered_product_ids = [];
        foreach ($existing_order->get_items() as $item) {
            $ordered_product_ids[] = $item->get_product_id();
        }
    
        // **Step 6: If cart products & ordered products don't fully match, store the abandoned cart**
        if (!empty(array_diff($cart_product_ids, $ordered_product_ids))) {
            $this->store_abandoned_cart();
        }
    }
    
    /**
     * Function to store abandoned cart using WooCommerce functions
     */
    private function store_abandoned_cart() {
        global $wpdb;

        // Define the abandoned cart table
        $table_name = $wpdb->prefix . __PREFIX . 'abandon_cart';
    
        // Get WooCommerce session details
        $session = WC()->session;
        $customer_name = WC()->session->get('billing_first_name') . ' ' . WC()->session->get('billing_last_name');
        $customer_email = strtolower(WC()->session->get('billing_email'));
        $customer_phone = normalize_phone_number(WC()->session->get('billing_phone'));
        $session_id = $session->get_customer_id();
    
        $billing_address = WC()->customer->get_billing_address();
        $shipping_address = WC()->customer->get_shipping_address();
        $shipping_data = $this->get_selected_shipping_data();
        $payment_method_title= $this->get_payment_method_title_from_session();


        // clean the record if session_id match
        $this->delete_cart_by_session_id($session_id, $table_name);

        // Prepare cart details
        $cart_contents = [
            'products' => [],
            'coupon_codes' => WC()->session->get('applied_coupons', []),
            'customer_note' => WC()->session->get('customer_note', ''),
            'payment_method_id' => WC()->session->get('chosen_payment_method', ''),
            'payment_method' => $payment_method_title,
            'shipping_method_id' => $shipping_data->id,
            'shipping_method' => $shipping_data->method,
            'shipping_method_title' => $shipping_data->title,
            'subtotal' => WC()->cart->get_subtotal(),
            'total'     => WC()->cart->get_total(),
            'total_discount' => WC()->cart->get_discount_total(),
            'shipping_cost' => WC()->cart->get_shipping_total(),
        ];
    
        $total_value = 0;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if (!$product || !is_object($product) || !$product->get_id()) {
                // Skip this cart item if product is not found or invalid
                continue;
            }
            $cart_contents['products'][] = [
                'product_id'  => $product->get_id(),
                'name'        => $product->get_name(),
                'image'       => wp_get_attachment_url($product->get_image_id()),
                'product_url' => get_permalink($product->get_id()),
                'quantity'    => $cart_item['quantity'],
                'price'       => $product->get_price(),
                'total_price' => $cart_item['line_total'],
            ];
            $total_value += $cart_item['line_total'];
        }
    
        $serialized_cart_contents = maybe_serialize($cart_contents);
    
        // // Check if abandoned cart already exists
        $existing_cart = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name 
             WHERE session_id = %s 
             AND status = 'abandoned'",
            $session_id
        ));
    
        if ($existing_cart) {
            // Update existing abandoned cart record
            $wpdb->update(
                $table_name,
                [
                    'customer_email' => $customer_email,
                    'customer_name' => $customer_name,
                    'customer_phone' => $customer_phone,
                    'cart_contents' => $serialized_cart_contents,
                    'total_value' => $total_value,
                    'billing_address' => $billing_address,
                    'shipping_address' => $shipping_address,
                    'updated_at' => current_time('mysql'),
                ],
                ['id' => $existing_cart],
                ['%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s'],
                ['%d']
            );
        } else {
            // Insert new abandoned cart record
            $wpdb->insert(
                $table_name,
                [
                    'session_id' => $session_id,
                    'customer_email' => $customer_email,
                    'customer_name' => $customer_name,
                    'customer_phone' => $customer_phone,
                    'cart_contents' => $serialized_cart_contents,
                    'total_value' => $total_value,
                    'billing_address' => $billing_address,
                    'shipping_address' => $shipping_address,
                    'abandoned_at' => null,
                    'status' => 'active',
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ],
                ['%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s']
            );
        }
    }

    private function get_payment_method_title_from_session() {
        if (WC()->session) {
            $chosen_payment_method = WC()->session->get('chosen_payment_method'); // Get payment method slug
    
            if ($chosen_payment_method) {
                $payment_gateways = WC()->payment_gateways->payment_gateways(); // Get all payment methods
    
                if (isset($payment_gateways[$chosen_payment_method])) {
                    return $payment_gateways[$chosen_payment_method]->get_title(); // Return payment method title
                }
            }
        }
    
        return 'No payment method selected';
    }
    

    private function delete_cart_by_session_id($session_id, $table_name) {
        global $wpdb;
    
        $wpdb->delete($table_name, ['session_id' => $session_id, 'status' => 'active'], ['%s', '%s']);
    }    
    

    private function get_selected_payment_method() {
        // Get chosen payment method ID
        $chosen_payment_method = WC()->session->get('chosen_payment_method');
    
        // Get available payment gateways
        $payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
    
        // Return payment method name as a string, or a default message
        return isset($payment_gateways[$chosen_payment_method]) 
            ? $payment_gateways[$chosen_payment_method]->get_title() // Get the name as a string
            : __('No payment method selected', 'woocommerce');
    }
    


    /**
     * Get the name of the selected shipping method.
     *
     * @return string Shipping method name or default message
     */
    private function get_selected_shipping_method() {
        // Get chosen shipping methods from session
        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');

        // Ensure a shipping method is selected
        if (empty($chosen_shipping_methods) || !is_array($chosen_shipping_methods)) {
            return __('No shipping method selected', 'woocommerce');
        }

        // Get shipping method ID (usually formatted like "flat_rate:1" or "free_shipping:2")
        $shipping_method_id = reset($chosen_shipping_methods); // Get the first selected method

        // Get available shipping rates for the current session
        $shipping_packages = WC()->shipping->get_packages();
        foreach ($shipping_packages as $package) {
            foreach ($package['rates'] as $rate_id => $rate) {
                if ($rate_id === $shipping_method_id) {
                    return $rate->get_label(); // Return human-readable shipping method name
                }
            }
        }

        return __('Unknown shipping method', 'woocommerce');
    }
       

    private function is_repeat_customer_by_billing_phone($billing_phone=null, $billing_email=null, $status='wc-completed') {
        if (empty($billing_phone) && empty($billing_email)) {
            return false; // No billing phone provided, cannot determine repeat status
        }
        
        // Query WooCommerce for all completed orders with the same billing phone
        $args = [
            'status'        => $status,
            'type'          => 'shop_order',
            'limit'         => -1,
            'return'        => 'ids', // Only retrieve order IDs   
        ];



        if (!empty($billing_phone)) {
            $args['billing_phone'] = normalize_phone_number($billing_phone);
        }
        else if (!empty($billing_email)) {
            $args['billing_email'] = $billing_email;
        }

        $orders = wc_get_orders($args);

        return count($orders ?? []) > 0;
    }

    public function deleteAbandonedOrderIfOrderProcessedSuccessfully($order_id, $old_status, $new_status)
    {
        if ($old_status !== 'processing' && $new_status !== 'processing') {
            return;
        }           

        global $wpdb;
    
        // Get the WooCommerce order by ID
        $order = wc_get_order($order_id);
        if (!$order) {
            return; // Exit if the order does not exist
        }
    
        // Retrieve billing phone and email, ensuring the phone number is normalized
        $customer_email = $order->get_billing_email();
        $customer_phone = normalize_phone_number($order->get_billing_phone());
    
        // Define the abandoned cart table name
        $table_name = $wpdb->prefix . __PREFIX . 'abandon_cart';
    
        if (!empty($customer_phone)) {
            // Delete all records with matching phone and status 'active' or 'abandoned'
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $table_name WHERE customer_phone = %s AND (status = 'active' OR status = 'abandoned')",
                    $customer_phone
                )
            );
        } elseif (!empty($customer_email)) {
            // If phone does not exist, delete all records with matching email and status 'active' or 'abandoned'
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $table_name WHERE customer_email = %s AND (status = 'active' OR status = 'abandoned')",
                    $customer_email
                )
            );
        }
    }
       
}
