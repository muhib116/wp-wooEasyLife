<?php

namespace WooEasyLife\API\Admin;

class ShippingMethodsAPI
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
        register_rest_route(__API_NAMESPACE, '/shipping-methods', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_shipping_methods_with_charges'],
            'permission_callback' => api_permission_check(), // Publicly accessible
        ]);
    }

    /**
     * Get available shipping methods
     */
    public function get_shipping_methods_with_charges()
    {
        $zones = \WC_Shipping_Zones::get_zones();
        $methods = [];

        foreach ($zones as $zone) {
            $zone_obj = new \WC_Shipping_Zone($zone['id']);
            $shipping_methods = $zone_obj->get_shipping_methods();

            foreach ($shipping_methods as $method) {
                if ($method->is_enabled()) {
                    $shipping_cost = isset($method->instance_settings['cost']) ? $method->instance_settings['cost'] : 0;

                    $methods[] = [
                        'zone_name'     => $zone_obj->get_zone_name(),
                        'method_id'     => $method->id,
                        'method_title'  => $method->get_title(),
                        'settings'      => $method->settings,
                        'shipping_cost' => $shipping_cost, // Format the cost
                    ];
                }
            }
        }

        // Include "Locations Not Covered by Your Other Zones"
        $default_zone = new \WC_Shipping_Zone(0);
        $default_methods = $default_zone->get_shipping_methods();
        foreach ($default_methods as $method) {
            if ($method->is_enabled()) {
                $shipping_cost = isset($method->instance_settings['cost']) ? $method->instance_settings['cost'] : 0;

                $methods[] = [
                    'zone_name'     => 'Default Zone',
                    'method_id'     => $method->id,
                    'method_title'  => $method->get_title(),
                    'settings'      => $method->settings,
                    'shipping_cost' => wc_price($shipping_cost), // Format the cost
                ];
            }
        }

        return new \WP_REST_Response($methods, 200);
    }
}
