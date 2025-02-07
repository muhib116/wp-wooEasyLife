<?php
namespace WooEasyLife\Admin;

use WooEasyLife\Frontend\CustomerHandler;

class StatusChangeAction {
    public function __construct()
    {
        add_action('woocommerce_order_status_changed', [$this, 'handle_order_status_change'], 10, 3);
    }

    public function handle_order_status_change($order_id, $old_status, $new_status) {
        // Get the order object
        $order = wc_get_order($order_id);

        if (!$order) {
            return; // Exit if the order is invalid
        }

        $sms_config_table = new \WooEasyLife\CRUD\SMSConfigTable();
        $sms_records = $sms_config_table->get_all('wc-'.$new_status, 1);

        $this->send_sms($order, $sms_records);
    }

    public function send_sms($order, $sms_records) 
    {
        global $config_data;

        $variables = [
            'site_name' => get_bloginfo('name'), 
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'customer_phone' => $order->get_billing_phone(),
            'customer_email' => $order->get_billing_email(),
            'customer_billing_address' => $order->get_formatted_billing_address(),
            'customer_shipping_address' => $order->get_formatted_shipping_address(),
            'customer_success_rate' => getCustomerSuccessRate($order->get_billing_phone()),
            'product_name' => implode(', ', array_map(function ($item) { return $item->get_name(); }, $order->get_items())),
            'total_amount' => $order->get_total(),
            'delivery_charge' => $order->get_shipping_total(),
            'payment_method' => $order->get_payment_method_title(),
            'product_price' => wc_price($order->get_subtotal()),
            'admin_phone' => $config_data["admin_phone"],
        ];
        
        foreach($sms_records as $key => $value){
            if($value["message_for"] == 'admin')
            {
                if(!empty($value["phone_number"])){
                    $variables["admin_phone"] = $value["phone_number"];
                }
                send_sms($variables["admin_phone"], $this->replace_placeholder_variables_in_message($value["message"], $variables));
            }else {
                send_sms($variables["customer_phone"], $this->replace_placeholder_variables_in_message($value["message"], $variables));
            }
        }
    }

    private function replace_placeholder_variables_in_message($message, $variables) {

        // Match all placeholders starting with '$' in the message
        preg_match_all('/\$(\w+)/', $message, $matches);

        // Replace each placeholder with its corresponding value
        foreach ($matches[1] as $placeholder) {
            // Check if the variable exists in the provided array
            if (array_key_exists($placeholder, $variables)) {
                $message = str_replace('$' . $placeholder, $variables[$placeholder], $message);
            }
        }

        // Remove all remaining HTML tags
        $message = strip_tags($message);
        return $message;
    }
}