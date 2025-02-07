<?php
namespace WooEasyLife\Admin;

class HandlePastNewOrders {

    public function include_past_new_orders_to_wel_plugin() {
        global $license_key;

        $url = get_api_end_point("package-order-use");
        $data = $this->get_past_new_orders_not_handled_by_wel_plugin();

        $headers = [
            'Authorization' => 'Bearer ' . $license_key,
            'Content-Type'  => 'application/json', // JSON format
            'origin' => site_url()
        ];

        // Use wp_remote_post for HTTP requests
        $response = wp_remote_post($url, [
            'method'      => 'POST',
            'body'        => json_encode($data['remote_api_data']),
            'headers'     => $headers,
            'timeout'     => 45,
            'sslverify'   => false,
        ]);

        // Check for errors in the response
        if (is_wp_error($response)) {
            return [
                'status'  => 'error',
                'message' => $response->get_error_message(),
            ];
        }

        $this->update_meta_data_of_past_orders($data['orders']);
    }
    public function include_missing_new_orders_for_balance_cut_issue() {
        global $license_key;

        $url = get_api_end_point("package-order-use");
        $data = $this->get_missing_new_orders_for_balance_cut_issue();

        $headers = [
            'Authorization' => 'Bearer ' . $license_key,
            'Content-Type'  => 'application/json', // JSON format
            'origin' => site_url()
        ];

        // Use wp_remote_post for HTTP requests
        $response = wp_remote_post($url, [
            'method'      => 'POST',
            'body'        => json_encode($data['remote_api_data']),
            'headers'     => $headers,
            'timeout'     => 45,
            'sslverify'   => false,
        ]);

        // Check for errors in the response
        if (is_wp_error($response)) {
            return [
                'status'  => 'error',
                'message' => $response->get_error_message(),
            ];
        }

        $this->update_meta_data_of_past_orders($data['orders']);
    }

    private function update_meta_data_of_past_orders($orders) 
    {
        if (empty($orders) || !is_array($orders)) {
            return [
                'status'  => 'error',
                'message' => 'No valid orders provided.',
            ];
        }
    
        $updatedOrders = 0;
        $failedOrders = 0;
    
        foreach ($orders as $order) {
            if (!is_object($order) || !method_exists($order, 'update_meta_data') || !method_exists($order, 'save')) {
                $failedOrders++;
                continue;
            }
    
            try {
                $order->update_meta_data('is_wel_order_handled', 1);
                $order->update_meta_data('is_wel_balance_cut', 1);
                $order->save();
                $updatedOrders++;
            } catch (\Exception $e) {
                $failedOrders++;
            }
        }
    
        if ($updatedOrders > 0) {
            return new \WP_REST_Response([
                'status'  => 'success',
                'message' => "Your ($updatedOrders) <strong>Past New orders</strong> have been successfully added to this table.",
                'data' => [
                    'updatedOrders' => $updatedOrders,
                    'failedOrders' => $failedOrders
                ]
            ], 200);
        }

        return new \WP_REST_Response([
            'status'  => 'error',
            'message' => "Failed to update orders. Please try again.",
            'data' => [
                'updatedOrders' => $updatedOrders,
                'failedOrders' => $failedOrders
            ]
        ], 400);
    }

    private function get_past_new_orders_not_handled_by_wel_plugin() 
    {
        $orders = $this->get_past_new_orders();
        $cartContents = [];

        foreach($orders as $order) {
            $cartContents[] = $this->get_order_item($order);
        }
        
        return [
            'orders' => $orders,
            "remote_api_data" => [
                'order_count' => count($orders),
                'use_details' => $cartContents
            ]
        ];     
    }

    private function get_missing_new_orders_for_balance_cut_issue() 
    {
        $orders = $this->get_missing_new_orders();
        $cartContents = [];

        foreach($orders as $order) {
            $cartContents[] = $this->get_order_item($order);
        }
        
        return [
            'orders' => $orders,
            "remote_api_data" => [
                'order_count' => count($orders),
                'use_details' => $cartContents
            ]
        ];     
    }

    private function get_order_item($order) 
    {
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
    
            $cart_contents = [
                'order_id'    => $order->get_id(),
                'name'        => $product->get_name(),
                'product_url' => get_permalink($product->get_id()),
                'quantity'    => $item->get_quantity(),
                'price'       => $product->get_price(), // Unit price
                'total_price' => $item->get_total(), // Total for this item
            ];
    
            $total_value += $item->get_total();
        }

        return [
            "cart_contents" => $cart_contents,
            "total_value" => $total_value
        ];
    }

    private function get_past_new_orders() {
        $args = [
            'status'    => ['wc-processing'],
            'limit'     => -1,
            'type'      => 'shop_order',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'is_wel_order_handled',
                    'compare' => 'NOT EXISTS', // Meta key does not exist
                ]
            ]
        ];
        
        $orders = wc_get_orders($args);
        return $orders;
    }

    private function get_missing_new_orders() {
        $args = [
            'status'    => ['wc-processing'],
            'limit'     => -1,
            'type'      => 'shop_order',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'is_wel_order_handled',
                    'value'   => '1', // Checking if it's explicitly set to "true" (1)
                    'compare' => '='
                ],
                [
                    'relation' => 'OR', // Either it's explicitly false (0), empty, or does not exist
                    [
                        'key'     => 'is_wel_balance_cut',
                        'value'   => '0',
                        'compare' => '='
                    ],
                    [
                        'key'     => 'is_wel_balance_cut',
                        'compare' => 'NOT EXISTS' // Key doesn't exist
                    ],
                    [
                        'key'     => 'is_wel_balance_cut',
                        'value'   => '',
                        'compare' => '='
                    ]
                ]
            ]
        ];
        
        $orders = wc_get_orders($args);
        return $orders;
    }
}