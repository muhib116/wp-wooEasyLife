<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class UpdateAddressAPI extends WP_REST_Controller
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register the REST API routes
     */
    public function register_routes()
    {
        register_rest_route(__API_NAMESPACE, '/update-address/(?P<id>\d+)', [
            'methods'             => 'POST',
            'callback'            => [$this, 'update_address'],
            'permission_callback' => api_permission_check(), // Allow public access for testing
            'args'                => $this->get_endpoint_args_for_item_schema(),
        ]);
    }

    /**
     * Update billing and shipping address
     */
    public function update_address(WP_REST_Request $request)
    {
        $order_id = $request->get_param('id');

        if (!$order_id) {
            return new WP_Error('no_order_id', 'Order ID is required', ['status' => 400]);
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            return new WP_Error('invalid_order', 'Invalid order ID', ['status' => 404]);
        }

        // Get the addresses from the request
        $billing_address = $request->get_param('billing');
        $shipping_address = $request->get_param('shipping');

        // Update billing address
        if (!empty($billing_address) && is_array($billing_address)) {
            $order->set_billing_first_name($billing_address['first_name'] ?? $order->get_billing_first_name());
            $order->set_billing_last_name($billing_address['last_name'] ?? $order->get_billing_last_name());
            $order->set_billing_company($billing_address['company'] ?? $order->get_billing_company());
            $order->set_billing_address_1($billing_address['address_1'] ?? $order->get_billing_address_1());
            $order->set_billing_address_2($billing_address['address_2'] ?? $order->get_billing_address_2());
            $order->set_billing_city($billing_address['city'] ?? $order->get_billing_city());
            $order->set_billing_state($billing_address['state'] ?? $order->get_billing_state());
            $order->set_billing_postcode($billing_address['postcode'] ?? $order->get_billing_postcode());
            $order->set_billing_country($billing_address['country'] ?? $order->get_billing_country());
            $order->set_billing_email($billing_address['email'] ?? $order->get_billing_email());
            $order->set_billing_phone($billing_address['phone'] ?? $order->get_billing_phone());

            if (!empty($billing_address['transaction_id'])) {
                $order->set_transaction_id($billing_address['transaction_id']);
            }
        }

        // Update shipping address
        if (!empty($shipping_address) && is_array($shipping_address)) {
            $order->set_shipping_first_name($shipping_address['first_name'] ?? $order->get_shipping_first_name());
            $order->set_shipping_last_name($shipping_address['last_name'] ?? $order->get_shipping_last_name());
            $order->set_shipping_company($shipping_address['company'] ?? $order->get_shipping_company());
            $order->set_shipping_address_1($shipping_address['address_1'] ?? $order->get_shipping_address_1());
            $order->set_shipping_address_2($shipping_address['address_2'] ?? $order->get_shipping_address_2());
            $order->set_shipping_city($shipping_address['city'] ?? $order->get_shipping_city());
            $order->set_shipping_state($shipping_address['state'] ?? $order->get_shipping_state());
            $order->set_shipping_postcode($shipping_address['postcode'] ?? $order->get_shipping_postcode());
            $order->set_shipping_country($shipping_address['country'] ?? $order->get_shipping_country());

            if (!empty($shipping_address['customer_note'])) {
                $order->set_customer_note($shipping_address['customer_note']);
            }
        }

        // Update payment method if provided
        if (!empty($request->get_param('payment_method'))) {
            $order->set_payment_method($request->get_param('payment_method'));
        }

        // Save the order
        $order->save();

        return new WP_REST_Response(['message' => 'Order address updated successfully'], 200);
    }
}
