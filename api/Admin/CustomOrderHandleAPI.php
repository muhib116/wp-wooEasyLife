<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class CustomOrderHandleAPI extends WP_REST_Controller
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
        register_rest_route(__API_NAMESPACE, '/create-custom-order', [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_custom_order'],
                'permission_callback' => api_permission_check(),
            ],
        ]);

        register_rest_route(__API_NAMESPACE, '/custom-orders/get-products', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_products'],
                'permission_callback' => api_permission_check(),
            ],
        ]);
    }

    /**
     * Create custom order with COD amount handling
     */
    public function create_custom_order(WP_REST_Request $request)
    {
        try {
            $data = $this->prepare_order_data_from_request($request);

            $address = $data['address'];
            $payment_method_id = $data['payment_method_id'];
            $shipping_method_id = $data['shipping_method_id'];
            $shipping_cost = $data['shipping_cost'];
            $customer_note = $data['customer_note'];
            $order_status = str_replace('wc-', '', $data['order_status']);
            $order_source = $data['order_source'];
            $coupon_codes = $data['coupon_codes'];
            $cod_amount = $data['cod_amount']; // New: COD amount
            $add_order_note = $data['add_order_note']; // New: Flag to add note
            
            // Step 1: Initialize the Custom Order
            $order = wc_create_order();
            if (is_wp_error($order)) {
                return new WP_REST_Response([
                    'status' => 'error',
                    'message' => 'Failed to create order.',
                ], 500);
            }
        
            // Step 2: Add Products to the Order
            foreach ($data['products'] as $item) {
                $this->add_product_to_order($order, $item['id'], $item['quantity']);
            }
        
            // Step 3: Add Billing and Shipping Details
            $this->add_billing_and_shipping_details_to_order($order, $address);
        
            // Step 4: Set Payment Method
            $this->add_payment_method_to_order($order, $payment_method_id);
        
            // Step 5: Add Shipping Method
            $this->add_shipping_method_to_order($order, $shipping_method_id, $shipping_cost);
        
            // Step 6: Add Customer Note
            if (!empty($customer_note)) {
                $order->set_customer_note($customer_note);
            }
        
            $order->set_created_via('admin');
            $order->update_meta_data('_wc_order_attribution_utm_source', $order_source);
        
            // Handle abandoned orders
            if ($order_source == 'abandoned') {
                // Balance already cut when abandoned order created in TrackAbandonCart.php
                // so don't need to cut balance
                $order->update_meta_data('is_wel_balance_cut', 1);
                $order->update_meta_data('is_wel_order_handled', 1);
            }

            // Step 7: Apply Coupon Codes
            if (!empty($coupon_codes)) {
                foreach ($coupon_codes as $coupon) {
                    $order->apply_coupon($coupon);
                }
            }
        
            // Step 8: Calculate Totals
            $order->calculate_totals();
            
            // Get the calculated total before COD modification
            $calculated_total = $order->get_total();
            
            // Step 9: Handle COD Amount Modification
            $cod_modified = false;
            if ($cod_amount !== null && $cod_amount > 0 && $cod_amount !== $calculated_total) {
                // Set the new total
                $order->set_total($cod_amount);
                $cod_modified = true;
                
                // Add order note if requested
                if ($add_order_note) {
                    $note_message = sprintf(
                        'Order Total (COD) manually updated from %s to %s via WEL plugin.',
                        wc_price($calculated_total, ['currency' => $order->get_currency()]),
                        wc_price($cod_amount, ['currency' => $order->get_currency()])
                    );
                    
                    $order->add_order_note(
                        $note_message,
                        0,    // Not a customer note
                        true  // Is a system note
                    );
                }
                
                // Add meta to track manual modification
                $order->update_meta_data('_cod_amount_modified', true);
                $order->update_meta_data('_original_total', $calculated_total);
                $order->update_meta_data('_modified_total', $cod_amount);
                $order->update_meta_data('_cod_modification_date', current_time('mysql'));
                $order->update_meta_data('_cod_modified_by', get_current_user_id());
            }
        
            // Step 10: Set the Order Status
            /**
             * Status update state should last 
             * otherwise product or product price, delivery charge or coupon 
             * calculation not get when send status change SMS
             */
            $order->update_status($order_status);

            // Step 11: Save the Order
            $order->save();

            $order_id = $order->get_id(); // Retrieve the new order ID
            
            // Store fraud data when placing order
            if (function_exists('storeFraudDataWhenPlaceOrder')) {
                storeFraudDataWhenPlaceOrder($order_id);
            }

            // Log order creation
            error_log(sprintf(
                'WEL Custom Order #%d created. Source: %s, Total: %s, COD Modified: %s',
                $order_id,
                $order_source,
                wc_price($order->get_total()),
                $cod_modified ? 'Yes' : 'No'
            ));

            return new WP_REST_Response([
                'status' => 'success',
                'message' => $cod_modified 
                    ? 'Order created successfully with modified COD amount.' 
                    : 'Order created successfully.',
                'data' => [
                    'order_id' => $order_id,
                    'order_number' => $order->get_order_number(),
                    'total' => $order->get_total(),
                    'calculated_total' => $calculated_total,
                    'cod_modified' => $cod_modified,
                    'order_url' => admin_url('post.php?post=' . $order_id . '&action=edit'),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            error_log('WEL Custom Order creation error: ' . $e->getMessage());
            
            return new WP_REST_Response([
                'status' => 'error',
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Prepare order data from request
     */
    private function prepare_order_data_from_request($request) {
        // Get the JSON payload from the request
        $payload = $request->get_json_params();
    
        // Prepare products data
        $products = [];
        if (!empty($payload['products'])) {
            foreach ($payload['products'] as $product) {
                $products[] = [
                    'id'       => isset($product['id']) ? intval($product['id']) : null,
                    'quantity' => isset($product['quantity']) ? intval($product['quantity']) : 1,
                ];
            }
        }
    
        // Prepare address data
        $address = [];
        if (!empty($payload['address'])) {
            foreach ($payload['address'] as $field) {
                $address = array_merge($address, $field);
            }
        }
    
        // Payment method
        $payment_method_id = isset($payload['payment_method_id']) ? sanitize_text_field($payload['payment_method_id']) : '';
    
        // Shipping method
        $shipping_method_id = isset($payload['shipping_method_id']) ? sanitize_text_field($payload['shipping_method_id']) : '';
        $shipping_cost = isset($payload['shipping_cost']) ? floatval($payload['shipping_cost']) : 0;
    
        // Order note
        $customer_note = isset($payload['customer_note']) ? sanitize_textarea_field($payload['customer_note']) : '';
    
        // Order source
        $order_source = isset($payload['order_source']) ? sanitize_text_field($payload['order_source']) : 'website';
    
        // Order status
        $order_status = isset($payload['order_status']) ? sanitize_text_field($payload['order_status']) : 'wc-confirmed';
    
        // Coupon codes
        $coupon_codes = !empty($payload['coupon_codes']) ? array_map('sanitize_text_field', $payload['coupon_codes']) : [];
        
        // COD amount (new)
        $cod_amount = isset($payload['cod_amount']) ? floatval($payload['cod_amount']) : null;
        
        // Add order note flag (new)
        $add_order_note = isset($payload['add_order_note']) ? (bool)$payload['add_order_note'] : true;
    
        return [
            'products'           => $products,
            'address'            => $address,
            'payment_method_id'  => $payment_method_id,
            'shipping_method_id' => $shipping_method_id,
            'shipping_cost'      => $shipping_cost,
            'customer_note'      => $customer_note,
            'order_source'       => $order_source,
            'order_status'       => $order_status,
            'coupon_codes'       => $coupon_codes,
            'cod_amount'         => $cod_amount,
            'add_order_note'     => $add_order_note,
        ];
    }
    
    /**
     * Add product to order
     */
    private function add_product_to_order($order, $product_id, $quantity) {
        $product = wc_get_product($product_id);
        if ($product) {
            $order->add_product($product, $quantity); // Add product and quantity to the order
        } else {
            throw new \Exception('Product not found with ID: ' . $product_id);
        }
    }

    /**
     * Add payment method to order
     */
    private function add_payment_method_to_order($order, $payment_method_id)
    {
        // Get the payment gateways
        $payment_gateways = WC()->payment_gateways->get_available_payment_gateways();

        // Check if the provided payment method exists
        if (!isset($payment_gateways[$payment_method_id])) {
            throw new \Exception('Invalid payment method ID: ' . $payment_method_id);
        }

        $payment_gateway = $payment_gateways[$payment_method_id];

        // Set the payment method ID
        $order->set_payment_method($payment_gateway->id);

        // Set the payment method title
        $order->set_payment_method_title($payment_gateway->get_title());
    }

    /**
     * Add billing and shipping details to order
     */
    private function add_billing_and_shipping_details_to_order($order, $address) {
        if (empty($address)) {
            throw new \Exception('Address information is required.');
        }
        
        $order->set_address($address, 'billing');
        $order->set_address($address, 'shipping');
    }

    /**
     * Add shipping method to order
     * 
     * Handles both direct method_id and instance_id formats
     * WooCommerce uses instance_id when a specific zone/instance is configured
     */
    private function add_shipping_method_to_order($order, $shipping_method_id, $shipping_cost = 0)
    {
        // Get all shipping zones to find the correct method
        $chosen_method = null;
        $shipping_methods = [];

        // Collect methods from all zones
        $zones = \WC_Shipping_Zones::get_zones();
        foreach ($zones as $zone) {
            $zone_obj = new \WC_Shipping_Zone($zone['id']);
            $zone_methods = $zone_obj->get_shipping_methods(false);
            $shipping_methods = array_merge($shipping_methods, $zone_methods);
        }

        // Also get default zone methods
        $default_zone = new \WC_Shipping_Zone(0);
        $default_methods = $default_zone->get_shipping_methods(false);
        $shipping_methods = array_merge($shipping_methods, $default_methods);

        // Try to find the method by instance_id first (preferred)
        foreach ($shipping_methods as $method) {
            // Match by instance_id (e.g., "1", "2", etc.)
            if ((string)$method->get_instance_id() === (string)$shipping_method_id) {
                $chosen_method = $method;
                break;
            }
        }

        // Fallback: try to match by method_id (e.g., "free_shipping", "flat_rate")
        if (!$chosen_method) {
            foreach ($shipping_methods as $method) {
                if ($method->id === $shipping_method_id) {
                    $chosen_method = $method;
                    break;
                }
            }
        }

        // If still not found, throw error
        if (!$chosen_method) {
            throw new \Exception('Shipping method not found: ' . $shipping_method_id);
        }

        // Get the shipping cost
        $calculated_cost = $shipping_cost;
        if ($calculated_cost == 0) {
            $calculated_cost = $chosen_method->get_instance_option('cost', '0');
        }

        // Create a shipping item for the order
        $item = new \WC_Order_Item_Shipping();
        $item->set_method_id($chosen_method->id);                    // Set the method ID (e.g., "free_shipping")
        $item->set_method_title($chosen_method->get_title());       // Set the method title from the actual method
        $item->set_total($calculated_cost);                          // Set the cost
        
        // Add instance ID to the item meta for reference
        $item->add_meta_data('instance_id', $chosen_method->get_instance_id(), true);

        // Add the shipping item to the order
        $order->add_item($item);

        // Save the shipping item
        $item->save();
    }

    /**
     * Get products for order creation
     */
    public function get_products(WP_REST_Request $request)
    {
        $search = sanitize_text_field($request->get_param('search'));
    
        // Base arguments for getting products
        $args = [
            'limit' => -1, // Retrieve all products
            'status' => 'publish', // Only published products
        ];
    
        // Handle search by name or ID
        if (!empty($search)) {
            $args['search'] = $search; // Search term for product name or ID
        }
    
        // Fetch products using WooCommerce functions
        $products = wc_get_products($args);
        $response = [];
    
        // Format product data
        foreach ($products as $product) {
            if (!$product || !is_object($product) || !$product->get_id()) {
                // Skip this product if not found or invalid
                continue;
            }
            $image_id = $product->get_image_id(); // Get the main image ID
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : wc_placeholder_img_src(); // Get the URL or a placeholder
            $product_currency_symbol = get_woocommerce_currency_symbol();

            $response[] = [
                'id'              => $product->get_id(),
                'currency_symbol' => $product_currency_symbol,
                'name'            => $product->get_name(),
                'price'           => $product->get_price(),
                'regular_price'   => $product->get_regular_price(),
                'sale_price'      => $product->get_sale_price(),
                'sku'             => $product->get_sku(),
                'stock_status'    => $product->get_stock_status(),
                'stock_quantity'  => $product->get_stock_quantity(),
                'in_stock'        => $product->is_in_stock(),
                'type'            => $product->get_type(),
                'permalink'       => get_permalink($product->get_id()),
                'image'           => $image_url, // Add image URL
            ];
        }
    
        // Check if no products were found
        if (empty($response)) {
            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'No products found.',
                'data'    => [],
            ], 200);
        }
    
        // Return the response
        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Products retrieved successfully.',
            'data'    => $response,
        ], 200);
    }    
}