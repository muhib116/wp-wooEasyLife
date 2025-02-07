<?php
namespace WooEasyLife\Frontend;

class CustomerHandler {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . __PREFIX . 'customer_data';

        // Hook into WooCommerce order creation
        // add_action('woocommerce_checkout_order_created', [$this, 'handle_customer_data'], 10, 2);
        add_action('woocommerce_new_order', [$this, 'handle_customer_data'], 10, 2);
    }


    public function recalculate_customer_data($table_id) {
        global $wpdb;
    
        // Fetch existing customer data
        $existing_customer = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d LIMIT 1",
                $table_id
            ),
            ARRAY_A
        );
    
        // If customer not found, exit early
        if (!$existing_customer) {
            return;
        }
    
        // Extract order, phone, and email
        $order_id = $existing_customer['order_id'] ?? null;
        $phone = $existing_customer['phone'] ?? null;
        $email = $existing_customer['email'] ?? null;
    
        // If no order ID, phone, or email, stop processing
        if (empty($order_id) || (empty($phone) && empty($email))) {
            return;
        }
    
        // Retrieve order object
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
    
        // Calculate key customer metrics
        $order_frequency = $this->calculate_order_frequency($phone, $email);
        $total_orders = $this->get_total_orders($phone, $email);
        $fraud_score = $this->calculate_fraud_score($order);
        $total_spent = $this->get_total_spent($phone, $email);
    

        // Prepare updated customer data
        $customer_data = [
            'order_frequency' => $order_frequency,
            'total_orders'    => $total_orders,
            'fraud_score'     => $fraud_score,
            'total_spent'     => $total_spent,
            'customer_type'   => $this->assign_customer_tags($total_orders, $order_frequency, $total_spent),
            'last_order_date' => current_time('mysql'),
            'updated_at'      => current_time('mysql'),
        ];

        // Update customer record
        $wpdb->update(
            $this->table_name,
            $customer_data,
            ['id' => $table_id]
        );
    }
    

    /**
     * Handle customer insertion or update when a new order is placed
     */
    public function handle_customer_data($order_id, $order) {
        global $wpdb;

        if(empty($order) && $order_id) {
            $order = wc_get_order($order_id);
        }
    
        // Ensure $order is a valid WooCommerce order object
        if (!$order instanceof \WC_Order) {
            return;
        }
    
        // Extract billing details directly from $order
        $phone = normalize_phone_number($order->get_billing_phone());
        $email = sanitize_email($order->get_billing_email());
        $first_name = sanitize_text_field($order->get_billing_first_name());
        $last_name = sanitize_text_field($order->get_billing_last_name());
        $address = sanitize_text_field($order->get_billing_address_1() . ' ' . $order->get_billing_address_2());
        $city = sanitize_text_field($order->get_billing_city());
        $state = sanitize_text_field($order->get_billing_state());
        $postcode = sanitize_text_field($order->get_billing_postcode());
        $country = sanitize_text_field($order->get_billing_country());
    
        // If both phone and email are missing, stop processing
        if (empty($phone) && empty($email)) {
            return;
        }
    
        // Fetch order frequency & total orders for customer
        $order_frequency = $this->calculate_order_frequency($phone, $email);
        $total_orders = $this->get_total_orders($phone, $email);
        $total_complete_orders = $this->get_total_orders($phone, $email, true);
        $referral_source = $this->get_referral_source($order);
        $fraud_score = $this->calculate_fraud_score($order);
        $total_spent = $this->get_total_spent($phone, $email);
    
        // Search for an existing customer using phone or email
        $existing_customer = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE phone = %s OR email = %s LIMIT 1",
                $phone, $email
            ),
            ARRAY_A
        );
    
        // Prepare customer data for insert/update
        $customer_data = [
            'customer_id'     => $order->get_customer_id(),
            'order_id'        => $order->get_id(),
            'phone'           => $phone,
            'email'           => $email,
            'first_name'      => $first_name,
            'last_name'       => $last_name,
            'address'         => $address,
            'city'            => $city,
            'state'           => $state,
            'postcode'        => $postcode,
            'country'         => $country,
            'order_frequency' => $order_frequency,
            'total_orders'    => $total_orders,
            'total_complete_orders' => $total_complete_orders,
            'referral_source' => $referral_source,
            'fraud_score'     => $fraud_score,
            'total_spent'     => $total_spent,
            'last_order_date' => current_time('mysql'),
            'updated_at'      => current_time('mysql'),
        ];
    
        if ($existing_customer) {
            // Assign customer type/tags based on history
            $customer_data['customer_type'] = $this->assign_customer_tags($existing_customer);
    
            // Update existing customer record
            $wpdb->update(
                $this->table_name,
                $customer_data,
                ['id' => $existing_customer['id']]
            );
        } else {
            // Insert new customer record
            $customer_data['first_order_date'] = current_time('mysql');
            $customer_data['created_at'] = current_time('mysql');
    
            $wpdb->insert($this->table_name, $customer_data);
        }

        return $customer_data;
    }
    
    static function get_customer_data($billing_phone = null, $billing_email = null) {
        global $wpdb;
    
        // Validate input
        if (empty($billing_phone) && empty($billing_email)) {
            return null; // No valid identifier provided
        }
    
        // Define the table name
        $table_name = $wpdb->prefix . __PREFIX . 'customer_data';
    
        // Prioritize phone, then fallback to email
        $query = "SELECT * FROM {$table_name} WHERE ";
        $query .= !empty($billing_phone) ? "phone = %s" : "email = %s";
        
        // Fetch customer data
        $customer_data = $wpdb->get_row(
            $wpdb->prepare($query, !empty($billing_phone) ? $billing_phone : $billing_email),
            ARRAY_A
        );
    
        return $customer_data ?: null;
    }

    /**
     * Get total orders for a customer by phone or email
     */
    private function get_total_orders($billing_phone = null, $billing_email = null, $onlyCompleteOrder=false) {
        // Prioritize phone, then fallback to email
        $identifier = !empty($billing_phone) ? $billing_phone : $billing_email;
    
        if (empty($identifier)) {
            return 0; // No valid identifier, return 0
        }
    
        // $args = array_merge([
        //     'limit'       => -1,
        //     'return'      => 'ids',
        // ], getMetaDataOfOrderForArgs());
    
        $args = [
            'limit'       => -1,
            'return'      => 'ids',
        ];

        if($onlyCompleteOrder) {
            $args['status'] = 'wc-completed';
        }
    
        if (!empty($billing_phone)) {
            $args['billing_phone'] = $billing_phone;
        } else if (!empty($billing_email)) {
            $args['billing_email'] = $billing_email;
        }
    
        // Fetch customer orders using WooCommerce function
        $customer_orders = wc_get_orders($args);
    
        return count($customer_orders);
    }    
    
    /**
     * Calculate order frequency (Orders per day)
     */
    public function calculate_order_frequency($billing_phone=null, $billing_email=null) {
        // Prioritize phone, then fallback to email
        $identifier = !empty($billing_phone) ? $billing_phone : $billing_email;

        if (empty($identifier)) {
            return 0; // No valid identifier, return 0 frequency
        }

        // $args = array_merge([
        //     'limit'       => -1,
        //     'return'      => 'ids',
            
        // ], getMetaDataOfOrderForArgs());

        $args = [
            'limit'       => -1,
            'return'      => 'ids',
            
        ];

        if($billing_phone){
            $args['billing_phone'] = $billing_phone;
        }else if($billing_email){
            $args['billing_email'] = $billing_email;
        }

        // Fetch all orders associated with this phone or email
        $customer_orders = wc_get_orders($args);

        $total_orders = count($customer_orders);

        if ($total_orders <= 1) {
            return $total_orders; // Single or no orders mean no frequency calculation needed
        }

        // Get first and last order
        $first_order = wc_get_order(min($customer_orders));
        $last_order = wc_get_order(max($customer_orders));

        if (!$first_order || !$last_order) {
            return 0; // Safety check
        }

        // Get timestamps for first and last orders
        $first_order_date = strtotime($first_order->get_date_created()->format('Y-m-d'));
        $last_order_date = strtotime($last_order->get_date_created()->format('Y-m-d'));

        // Calculate days between first and last order
        $days_between = ($last_order_date - $first_order_date) / (60 * 60 * 24);

        // Calculate order frequency (Orders per day)
        return $days_between > 0 ? round($total_orders / $days_between, 2) : $total_orders;
    }

    private function assign_customer_tags($existingCustomer) 
    {
        $billing_phone = $existingCustomer['phone'];
        $billing_email = $existingCustomer['email'];

        $total_orders = $this->get_total_orders($billing_phone, $billing_email);
        $order_frequency = $this->calculate_order_frequency($billing_phone, $billing_email);

        
        $tags = 'fraud';
    
        // Assign "New" tag for first-time customers
        if ($total_orders == 1) {
            $tags = 'new';
        }
        else if ($total_orders > 1 && $order_frequency < 1) {
            $tags = 'returning';
        }
    
        // Assign "Loyal" tag if the customer has placed a high number of orders
        else if ($total_orders >= 10 && $order_frequency < 1) {
            $tags = 'loyal';
        }
    
        // Assign "VIP" tag if the customer orders frequently
        else if ($total_orders > 20 && $order_frequency < 1) {
            $tags = 'vip';
        }
    
        return $tags;
    }

    private function get_referral_source($order) {
        // Example: Get UTM source stored as meta
        return $order->get_meta('_wc_order_attribution_utm_source', true) ?: 'Direct';
    }

    private function get_total_spent($billing_phone = null, $billing_email = null) {
        // Validate input
        if (empty($billing_phone) && empty($billing_email)) {
            return 0; // No valid identifier, return 0
        }
    
        // Build WooCommerce query args
        // $args = array_merge([
        //     'status'      => ['wc-completed'],
        //     'limit'       => -1, // Fetch all completed orders
            
        // ], getMetaDataOfOrderForArgs());

        $args = [
            'status'      => ['wc-completed'],
            'limit'       => -1, // Fetch all completed orders
            
        ];
    
        // Prioritize phone, then fallback to email
        if (!empty($billing_phone)) {
            $args['billing_phone'] = $billing_phone;
        } elseif (!empty($billing_email)) {
            $args['billing_email'] = $billing_email;
        }
    
        // Fetch all orders for this customer
        $customer_orders = wc_get_orders($args);
        
        // Calculate total spent amount
        $total_spent = 0;
        foreach ($customer_orders as $order) {
            $total_spent += (float) $order->get_total(); // Ensure value is numeric
        }
    
        return $total_spent;
    }
    private function calculate_fraud_score($order) {
        $score = 0;
    
        // Extract relevant order details
        $billing_phone = normalize_phone_number($order->get_billing_phone());
        $billing_email = $order->get_billing_email();
        $customer_ip = $order->get_customer_ip_address();
        $total_orders = $this->get_total_orders($billing_phone, $billing_email);
    
        // 1️⃣ **🚚 Calculate Courier Fraud Score**
        $score += $this->get_courier_fraud_score($order);
    
        // 2️⃣ **🏠 Mismatched Billing & Shipping Address → Potential fraud**
        if ($order->get_billing_address_1() !== $order->get_shipping_address_1()) {
            $score += 10;
        }

        // 📦 **Check for Address Length to Minimize Delivery Risk**
        $min_length = 10; // Minimum length for a valid address
        $short_address_penalty = 5; // Penalty score for short addresses

        // If Billing Address 1 is too short, increase delivery risk score
        if (strlen(trim($order->get_billing_address_1())) < $min_length) {
            $score += $short_address_penalty;
        }

        // If Shipping Address 1 is too short, increase delivery risk score
        if (strlen(trim($order->get_shipping_address_1())) < $min_length) {
            $score += $short_address_penalty;
        }

        // Billing Address 2 is often optional, but if present and too short, add a smaller penalty
        if (!empty(trim($order->get_billing_address_2())) && strlen(trim($order->get_billing_address_2())) < $min_length) {
            $score += 2;
        }

        // Similarly, check Shipping Address 2
        if (!empty(trim($order->get_shipping_address_2())) && strlen(trim($order->get_shipping_address_2())) < $min_length) {
            $score += 2;
        }
    
        // 4️⃣ **🛑 Blacklist Check → If customer email, phone, or IP is blacklisted**
        $totalBlacklistedRecord = $this->is_blacklisted($billing_phone, $billing_email, $customer_ip);
        if ($totalBlacklistedRecord) {
            $score += $totalBlacklistedRecord * (50/3); // High risk if blacklisted
        }
    
        // 5️⃣ **❌ Multiple failed/canceled orders → Possible fraud**
        $failed_orders = $this->get_failed_orders_count($billing_phone, $billing_email);
        if ($failed_orders >= 4) {
            $score += 20;
        } elseif ($failed_orders >= 2) {
            $score += 10;
        }
    
        // 6️⃣ **📊 Order Frequency → Unusual frequency patterns**
        $order_frequency = $this->getOrderFrequency($billing_phone, $billing_email);
    
        if ($order_frequency < 0.3 || $total_orders == 1) {
            $score += 0; // Low frequency, no risk
        } elseif ($order_frequency >= 0.3 && $order_frequency < 0.6) {
            $score += 5; // Mild risk
        } elseif ($order_frequency >= 0.6 && $order_frequency < 1.2) {
            $score += 15; // Moderate risk
        } elseif ($order_frequency >= 1.2 && $order_frequency < 2) {
            $score += 25; // High risk
        } elseif ($order_frequency >= 2) {
            $score += 40; // Very high risk
        }
    
        // 7️⃣ **🚨 High Order Amount on First Order → Suspicious**
        $order_total = (float) $order->get_total();
        $total_orders = $this->get_total_orders($billing_phone, $billing_email);
    
        if ($total_orders == 1 && $order_total > 5000) {
            $score += 25; // High fraud risk for large first-time orders
        } elseif ($total_orders > 1 && $order_total > 10000) {
            $score += 15; // Potential fraud risk for bulk orders
        }
    
        // Final fraud score (capped at 100)
        return min($score, 100);
    }    

    private function get_courier_fraud_score($order) {
        $score = 0;
    
        // Fetch courier fraud data
        $fraud_data = 
        $fraud_data = customer_courier_fraud_data($order);
    
        if (empty($fraud_data) || !isset($fraud_data['report'])) {
            return $score; // No data available, return 0 fraud points
        }
    
        $total_orders = $fraud_data['report']['total_order'] ?? 0;
        $canceled_orders = $fraud_data['report']['cancel'] ?? 0;
        $success_rate = isset($fraud_data['report']['success_rate']) ? floatval(str_replace('%', '', $fraud_data['report']['success_rate'])) : 100;
    
        // 1️⃣ **High order frequency → Potential bulk fraudulent orders**
        if ($total_orders > 10 && ($success_rate < 50 || $canceled_orders > 3)) {
            $score += 20;
        } elseif ($total_orders > 5 && $success_rate < 70) {
            $score += 10;
        }
    
        // 2️⃣ **Multiple failed/canceled orders → Possible fraud**
        if ($canceled_orders >= 5) {
            $score += 30;
        } elseif ($canceled_orders >= 3) {
            $score += 15;
        }
    
        // 3️⃣ **New Customer + High Order Amount → Suspicious**
        if ($total_orders <= 1 && $order->get_total() > 5000) {
            $score += 25;
        } elseif ($total_orders > 1 && $order->get_total() > 10000) {
            $score += 15;
        }
    
        // 4️⃣ **Courier-Specific Fraud Checks**
        foreach ($fraud_data['report']['courier'] ?? [] as $courier) {
            $courier_success_rate = isset($courier['report']['success_rate']) ? floatval(str_replace('%', '', $courier['report']['success_rate'])) : 100;
            $courier_cancel_count = $courier['report']['cancel'] ?? 0;
    
            if ($courier_cancel_count > 2 && $courier_success_rate < 60) {
                $score += 10;
            }
    
            if ($courier_success_rate < 50) {
                $score += 15;
            }
        }
    
        return $score;
    }
    
    private function getOrderFrequency ($phone, $email) {
        global $wpdb;
        
        $existing_customer_data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE phone = %s OR email = %s LIMIT 1",
                $phone, $email
            ),
            ARRAY_A
        );

        $order_frequency = isset($existing_customer_data['order_frequency']) ? $existing_customer_data['order_frequency'] : 0;
        return  $order_frequency;
    }    
   
    private function get_failed_orders_count($billing_phone, $billing_email) {
        // $args = array_merge([
        //     'status'      => ['wc-failed', 'wc-cancelled'],
        //     'limit'       => -1,
        //     'return'      => 'ids',
        // ], getMetaDataOfOrderForArgs());
        $args = array_merge([
            'status'      => ['wc-failed', 'wc-cancelled'],
            'limit'       => -1,
            'return'      => 'ids',
        ]);
    
        if ($billing_phone) {
            $args['billing_phone'] = $billing_phone;
        } elseif ($billing_email) {
            $args['billing_email'] = $billing_email;
        }
    
        return count(wc_get_orders($args));
    }
    
    private function is_blacklisted($phone, $email, $ip) {
        global $wpdb;
        $table_name = $wpdb->prefix . __PREFIX .'block_list';
    
        $blacklist = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE ip_phone_or_email = %s OR ip_phone_or_email = %s OR ip_phone_or_email = %s",
            $phone, $email, $ip
        ));
    
        return (int) $blacklist;
    }
}
