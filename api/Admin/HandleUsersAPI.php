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
}