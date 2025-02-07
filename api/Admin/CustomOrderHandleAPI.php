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
                'methods'             => 'post',
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
     * Get custom orders
     */
    public function create_custom_order(WP_REST_Request $request)
    {
        $data = $this->prepare_order_data_from_request($request);

        $address = $data['address'];
        $payment_method_id = $data['payment_method_id'];
        $shipping_method_id = $data['shipping_method_id'];
        $shipping_cost = $data['shipping_cost'];
        $customer_note = $data['customer_note'];
        $order_status = str_replace('wc-', '', $data['order_status']);
        $order_source = $data['order_source'];
        $coupon_codes  = $data['coupon_codes'];
        
        // Step 1: Initialize the Custom Order
        $order = wc_create_order();
        if (is_wp_error($order)) {
            return new WP_Error('order_creation_failed', 'Failed to create order.', ['status' => 500]);
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
    
        // Step 6: Add customer Note
        if (!empty($customer_note)) {
            $order->set_customer_note($customer_note);
        }
    
        $order->set_created_via('admin');
        $order->set_meta_data('_wc_order_attribution_utm_source', $order_source);
    
        // Step 7: Apply Coupon Codes
        if (!empty($coupon_codes)) {
            foreach ($coupon_codes as $coupon) {
                $order->apply_coupon($coupon);
            }
        }
    
        // Step 8: Calculate Totals
        $order->calculate_totals();
    
        //Step 9: Set the Order Status
        /**
         * status update state should last 
         * other wise product or product price, delivery charge or coupon 
         * calculation not get when send status change sms
         */
        $order->update_status($order_status);

        // Step 10: Save the Order
        $order->save();

        $order_id = $order->get_id(); // Retrieve the new order ID.
        storeFraudDataWhenPlaceOrder($order_id);

        return new WP_REST_Response([
            'status' => 'success',
            'data' => [
                "order_id" => $order_id
            ]
        ], 200);
    }
    

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
        $shipping_cost = isset($payload['shipping_cost']) ? sanitize_text_field($payload['shipping_cost']) : 0;
    
        // Order note
        $customer_note = isset($payload['customer_note']) ? sanitize_textarea_field($payload['customer_note']) : '';
    
        // Order source
        $order_source = isset($payload['order_source']) ? sanitize_text_field($payload['order_source']) : 'website';
    
        // Order status
        $order_status = isset($payload['order_status']) ? sanitize_text_field($payload['order_status']) : 'wc-confirmed';
    
        // Coupon codes
        $coupon_codes = !empty($payload['coupon_codes']) ? array_map('sanitize_text_field', $payload['coupon_codes']) : [];
    
        return [
            'products'          => $products,
            'address'           => $address,
            'payment_method_id' => $payment_method_id,
            'shipping_method_id'=> $shipping_method_id,
            'shipping_cost'     => $shipping_cost,
            'customer_note'        => $customer_note,
            'order_source'      => $order_source,
            'order_status'      => $order_status,
            'coupon_codes'      => $coupon_codes,
        ];
    }
    
    private function add_product_to_order($order, $product_id, $quantity) {
        $product = wc_get_product($product_id);
        if ($product) {
            $order->add_product($product, $quantity); // Add product and quantity to the order
        } else {
            die('Product not found.');
        }
    }

    private function add_payment_method_to_order($order, $payment_method_id)
    {
        // Get the payment gateways
        $payment_gateways = WC()->payment_gateways->get_available_payment_gateways();

        // Check if the provided payment method exists
        if (!isset($payment_gateways[$payment_method_id])) {
            throw new \Exception('Invalid payment method ID.');
        }

        $payment_gateway = $payment_gateways[$payment_method_id];

        // Set the payment method ID
        $order->set_payment_method($payment_gateway->id);

        // Set the payment method title
        $order->set_payment_method_title($payment_gateway->get_title());
    }

    private function add_billing_and_shipping_details_to_order($order, $address) {
        $order->set_address($address, 'billing');
        $order->set_address($address, 'shipping');
    }

    private function add_shipping_method_to_order($order, $shipping_method_id, $shipping_cost = 0)
    {
        // Get all available shipping methods
        $shipping_methods = WC()->shipping->get_shipping_methods();

        // Check if the shipping method exists
        $method = $shipping_methods[$shipping_method_id] ?? null;
        if (!$method) {
            throw new \Exception('Invalid shipping method ID provided.');
        }

        // Get the shipping cost, either from the method settings or as provided
        $calculated_cost = $shipping_cost;
        if ($calculated_cost == 0) {
            $calculated_cost = $method->get_instance_option('cost', '0'); // Default to '0' if not set
        }

        // Create a new shipping item for the order
        $item = new \WC_Order_Item_Shipping();
        $item->set_method_id($shipping_method_id); // Set the shipping method ID
        $item->set_method_title($method->get_title()); // Set the shipping method title
        $item->set_total($calculated_cost); // Set the total shipping cost

        // Add the shipping item to the order
        $order->add_item($item);

        // Save the shipping item to persist it in the order
        $item->save();
    }


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
            $image_id = $product->get_image_id(); // Get the main image ID
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : wc_placeholder_img_src(); // Get the URL or a placeholder
            $product_currency_symbol = get_woocommerce_currency_symbol();

            $response[] = [
                'id'          => $product->get_id(),
                'currency_symbol' => $product_currency_symbol,
                'name'        => $product->get_name(),
                'price'       => $product->get_price(),
                'sku'         => $product->get_sku(),
                'stock_status'=> $product->get_stock_status(),
                'type'        => $product->get_type(),
                'permalink'   => get_permalink($product->get_id()),
                'image'       => $image_url, // Add image URL
            ];
        }
    
        // Check if no products were found
        if (empty($response)) {
            return new \WP_REST_Response([
                'status'  => 'success',
                'message' => 'No products found.',
                'data'    => [],
            ], 200);
        }
    
        // Return the response
        return new \WP_REST_Response([
            'status'  => 'success',
            'message' => 'Products retrieved successfully.',
            'data'    => $response,
        ], 200);
    }    
    
}