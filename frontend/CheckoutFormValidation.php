<?php
namespace WooEasyLife\Frontend;

class CheckoutFormValidation {
    public function __construct()
    {   
        add_action('woocommerce_checkout_create_order', [$this, 'modify_order_phone'], 10, 2);
        add_action('woocommerce_after_checkout_validation', [$this, 'form_validation']);
    }

    public function form_validation() {
        global $config_data;

        if(!empty($config_data['validate_duplicate_order'])) 
        {
            $admin_phone = $config_data['admin_phone'];
            // validate same product repeat order
            $last_order_id = $this->has_same_product_in_last_order($billing_phone, $billing_email);
            if($last_order_id) 
            {
                // Customized error message
                $error_message = 'আপনি ইতিমধ্যে এই পণ্যটির জন্য একটি অর্ডার করেছেন।';
                $error_message .= ' আপনার আগের অর্ডার আইডি: ' . '<strong>#' . $last_order_id . '</strong>.';
                $error_message .= ' যদি আপনি একই পণ্য আবার অর্ডার করতে চান, তাহলে দয়া করে আমাদের সাথে যোগাযোগ করুন: ';
                $error_message .= '<a href="tel:'.$admin_phone.'">'.$admin_phone.'</a>.';

                // Throw exception
                throw new \Exception($error_message);

                // Add error to WooCommerce errors
                $errors->add('validation', $error_message);
                return;
            }
        }

        if (empty($config_data['validate_checkout_form'])) {
            return;
        }

        // Retrieve and sanitize user inputs
        $billing_address_1 = isset($_POST['billing_address_1']) ? sanitize_text_field($_POST['billing_address_1']) : '';
        $billing_address_2 = isset($_POST['billing_address_2']) ? sanitize_text_field($_POST['billing_address_2']) : '';
        $billing_phone     = isset($_POST['billing_phone']) ? normalize_phone_number(sanitize_text_field($_POST['billing_phone'])) : '';
        $billing_email     = isset($_POST['billing_email']) ? sanitize_text_field($_POST['billing_email']) : '';
    
        $shipping_address_1 = isset($_POST['shipping_address_1']) ? sanitize_text_field($_POST['shipping_address_1']) : '';
        $shipping_address_2 = isset($_POST['shipping_address_2']) ? sanitize_text_field($_POST['shipping_address_2']) : '';
        $shipping_phone = isset($_POST['shipping_phone']) ? sanitize_text_field($_POST['shipping_phone']) : '';
    
        $first_name = isset($_POST['billing_first_name']) ? sanitize_text_field($_POST['billing_first_name']) : '';
        $last_name  = isset($_POST['billing_last_name']) ? sanitize_text_field($_POST['billing_last_name']) : '';
    
        // Combine billing address 1 and 2
        $billing_address = trim($billing_address_1 . ' ' . $billing_address_2);
        // Combine shipping address 1 and 2 (if provided)
        $shipping_address = trim($shipping_address_1 . ' ' . $shipping_address_2);
        // Combine first_name and last_name
        $name = trim($first_name . ' ' . $last_name);
    
        // Validate billing address
        if (!$this->validate_address($billing_address)) {
            throw new \Exception(__('আপনার দেওয়া ঠিকানাটি সঠিক বলে মনে হচ্ছে না। অনুগ্রহ করে সম্পূণর্ন ঠিকানাটি সঠিকভাবে লিখুন।', 'your-text-domain'));
            $errors->add( 'validation', 'আপনার দেওয়া ঠিকানাটি সঠিক বলে মনে হচ্ছে না। অনুগ্রহ করে সম্পূণর্ন ঠিকানাটি সঠিকভাবে লিখুন।' );
            return;
        }
    
        // Validate shipping address (if provided)
        if (!empty($shipping_address_1) && !$this->validate_address($shipping_address)) {
            throw new \Exception(__('আপনার দেওয়া শিপিং এর ঠিকানাটি সঠিক বলে মনে হচ্ছে না। অনুগ্রহ করে সম্পূণর্ন ঠিকানাটি সঠিকভাবে লিখুন।', 'your-text-domain'));
            $errors->add( 'validation', 'আপনার দেওয়া শিপিং এর ঠিকানাটি সঠিক বলে মনে হচ্ছে না। অনুগ্রহ করে সম্পূণর্ন ঠিকানাটি সঠিকভাবে লিখুন।' );
            return;
        }
    
        // Validate first name and last name
        if (!$this->validate_name($name)) {
            throw new \Exception(__('আপনার নামটি সঠিক বলে মনে হচ্ছে না। অনুগ্রহ করে আপনার সঠিক নাম প্রদান করুন।', 'your-text-domain'));
            $errors->add( 'validation', 'আপনার নামটি সঠিক বলে মনে হচ্ছে না। অনুগ্রহ করে আপনার সঠিক নাম প্রদান করুন।');
            return;
        }
    
        // Validate billing phone number
        if (!validate_BD_phoneNumber(normalize_phone_number($billing_phone))) { // Fixed the logic
            throw new \Exception(__('আপনার বিলিং ফোন নম্বরটি সঠিক নয়। অনুগ্রহ করে একটি সঠিক নম্বর প্রদান করুন।.', 'your-text-domain'));
            $errors->add( 'validation', 'আপনার বিলিং ফোন নম্বরটি সঠিক নয়। অনুগ্রহ করে একটি সঠিক নম্বর প্রদান করুন।.');
            return;
        }
    
        // Validate shipping phone number (if provided)
        if (!empty($shipping_phone) && !validate_BD_phoneNumber(normalize_phone_number($shipping_phone))) { // Fixed the logic
            throw new \Exception(__('আপনার শিপিং ফোন নম্বরটি সঠিক নয়। অনুগ্রহ করে একটি সঠিক নম্বর প্রদান করুন।', 'your-text-domain'));
            $errors->add( 'validation', 'আপনার শিপিং ফোন নম্বরটি সঠিক নয়। অনুগ্রহ করে একটি সঠিক নম্বর প্রদান করুন।');
            return;
        }
    }
    
