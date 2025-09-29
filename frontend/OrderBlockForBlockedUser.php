<?php
namespace WooEasyLife\Frontend;

class OrderBlockForBlockedUser {
    public function __construct()
    {
        add_action( 'woocommerce_after_checkout_validation', [$this, 'phone_number_block'] );
    }

    public function phone_number_block() {
        global $config_data;
        
        if (!is_wel_license_valid()) {
            return; // Exit the *current* function if the license is not valid.
        }  

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
            $error_message = sprintf(
            __('আপনার ফোন নম্বর <strong style="font-weight:bold;color: #508ef5;">%s</strong> ব্লক করা হয়েছে এবং এটি ব্যবহার করে আপনি অর্ডার করতে পারবেন না। সহায়তার জন্য অনুগ্রহ করে আমাদের সাপোর্ট টিমের সাথে যোগাযোগ করুন।', 'your-text-domain'),
                esc_html($normalized_phone)
            );

            if ($admin_phone) {
                $error_message .= ' যোগাযোগের জন্য কল করুন: <a href="tel:'.$admin_phone.'">'.$admin_phone.'</a>.';
            }

            throw new \Exception($error_message);
        }
    }

    private function check_email_block($billing_email){
        $email_block_listed = get_block_data_by_type($billing_email, 'email');

        if($email_block_listed){
            $error_message = sprintf(
                __('আপনার ইমেইল এড্ড্রেস <strong style="font-weight:bold;color: #508ef5;">%s</strong> ব্লক করা হয়েছে এবং এটি ব্যবহার করে আপনি অর্ডার করতে পারবেন না। সহায়তার জন্য অনুগ্রহ করে আমাদের সাপোর্ট টিমের সাথে যোগাযোগ করুন।', 'your-text-domain'),
                esc_html($billing_email)
            );

            if ($admin_phone) {
                $error_message .= ' যোগাযোগের জন্য কল করুন: <a href="tel:'.$admin_phone.'">'.$admin_phone.'</a>.';
            }

            throw new \Exception($error_message);
        }
    }
    
    private function check_ip_block(){
        global $config_data;
        $admin_phone = $config_data['admin_phone'] ?? '';
        // Get the customer's IP address
        $customer_ip = get_customer_ip();
        
        // Check if the IP is block-listed
        $ip_block_listed = get_block_data_by_type($customer_ip, 'ip');

        if($ip_block_listed){
            $error_message = sprintf(
            __('আপনার আইপি এড্রেস <strong style="font-weight:bold;color: #508ef5;">%s</strong> ব্লক করা হয়েছে এবং এটি ব্যবহার করে আপনি অর্ডার করতে পারবেন না। সহায়তার জন্য অনুগ্রহ করে আমাদের সাপোর্ট টিমের সাথে যোগাযোগ করুন।', 'your-text-domain'),
                esc_html($customer_ip)
            );

            if ($admin_phone) {
                $error_message .= ' যোগাযোগের জন্য কল করুন: <a href="tel:'.$admin_phone.'">'.$admin_phone.'</a>.';
            }

            throw new \Exception($error_message);

        }
    }
}