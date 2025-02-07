<?php
namespace WooEasyLife\Init;

class InitClass {
    public function create_static_statuses()
    {
        // Define static statuses
        $static_statuses = [
            'unknown' => [
                'title'       => 'Unknown',
                'slug'        => 'unknown',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#444444',
                'description' => 'Order is being prepared.',
            ],
            'processing' => [
                'title'       => 'Processing',
                'slug'        => 'processing',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#FFA500',
                'description' => 'Order is being prepared.',
            ],
            'follow-up' => [
                'title'       => 'Follow Up',
                'slug'        => 'follow-up',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#e61976',
                'description' => 'Follow-up action is required.',
            ],
            'confirmed' => [
                'title'       => 'Confirmed',
                'slug'        => 'confirmed',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#28A745',
                'description' => 'Order details have been confirmed.',
            ],
            'call-not-received' => [
                'title'       => 'Call Not Received',
                'slug'        => 'call-not-received',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#6C757D',
                'description' => 'Customer call not received.',
            ],
            'canceled' => [
                'title'       => 'Canceled',
                'slug'        => 'canceled',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#DC3545',
                'description' => 'Order has been canceled.',
            ],
            'fake' => [
                'title'       => 'Fake Order',
                'slug'        => 'fake',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#8B0000',
                'description' => 'Order is marked as fraudulent.',
            ],
            'courier-entry' => [
                'title'       => 'Courier Entry',
                'slug'        => 'courier-entry',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#6F42C1',
                'description' => 'Order entered into courier system.',
            ],
            'courier-hand-over' => [
                'title'       => 'Courier Hand Over',
                'slug'        => 'courier-hand-over',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#0056B3',
                'description' => 'Order handed over to courier.',
            ],
            'out-for-delivery' => [
                'title'       => 'Out for Delivery',
                'slug'        => 'out-for-delivery',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#8bc005',
                'description' => 'Courier is delivering the order.',
            ],
            'delivered' => [
                'title'       => 'Delivered',
                'slug'        => 'delivered',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#18ac2e',
                'description' => 'Order delivered successfully.',
            ],
            'payment-received' => [
                'title'       => 'Payment Received',
                'slug'        => 'payment-received',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#FFD700',
                'description' => 'Payment received for the order.',
            ],
            'pending-payment' => [
                'title'       => 'Pending Payment',
                'slug'        => 'pending-payment',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#FFC107',
                'description' => 'Awaiting payment confirmation.',
            ],
            'returned' => [
                'title'       => 'Returned',
                'slug'        => 'returned',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#FF6961',
                'description' => 'Order returned by the customer.',
            ],
            'refunded' => [
                'title'       => 'Refunded',
                'slug'        => 'refunded',
                'is_default'  => true,
                'not_using'   => false,
                'color'       => '#0e1011',
                'description' => 'Payment refunded to the customer.',
            ],
        ];

        // Save the updated statuses
        if (empty(get_option(__PREFIX.'custom_order_statuses'))) {
            update_option(__PREFIX.'custom_order_statuses', $static_statuses);
        }
    }

    public function save_default_config()
    {
        $site_title = get_bloginfo('name');
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');

        $config = [
            "admin_phone" => '',
            "invoice_company_name" => $site_title,
            "invoice_logo" => $logo_url,
            "invoice_email" => '',
            "invoice_phone" => '',
            "invoice_print" => false,
            "clear_data_when_deactivate_plugin" => false,
            "ip_block" => true,
            "phone_number_block" => true,
            "email_block" => true,
            "place_order_otp_verification" => false,
            "daily_order_place_limit_per_customer" => 3,
            "only_bangladeshi_ip" => false,
            "courier_automation" => false,
            "fraud_customer_checker" => false,
        ];

        // Save the updated config
        if (empty(get_option(__PREFIX.'config'))) {
            update_option(__PREFIX.'config', $config);
        }
    }
}