    public function has_same_product_in_last_order($billing_phone = null, $billing_email = null) {
        // Get the WooCommerce cart session items
        $cart = WC()->cart->get_cart();
        if (empty($cart)) {
            return false; // No products in cart
        }
    
        // Prepare order query args
        $args = [
            'limit'       => 1, // Get the most recent order
            'orderby'     => 'date',
            'order'       => 'DESC',
            'status'      => ['wc-processing', 'wc-confirmed', 'wc-on-hold', 'wc-pending'], // Only check active orders
            'type'        => 'shop_order' // Ensure only WooCommerce orders are retrieved
        ];
    
        // Check by billing phone or email
        if ($billing_phone) {
            $args['billing_phone'] = $billing_phone;
        } elseif ($billing_email) {
            $args['billing_email'] = $billing_email;
        }
    
        // Fetch orders
        $orders = wc_get_orders($args);
        if (empty($orders)) {
            return false; // No previous order found
        }
    
        // Get the most recent order
        $last_order = $orders[0];
        $last_order_id = $last_order->get_id();
    
        // Get products from the last order
        $ordered_items = $last_order->get_items();
        $ordered_product_ids = [];
    
        foreach ($ordered_items as $item) {
            $ordered_product_ids[] = $item->get_product_id();
        }
    
        // Get cart product IDs
        $cart_product_ids = [];
        foreach ($cart as $cart_item) {
            $cart_product_ids[] = $cart_item['product_id'];
        }
    
        // Use array_diff() to check if both arrays are identical
        if (empty(array_diff($cart_product_ids, $ordered_product_ids)) && empty(array_diff($ordered_product_ids, $cart_product_ids))) {
            return $last_order_id; // All products match
        }
    
        return false; // No exact match found
    }
    

    public function modify_order_phone($order, $data) {
        if (isset($data['billing_phone'])) {
            $normalized_phone = normalize_phone_number($data['billing_phone']);
            $order->set_billing_phone($normalized_phone);
        }
        if (isset($data['shipping_phone'])) {
            $normalized_phone = normalize_phone_number($data['shipping_phone']);
            $order->set_shipping_phone($normalized_phone);
        }
    }

    /**
     * Validate full address (billing/shipping)
     * Ensures it is long enough and contains valid characters.
     */
    private function validate_address($address) {
        // Address must be at least 5 characters long and contain letters or numbers
        return (strlen($address) >= 10 && preg_match('/[a-zA-Z0-9]/', $address));
    }

    /**
     * Validate first and last name to prevent fake orders
     * Allows letters, spaces, and dots.
     */
    private function validate_name($name) {
        return (strlen($name) >= 3 && preg_match('/^[a-zA-Z\s.]+$/', $name));
    }
}