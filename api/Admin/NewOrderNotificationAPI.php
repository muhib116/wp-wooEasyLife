<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class NewOrderNotificationAPI extends WP_REST_Controller
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
        add_action('woocommerce_new_order', function ($order_id) {
            set_transient('new_order_notification', true, 60 * 5); // Notification active for 5 minutes
        });
    }

    /**
     * Register REST API routes.
     */
    public function register_routes()
    {
        register_rest_route(
            __API_NAMESPACE,
            '/check-new-orders-for-notification',
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'check_new_orders'],
                'permission_callback' => api_permission_check(),
            ]
        );
    }

    /**
     * Check for new orders placed after the last check.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function check_new_orders(WP_REST_Request $request)
    {
        global $wpdb;
    
        $has_new_notification = get_transient('new_order_notification') ? true : false;
        clear_new_order_notification();
        // If no new orders are found
        return new WP_REST_Response([
            'status' => 'success',
            'data'   => [
                'has_new_orders' => $has_new_notification
            ],
        ], 200);
    }
    
}

// Clear notification after check
function clear_new_order_notification() {
    delete_transient('new_order_notification');
}