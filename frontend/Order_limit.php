<?php
namespace WooEasyLife\Frontend;

class Order_limit {
    public function __construct()
    {
        add_action('woocommerce_after_checkout_validation', [$this, 'enforce_order_limit'], 10, 2);
    }

    /**
     * Enforces a daily order limit for customers by checking User ID, Email, Phone Number, AND IP Address.
     * This provides a robust mechanism to prevent limit bypass, especially from guest users.
     * A limit of 0 is considered unlimited.
     *
     * @param array     $data   An array of posted data.
     * @param \WP_Error $errors Validation errors object.
     */
    public function enforce_order_limit($data, $errors) {
        global $config_data;
        
        if (!is_wel_license_valid()) {
            return; // Exit the *current* function if the license is not valid.
        }

        // Step 1: Check if the feature is enabled and get the limit.
        $order_limit = isset($config_data["daily_order_place_limit_per_customer"]) ? intval($config_data["daily_order_place_limit_per_customer"]) : 0;
        if ($order_limit <= 0) {
            return; // Exit if the limit is set to 0 (unlimited) or not configured.
        }

        // Step 2: Get all available customer identifiers.
        $user_id       = get_current_user_id();
        $billing_email = isset($data['billing_email']) ? sanitize_email($data['billing_email']) : '';
        $billing_phone = isset($data['billing_phone']) ? normalize_phone_number(sanitize_text_field($data['billing_phone'])) : '';
        $customer_ip   = get_customer_ip();

        if (!$user_id && !$billing_email && !$billing_phone && !$customer_ip) {
            return; // Cannot identify the customer in any way.
        }

        // Step 3: Prepare the base query arguments for the last 24 hours.
        $base_args = [
            'type'        => 'shop_order',
            'return'      => 'ids',
            'limit'       => -1,
            'status'      => ['wc-processing', 'wc-completed', 'wc-on-hold', 'wc-pending'],
            'date_query'  => [
                [
                    'after'     => '24 hours ago',
                    'inclusive' => true,
                ],
            ],
            // This ensures we only count orders handled by Woo Easy Life as per your requirement.
            'meta_query' => getMetaDataOfOrderForArgs()['meta_query'],
        ];

        // Step 4: Find all order IDs associated with ANY of the identifiers.
        $all_order_ids = [];

        // Find by User ID (if logged in)
        if ($user_id) {
            $user_orders = wc_get_orders(array_merge($base_args, ['customer_id' => $user_id]));
            $all_order_ids = array_merge($all_order_ids, $user_orders);
        }

        // Find by Email (if provided)
        if ($billing_email) {
            $email_orders = wc_get_orders(array_merge($base_args, ['customer' => $billing_email]));
            $all_order_ids = array_merge($all_order_ids, $email_orders);
        }

        // Find by Phone (if provided)
        if ($billing_phone) {
            $phone_orders = wc_get_orders(array_merge($base_args, ['billing_phone' => $billing_phone]));
            $all_order_ids = array_merge($all_order_ids, $phone_orders);
        }
        
        // Find by IP Address (most crucial for guests)
        if ($customer_ip) {
            // wc_get_orders doesn't support querying by IP directly, so we add a meta_query.
            $ip_args = $base_args;
            $ip_args['meta_query'][] = [
                'key' => '_customer_ip_address',
                'value' => $customer_ip,
                'compare' => '=',
            ];
            $ip_orders = wc_get_orders($ip_args);
            $all_order_ids = array_merge($all_order_ids, $ip_orders);
        }
        
        // Step 5: Count unique orders and check against the limit.
        $unique_order_ids = array_unique($all_order_ids);
        $order_count = count($unique_order_ids);
    
        if ($order_count >= $order_limit) {
            $admin_phone = $config_data['admin_phone'] ?? '';
            $error_message = "আপনি আজকের অর্ডারের সীমা ($order_limit) পূর্ণ করেছেন।";  

            if ($admin_phone) {  
                $error_message .= ' পুনরায় অর্ডার করতে চাইলে আমাদের সাথে যোগাযোগ করুন: ';  
                $error_message .= '<a href="tel:'.$admin_phone.'">'.$admin_phone.'</a>.';  
            }  

            throw new \Exception(
                __($error_message, 'your-text-domain')
            );
        }
    }
}