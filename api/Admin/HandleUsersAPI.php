<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

class HandleUsersAPI extends WP_REST_Controller {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route(__API_NAMESPACE, 'users/shop-managers', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_all_shop_managers'],
                'permission_callback' => api_permission_check(),
            ]
        ]);

        register_rest_route(__API_NAMESPACE, 'users/delivery-stats', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_customer_delivery_stats'],
                'permission_callback' => api_permission_check(),
            ]
        ]);
    }

    /**
     * Get all shop managers
     */
    public function get_all_shop_managers(WP_REST_Request $request) {
        $users = get_users(['role' => 'shop_manager']);

        if (empty($users)) {
            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'No shop managers found.',
                'data'    => [],
            ], 200);
        }

        $shop_managers = [];
        foreach ($users as $user) {
            $shop_managers[] = [
                'id'       => $user->ID,
                'name'     => $user->display_name,
                'email'    => $user->user_email,
                'username' => $user->user_login,
            ];
        }

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Shop managers retrieved successfully.',
            'data'    => $shop_managers,
        ], 200);
    }

    /**
     * Get customer delivery statistics by phone number
     */
    public function get_customer_delivery_stats(WP_REST_Request $request) {
        $phone = $request->get_param('phone');
        
        if (empty($phone)) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Phone number is required.',
            ], 400);
        }

        // Normalize phone number
        $normalized_phone = normalize_phone_number($phone);
        
        // Get all orders for this phone number
        $args = [
            'billing_phone' => $normalized_phone,
            'status' => 'any',
            'return' => 'objects',
            'type' => 'shop_order',
            'limit' => -1
        ];
        
        $orders = wc_get_orders($args);
        
        if (empty($orders)) {
            return new WP_REST_Response([
                'status' => 'success',
                'data' => [
                    'total_orders' => 0,
                    'confirmed_orders' => 0,
                    'canceled_orders' => 0,
                    'success_rate' => 0,
                    'phone' => $normalized_phone
                ]
            ], 200);
        }

        $total_orders = count($orders);
        $confirmed_orders = 0;
        $canceled_orders = 0;
        
        // Define success statuses (completed, processing, etc.)
        $success_statuses = ['completed', 'processing'];
        // Define cancel statuses
        $cancel_statuses = ['cancelled', 'refunded', 'failed'];
        
        foreach ($orders as $order) {
            $status = $order->get_status();
            
            if (in_array($status, $success_statuses)) {
                $confirmed_orders++;
            } elseif (in_array($status, $cancel_statuses)) {
                $canceled_orders++;
            }
        }
        
        // Calculate success rate
        $success_rate = $total_orders > 0 ? round(($confirmed_orders / $total_orders) * 100, 1) : 0;
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => [
                'total_orders' => $total_orders,
                'confirmed_orders' => $confirmed_orders,
                'canceled_orders' => $canceled_orders,
                'success_rate' => $success_rate,
                'phone' => $normalized_phone
            ]
        ], 200);
    }
}