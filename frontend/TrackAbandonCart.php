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

        add_action('woocommerce_thankyou', [$this, 'deleteAbandonedOrderIfOrderProcessedSuccessfully'], 10, 1);
        // add_action('woocommerce_order_status_changed', [$this, 'mark_abandoned_cart_as_recovered'], 10, 3);

        // abandoned marked from abandonedOrderAPI.php
    }

    public function enqueue_my_ajax_script() {
        wp_enqueue_script( 
            'woo-easy-life-ajax-script', 
            plugins_url('includes/checkoutPage/AbandonedOrder.js', __DIR__), 
            [], 
            null, 
            true 
        );
    
        // Localize script to pass the AJAX URL
        wp_localize_script( 'woo-easy-life-ajax-script', 'woo_easy_life_ajax_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'wc_session_id' => ( class_exists( 'WooCommerce' ) && WC()->session ) ? WC()->session->get_customer_id() : ''
        ));        
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
    

    public function store_abandoned_cart_data() {
        global $wpdb;
    
        // Define the table name
        $table_name = $wpdb->prefix . __PREFIX . 'abandon_cart';
        
        // Get WooCommerce session data
        $session = WC()->session;
        $cart = WC()->cart->get_cart();
        
        if (empty($cart)) {
            return; // Exit if the cart is empty
        }
        
        // Get customer details
        $customer_name = WC()->session->get('billing_first_name') . ' ' . WC()->session->get('billing_last_name');
        $customer_email = WC()->session->get('billing_email');
        $customer_phone = normalize_phone_number(WC()->session->get('billing_phone'));
    
        if (empty($customer_phone) && empty($customer_email)) {
            return false;
        }

        // Check if the customer has already placed a new order with 'wc-processing' status
        $existingNewOrder = $this->is_repeat_customer_by_billing_phone($customer_phone, $customer_email, 'wc-processing');
        
        if ($existingNewOrder) {
            return;
        }
    
        $billing_address = WC()->customer->get_billing_address_1() . ', ' . WC()->customer->get_billing_city() . ', ' . WC()->customer->get_billing_state() . ', ' . WC()->customer->get_billing_postcode();
        $shipping_address = WC()->customer->get_shipping_address_1() . ', ' . WC()->customer->get_shipping_city() . ', ' . WC()->customer->get_shipping_state() . ', ' . WC()->customer->get_shipping_postcode();
    
        // Determine if the customer is a repeat customer (check WooCommerce orders)
        $is_repeat_customer = $this->is_repeat_customer_by_billing_phone($customer_phone, $customer_email);
    
        // Serialize cart contents to store in the database
        $cart_contents = [];
        $total_value = 0;
    
        foreach ($cart as $cart_item) {
            $product = $cart_item['data']; // WC_Product object
    
            $cart_contents[] = [
                'name'        => $product->get_name(),
                'image'       => wp_get_attachment_url($product->get_image_id()),
                'product_url' => get_permalink($product->get_id()),
                'quantity'    => $cart_item['quantity'],
                'price'       => $product->get_price(), // Unit price of the product
                'total_price' => $cart_item['line_total'], // Total price for the quantity of this item
            ];
            
            $total_value += $cart_item['line_total'];
        }
    
        $serialized_cart_contents = maybe_serialize($cart_contents);

        
        // Check if the cart is already stored
        $session_id = $session->get_customer_id();
        $existing_cart = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_name 
                WHERE session_id = %s 
                AND (
                    status = 'active' 
                )",
                $session_id
            )
        );
    
        if ($existing_cart) {
            // Update the existing abandoned cart record
            $wpdb->update(
                $table_name,
                [
                    'customer_email'         => $customer_email,
                    'customer_name'          => $customer_name,
                    'customer_phone'         => $customer_phone,
                    'cart_contents'          => $serialized_cart_contents,
                    'total_value'            => $total_value,
                    'billing_address'        => $billing_address,
                    'shipping_address'       => $shipping_address,
                    'is_repeat_customer'     => $is_repeat_customer,
                    'updated_at'             => current_time('mysql'),
                ],
                ['id' => $existing_cart],
                ['%s', '%s', '%s', '%s', '%f', '%s', '%s', '%d', '%s'],
                ['%d']
            );
        } else {
            // Insert a new abandoned cart record
            $wpdb->insert(
                $table_name,
                [
                    'session_id'             => $session_id,
                    'customer_email'         => $customer_email,
                    'customer_name'          => $customer_name,
                    'customer_phone'         => $customer_phone,
                    'cart_contents'          => $serialized_cart_contents,
                    'total_value'            => $total_value,
                    'billing_address'        => $billing_address,
                    'shipping_address'       => $shipping_address,
                    'is_repeat_customer'     => $is_repeat_customer,
                    'abandoned_at'           => null, // Explicitly set as NULL
                    'status'                 => 'active', // Mark cart as active initially
                    'created_at'             => current_time('mysql'),
                    'updated_at'             => current_time('mysql'),
                ],
                [
                    '%s', // session_id
                    '%s', // customer_email
                    '%s', // customer_name
                    '%s', // customer_phone
                    '%s', // cart_contents
                    '%f', // total_value
                    '%s', // billing_address
                    '%s', // shipping_address
                    '%d', // is_repeat_customer
                    '%s', // abandoned_at (NULL is safely handled with %s)
                    '%s', // status
                    '%s', // created_at
                    '%s', // updated_at
                ]
            );
        }
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

        $completed_orders = wc_get_orders($args);

        // If there are any remaining completed orders, the customer is a repeat customer
        return count($completed_orders) > 0;
    }

    public function deleteAbandonedOrderIfOrderProcessedSuccessfully($order_id) {
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
    
        // Check if an abandoned order exists for this customer where status is 'active'
        $abandoned_cart_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE (customer_email = %s OR customer_phone = %s) AND status = 'active'",
                $customer_email,
                $customer_phone
            )
        );
    
        if ($abandoned_cart_id) {
            // Delete the abandoned order record
            $wpdb->delete(
                $table_name,
                ['id' => $abandoned_cart_id],
                ['%d']
            );
        }
    }    
}
