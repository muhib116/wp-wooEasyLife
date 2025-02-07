<?php
namespace WooEasyLife\Frontend;

class CheckoutFormValidation {
    public function __construct()
    {
        add_action('woocommerce_after_checkout_validation', [$this, 'form_validation']);
        add_action('woocommerce_checkout_create_order', [$this, 'modify_order_phone'], 10, 2);
    }

    public function form_validation() {
        // Retrieve and sanitize user inputs
        $billing_address_1 = isset($_POST['billing_address_1']) ? sanitize_text_field($_POST['billing_address_1']) : '';
        $billing_address_2 = isset($_POST['billing_address_2']) ? sanitize_text_field($_POST['billing_address_2']) : '';
        $shipping_address_1 = isset($_POST['shipping_address_1']) ? sanitize_text_field($_POST['shipping_address_1']) : '';
        $shipping_address_2 = isset($_POST['shipping_address_2']) ? sanitize_text_field($_POST['shipping_address_2']) : '';
        $first_name = isset($_POST['billing_first_name']) ? sanitize_text_field($_POST['billing_first_name']) : '';
        $last_name = isset($_POST['billing_last_name']) ? sanitize_text_field($_POST['billing_last_name']) : '';

        // Combine billing address 1 and 2
        $billing_address = trim($billing_address_1 . ' ' . $billing_address_2);
        // Combine shipping address 1 and 2 (if provided)
        $shipping_address = trim($shipping_address_1 . ' ' . $shipping_address_2);
        // combine first_name and last_name
        $name = trim($first_name . ' ' . $last_name);

        // Validate billing address
        if (!$this->validate_address($billing_address)) {
            throw new \Exception(__('Your billing address is not valid. Please enter a correct address (in detail).', 'your-text-domain'));
            return;
        }

        // Validate shipping address (if provided)
        if (!empty($shipping_address_1) && !$this->validate_address($shipping_address)) {
            throw new \Exception(__('Your shipping address is not valid. Please enter a correct shipping address (in detail).', 'your-text-domain'));
            return;
        }

        // Validate first name and last name
        if (!$this->validate_name($name)) {
            throw new \Exception(__('Your name is invalid. Please enter a valid name.', 'your-text-domain'));
            return;
        }
    }

    public function modify_order_phone($order, $data) {
        if (isset($data['billing_phone'])) {
            $normalized_phone = normalize_phone_number($data['billing_phone']);
            $order->set_billing_phone($normalized_phone);
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