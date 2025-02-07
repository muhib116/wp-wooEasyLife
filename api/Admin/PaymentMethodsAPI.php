<?php

namespace WooEasyLife\API\Admin;

class PaymentMethodsAPI
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes()
    {
        register_rest_route(__API_NAMESPACE, '/payment-methods', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_payment_methods'],
            'permission_callback' => api_permission_check(), // Publicly accessible
        ]);
    }

    /**
     * Get available payment methods
     */
    public function get_payment_methods()
    {
        $payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
        $methods = [];

        foreach ($payment_gateways as $gateway) {
            $methods[] = [
                'id'          => $gateway->id,
                'title'       => $gateway->get_title(),
                'description' => $gateway->get_description(),
            ];
        }

        return rest_ensure_response($methods);
    }
}
