<?php
namespace WooEasyLife\Frontend;

class Remote_UsePackageHistory {
    public function __construct()
    {
        add_action('woocommerce_order_status_changed', [$this, 'confirmOrderPlaced'], 11, 4);
    }
    public function confirmOrderPlaced($order_id, $old_status, $new_status, $order) {

        if($new_status != 'processing') {
            return;
        }
        global $license_key;
    
        $url = get_api_end_point("package-order-use");
    
        // Get the WooCommerce order object
        
        if (!$order) {
            return [
                'status'  => 'error',
                'message' => 'Invalid order ID.',
            ];
        }
    
        $cart_contents = [];
        $total_value = 0;
    
        foreach ($order->get_items() as $item_id => $item) {
            // Ensure item is a product
            if (!$item instanceof \WC_Order_Item_Product) {
                continue;
            }
    
            $product = $item->get_product(); // WC_Product object
    
            if (!$product) {
                continue; // Skip if product data is not found
            }
    
            $cart_contents[] = [
                'order_id'    => $order_id,
                'name'        => $product->get_name(),
                'product_url' => get_permalink($product->get_id()),
                'quantity'    => $item->get_quantity(),
                'price'       => $product->get_price(), // Unit price
                'total_price' => $item->get_total(), // Total for this item
            ];
    
            $total_value += $item->get_total();
        }
    
        // Encode data properly for API request
        $data = json_encode([
            'order_count' => 1,
            'use_details' => [[
                "cart_contents" => $cart_contents,
                "total_value" => $total_value
            ]]
        ]);
    
        $headers = [
            'Authorization' => 'Bearer ' . $license_key,
            'Content-Type'  => 'application/json', // JSON format
            'origin' => site_url()
        ];

        error_log($data);
    
        // Use wp_remote_post for HTTP requests
        $response = wp_remote_post($url, [
            'method'      => 'POST',
            'body'        => $data,
            'headers'     => $headers,
            'timeout'     => 45,
            'sslverify'   => false,
        ]);

        error_log(json_encode($response));


        // Check for errors in the response
        if (is_wp_error($response)) {
            return [
                'status'  => 'error',
                'message' => $response->get_error_message(),
            ];
        }

        $order->update_meta_data('is_wel_order_handled', 1);

        // Decode and return the response
        $response_body = wp_remote_retrieve_body($response);
        $response_body = json_decode($response_body, true) ?: $response_body;

        $order->update_meta_data( 'is_wel_balance_cut', 1);
        if(is_array($response_body) && $response_body['is_order_limit_over']){
            $order->update_meta_data( 'is_wel_balance_cut', 0);
        }

        $order->save();
        return [
            'status'  => 'success',
            'message' => $response_body,
        ];
    }    
}