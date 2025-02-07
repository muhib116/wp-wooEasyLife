<?php
namespace WooEasyLife\Frontend;

class OrderBlockForBlockedUser {
    public function __construct()
    {
        add_action( 'woocommerce_after_checkout_validation', [$this, 'phone_number_block'] );
    }

    public function phone_number_block() {
        global $config_data;

        // Retrieve data sent via AJAX
        $billing_phone = isset( $_POST['billing_phone'] ) ? sanitize_text_field( $_POST['billing_phone'] ) : '';
        $billing_email = isset( $_POST['billing_email'] ) ? sanitize_text_field( $_POST['billing_email'] ) : '';
    
        // Check if phone number blocking is enabled in the config
        if (!empty($config_data["phone_number_block"])) {
            $this->check_phone_number_block($billing_phone);
        }

        // Check if email blocking is enabled in the config
        if (empty($config_data["email_block"])) {
            $this->check_email_block($billing_email);
        }

        if($config_data["ip_block"]){
            $this->check_ip_block();
        }
    }

    private function check_phone_number_block($billing_phone){
        // Normalize the phone number
        $normalized_phone = normalize_phone_number($billing_phone);

        // Check if the phone number is blocked
        $phone_block_listed = get_block_data_by_type($normalized_phone, 'phone_number');

        if($phone_block_listed){
            throw new \Exception(
                sprintf(
                    __('Your phone number <strong style=\"font-weight:bold;color: #508ef5;\">%s</strong> is restricted and cannot be used to place an order. Please contact our support team for assistance.', 'your-text-domain'),
                    esc_html($normalized_phone)
                )
            );
        }
    }

    private function check_email_block($billing_email){
        $email_block_listed = get_block_data_by_type($billing_email, 'email');

        if($email_block_listed){
            throw new \Exception(
                sprintf(
                    __('Your email address <strong style=\"font-weight:bold;color: #508ef5;\">%s</strong> is restricted and cannot be used to place an order. Please contact our support team for assistance.', 'your-text-domain'),
                    esc_html($billing_email)
                )
            );
        }
    }
    
    private function check_ip_block(){
        // Get the customer's IP address
        $customer_ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    
        // Check if the IP is block-listed
        $ip_block_listed = get_block_data_by_type($customer_ip, 'ip');

        if($ip_block_listed){
            throw new \Exception(
                sprintf(
                    __('Your IP address <strong style=\"font-weight:bold;color: #508ef5;\">%s</strong> is restricted and cannot be used to place an order. Please contact our support team for assistance.', 'your-text-domain'),
                    esc_html($customer_ip)
                )
            );
        }
    }
}