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
            $last_order_id = $this->validate_same_product_repeat_order($billing_phone, $billing_email);
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
            throw new \Exception(__('Your billing address is not valid. Please enter a correct address (in detail).', 'your-text-domain'));
            $errors->add( 'validation', 'Your billing address is not valid. Please enter a correct address (in detail).' );
            return;
        }
    
        // Validate shipping address (if provided)
        if (!empty($shipping_address_1) && !$this->validate_address($shipping_address)) {
            throw new \Exception(__('Your shipping address is not valid. Please enter a correct shipping address (in detail).', 'your-text-domain'));
            $errors->add( 'validation', 'Your shipping address is not valid. Please enter a correct shipping address (in detail).' );
            return;
        }
    
        // Validate first name and last name
        if (!$this->validate_name($name)) {
            throw new \Exception(__('Your name is invalid. Please enter a valid name.', 'your-text-domain'));
            $errors->add( 'validation', 'Your name is invalid. Please enter a valid name.');
            return;
        }
    
        // Validate billing phone number
        if (!validate_BD_phoneNumber(normalize_phone_number($billing_phone))) { // Fixed the logic
            throw new \Exception(__('Your billing phone number is invalid. Please enter a valid number.', 'your-text-domain'));
            $errors->add( 'validation', 'Your billing phone number is invalid. Please enter a valid number.');
            return;
        }
    
        // Validate shipping phone number (if provided)
        if (!empty($shipping_phone) && !validate_BD_phoneNumber(normalize_phone_number($shipping_phone))) { // Fixed the logic
            throw new \Exception(__('Your shipping phone number is invalid. Please enter a valid number.', 'your-text-domain'));
            $errors->add( 'validation', 'Your shipping phone number is invalid. Please enter a valid number.');
            return;
        }
    }
    
    public function validate_same_product_repeat_order($billing_phone=null, $billing_email=null){
        $args = [
            'limit'       => 1, // Get the most recent order
            'orderby'     => 'date',
            'order'       => 'DESC',
            'status'      => ['wc-processing', 'wc-confirmed', 'wc-on-hold', 'wc-pending'], // Only check orders in progress
            'type'        => 'shop_order' // Ensure only WooCommerce orders are retrieved
        ];
        
        if($billing_phone) {
            $args['billing_phone'] = $billing_phone;
        } else if($billing_email) {
            $args['billing_email'] = $billing_email;
        }

        $orders = wc_get_orders($args);
        $last_order_id = !empty($orders) ? $orders[0]->get_id() : '';

        return $last_order_id;
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