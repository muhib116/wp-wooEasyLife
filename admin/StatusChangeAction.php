<?php
namespace WooEasyLife\Admin;

use WooEasyLife\Frontend\CustomerHandler;

class StatusChangeAction {
    public function __construct()
    {
        add_action('woocommerce_order_status_changed', [$this, 'handle_order_status_change'], 10, 3);
    }

    public function handle_order_status_change($order_id, $old_status, $new_status) {
        if($old_status == $new_status) return;

        if (!is_wel_license_valid()) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Prevent duplicate SMS for same status
        $meta_key = '_wel_sms_sent_' . $new_status;
        if (get_post_meta($order_id, $meta_key, true)) {
            return;
        }

        $sms_config_table = new \WooEasyLife\CRUD\SMSConfigTable();
        $sms_records = $sms_config_table->get_all('wc-'.$new_status, 1);

        $this->send_sms($order, $sms_records);

        // Send pixel event for confirmed or cancelled
        if (in_array($new_status, ['confirmed', 'cancelled'])) {
            $this->send_order_event_to_pixel($order, $old_status, $new_status);
        }

        update_post_meta($order_id, $meta_key, 1);
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

    public function send_order_event_to_pixel($order, $old_status, $new_status) {
        // Only fire if status actually changed and is now 'confirmed' or 'cancelled'
        if ($old_status === $new_status || !in_array($new_status, ['confirmed', 'cancelled'])) {
            return;
        }

        global $config_data;

        $pixel_id = isset($config_data["pixel_id"]) ? trim($config_data["pixel_id"]) : '';
        $access_token = isset($config_data["pixel_access_token"]) ? trim($config_data["pixel_access_token"]) : '';

        if (empty($pixel_id) || empty($access_token)) {
            $this->log_facebook_pixel_response('Missing Pixel ID or Access Token.');
            return;
        }

        if (!is_object($order) || !method_exists($order, 'get_id')) {
            $this->log_facebook_pixel_response('Invalid order object passed.');
            return;
        }

        $hash_data = function($value) {
            return hash('sha256', strtolower(trim($value)));
        };

        // Customer info
        $email      = $order->get_billing_email();
        $phone_raw  = preg_replace('/\D/', '', $order->get_billing_phone());
        if ($phone_raw && strpos($phone_raw, '880') !== 0) {
            $phone_raw = '880' . ltrim($phone_raw, '0');
        }
        $first_name = $order->get_billing_first_name();
        $last_name  = $order->get_billing_last_name();
        $city       = $order->get_billing_city();
        $state      = $order->get_billing_state();
        $zip        = $order->get_billing_postcode();
        $country    = $order->get_billing_country() ?: 'BD';
        $address_1  = $order->get_billing_address_1();

        // Get client IP (proxy aware)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $client_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        $user_data = [];
        if (!empty($email))      $user_data['em']      = $hash_data($email);
        if (!empty($phone_raw))  $user_data['ph']      = $hash_data($phone_raw);
        if (!empty($first_name)) $user_data['fn']      = $hash_data($first_name);
        if (!empty($last_name))  $user_data['ln']      = $hash_data($last_name);
        if (!empty($city))       $user_data['ct']      = $hash_data($city);
        if (!empty($state))      $user_data['st']      = $hash_data($state);
        if (!empty($zip))        $user_data['zp']      = $hash_data($zip);
        if (!empty($country))    $user_data['country'] = $hash_data($country);
        if (!empty($address_1))  $user_data['addr']    = $hash_data($address_1);

        // Required fields for CAPI
        $user_data['client_ip_address'] = $client_ip;
        $user_data['client_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $value    = floatval($order->get_total());
        $currency = strtoupper($order->get_currency()) ?: 'BDT';
        $order_id = $order->get_id();

        // Set event name and event_id based on status
        $event_name = $new_status === 'confirmed' ? 'Purchase' : 'OrderCancelled';
        $event_id = strtolower($event_name) . '_' . sanitize_key($order_id);

        $contents = [];
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if ($product && is_object($product)) {
                $contents[] = [
                    'id'       => $product->get_id(),
                    'quantity' => $item->get_quantity(),
                    'item_price' => $product->get_price(),
                    'name'     => $product->get_name(),
                ];
            }
        }

        $event = [
            'event_name'    => $event_name,
            'event_time'    => time(),
            'event_id'      => $event_id,
            'action_source' => 'website',
            'user_data'     => $user_data,
            'custom_data'   => [
                'currency' => $currency,
                'value'    => $value,
                'contents' => $contents,
                'num_items' => count($contents),
            ]
        ];

        $payload = [
            'data' => [$event],
            'access_token' => $access_token
        ];

        if (!empty($config_data['pixel_test_event_code']) && defined('WP_DEBUG') && WP_DEBUG) {
            $payload['test_event_code'] = $config_data['pixel_test_event_code'];
        }

        $url = "https://graph.facebook.com/v18.0/{$pixel_id}/events";

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload),
            'timeout' => 20
        ]);

        // Improved error handling and logging
        if (is_wp_error($response)) {
            error_log('[FB Pixel] Error: ' . $response->get_error_message());
            error_log('[FB Pixel] Payload: ' . json_encode($payload));
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            error_log("[FB Pixel] Status Code: {$code}");
            error_log("[FB Pixel] Response Body: " . $body);
            error_log("[FB Pixel] Payload Sent: " . json_encode($payload));
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

    private function log_facebook_pixel_response($response, $payload = []) {
        if (is_wp_error($response)) {
            error_log('[FB Pixel] Error: ' . $response->get_error_message());
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            error_log("[FB Pixel] Status Code: {$code}");
            error_log("[FB Pixel] Response Body: " . $body);

            if (!empty($payload)) {
                error_log("[FB Pixel] Payload Sent: " . json_encode($payload));
            }
        }
    }

}