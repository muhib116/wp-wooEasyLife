<?php
namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

class LicenseStatusAPI extends WP_REST_Controller {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes for updating license status.
     */
    public function register_routes() {
        register_rest_route(__API_NAMESPACE, '/license-status', [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'update_license_status'],
                'permission_callback' => api_permission_check(),
            ]
        ]);
    }

    /**
     * Callback to update the license status in wp_options.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_license_status(WP_REST_Request $request) {
        $status = sanitize_text_field($request->get_param('status'));

        // We only accept specific statuses for security.
        $allowed_statuses = ['valid', 'expired', 'invalid', 'unauthenticated'];
        if (!$status || !in_array($status, $allowed_statuses, true)) {
            return new \WP_Error('invalid_status', 'Invalid status provided.', ['status' => 400]);
        }

        update_option('woo_easy_life_license_status', $status);

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'License status updated successfully.',
            'data'    => ['license_status' => $status],
        ], 200);
    }
}