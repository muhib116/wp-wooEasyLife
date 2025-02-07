<?php
namespace WooEasyLife\Frontend;

class Order_limit {
    function __construct()
    {
        add_action('woocommerce_after_checkout_validation', [$this, 'enforce_order_limit']);
    }

    public function enforce_order_limit() {
        global $config_data;


        if($config_data["daily_order_place_limit_per_customer"])
        {
            $order_limit = $config_data["daily_order_place_limit_per_customer"];
            $billing_email = isset($_POST['billing_email']) ? sanitize_text_field($_POST['billing_email']) : '';
            $billing_phone = isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : '';

            // Query to count orders for the given email or phone number
            $args = array_merge([
                'billing_phone' => normalize_phone_number($billing_phone),
                'post_type'   => 'shop_order',
                'post_status' => 'wc-processing', // You can also include other statuses if needed
                'return'      => 'ids',
                
            ], getMetaDataOfOrderForArgs());
        
            $orders = wc_get_orders($args);
            $order_count = count($orders);
        
            // Check if the user has exceeded the order limit
            if ($order_count >= $order_limit) {
                // Prevent the order from being processed
                throw new \Exception(
                    sprintf(
                        __('You have reached the order limit of %d orders. Please contact our support team for assistance.', 'your-text-domain'),
                        $order_limit
                    )
                );
            }
        }
    }
}