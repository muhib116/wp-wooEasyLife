<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class ValidateCouponAPI
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
        register_rest_route(__API_NAMESPACE, '/validate-coupon', [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'validate_coupon'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_validate_coupon_args(),
            ],
        ]);
    }

    /**
     * Validate WooCommerce Coupon
     *
     * @param WP_REST_Request $request The API request object.
     * @return WP_REST_Response The API response.
     */
    public function validate_coupon(WP_REST_Request $request)
    {
        try {
            $coupon_code = sanitize_text_field($request->get_param('coupon_code'));

            if (empty($coupon_code)) {
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'Coupon code cannot be empty.',
                ], 400);
            }

            // Load the WooCommerce coupon object
            $coupon = new \WC_Coupon($coupon_code);

            // Check if the coupon exists
            if (!$coupon->get_id()) {
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'Invalid coupon code.',
                ], 400);
            }

            // Check if the coupon is enabled
            $coupon_status = get_post_status($coupon->get_id());
            if (!$coupon_status || $coupon_status !== 'publish') {
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'This coupon is not active.',
                ], 400);
            }

            // Check for usage limit
            $usage_limit = $coupon->get_usage_limit();
            $usage_count = $coupon->get_usage_count();
            if ($usage_limit && $usage_count >= $usage_limit) {
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'This coupon has reached its usage limit.',
                ], 400);
            }

            // Check expiration date
            $expiry_date = $coupon->get_date_expires();
            if ($expiry_date && $expiry_date->getTimestamp() < time()) {
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'This coupon has expired.',
                ], 400);
            }

            // Check if coupon is restricted to specific products or categories
            $restricted_products = $coupon->get_product_ids();
            $restricted_categories = $coupon->get_product_categories();

            if (!empty($restricted_products) || !empty($restricted_categories)) {
                return new WP_REST_Response([
                    'status'  => 'warning',
                    'message' => 'This coupon is restricted to specific products or categories.',
                    'data'    => [
                        'restricted_products'  => $restricted_products,
                        'restricted_categories' => $restricted_categories,
                    ],
                ], 200);
            }

            // All validations passed
            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'The coupon is valid and can be applied.',
                'data'    => [
                    'coupon_code'   => $coupon_code,
                    'calc_discounts_sequentially' => $this->is_sequential_discounts_enabled(),
                    'discount_type' => $coupon->get_discount_type(),
                    'amount'        => $coupon->get_amount(),
                    'usage_limit'   => $usage_limit,
                    'usage_count'   => $usage_count,
                    'expiry_date'   => $expiry_date ? $expiry_date->date('Y-m-d H:i:s') : null,
                ],
            ], 200);

        } catch (\Exception $e) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'An unexpected error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function is_sequential_discounts_enabled() {
        $enabled = get_option('woocommerce_calc_discounts_sequentially', 'no');
        return $enabled === 'yes';
    }

    /**
     * Define API arguments for coupon validation
     */
    private function get_validate_coupon_args()
    {
        return [
            'coupon_code' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'The coupon code to validate.',
            ],
        ];
    }
}