<?php
namespace WooEasyLife\API;

use WooEasyLife\API\Admin\AbandonedOrderAPI;
use WooEasyLife\API\Admin\NewOrderNotificationAPI;
use WooEasyLife\API\Admin\LicenseStatusAPI;

class API_Register {
    public function __construct() {
        // Prevent caching of WooEasyLife API endpoints
        add_filter('rest_pre_serve_request', [$this, 'prevent_api_caching'], 10, 4);
        add_action('rest_api_init', [$this, 'disable_cache_for_apis']);
        
        /**
         * API Path
         * /wp-json/wooeasylife/v1/orders
         * method: get
         */
        new \WooEasyLife\API\Admin\OrderListAPI();

        /**
         * API Path
         * /wp-json/wooeasylife/v1/payment-methods
         * method: get
         */
        new \WooEasyLife\API\Admin\PaymentMethodsAPI();

        /**
         * API Path
         * /wp-json/wooeasylife/v1/shipping-methods
         * method: get
         */
        new \WooEasyLife\API\Admin\ShippingMethodsAPI();

        /**
         * API Path
         * /wp-json/wooeasylife/v1/validate-coupon
         * method: get
         */
        new \WooEasyLife\API\Admin\ValidateCouponAPI();

        /**
         * API Path
         * /wp-json/wooeasylife/v1/update-address/{order_id}
         * method: post
         * payload:
         * {
         *       "billing": {
         *           "address_1": "123 Main St",
         *           "city": "New York",
         *           "state": "NY",
         *           "postcode": "10001",
         *           "country": "US",
         *           "email": "customer@example.com",
         *           "phone": "555-1234"
         *       },
         *       "shipping": {
         *           "address_1": "123 Main St",
         *           "city": "New York",
         *           "state": "NY",
         *           "postcode": "10001",
         *           "country": "US"
         *       }
         *   }
         */
        new \WooEasyLife\API\Admin\UpdateAddressAPI();

        /**
         * customer ip, phone block, list, and edit
         */
        // new WooEasyLife\Admin\BlockFakeCustomer();


        new \WooEasyLife\API\Frontend\OTPHandlerAPI();
        new \WooEasyLife\API\Admin\OrderStatisticsAPI();
        new \WooEasyLife\API\Admin\CustomOrderStatusAPI();
        new \WooEasyLife\API\Admin\WPOptionAPI();
        new \WooEasyLife\API\Admin\SMSConfigAPI();
        new \WooEasyLife\API\Admin\BlockListAPI();
        new \WooEasyLife\API\Admin\SMSHistoryAPI();
        new \WooEasyLife\API\Admin\CustomOrderHandleAPI();
        new \WooEasyLife\API\Admin\CourierHandleAPI();
        new \WooEasyLife\API\Admin\NewOrderNotificationAPI();
        new \WooEasyLife\API\Admin\AbandonedOrderAPI();
        new \WooEasyLife\API\Admin\HandleUsersAPI();
        new \WooEasyLife\API\Admin\LicenseStatusAPI();
        new \WooEasyLife\API\Admin\ProductAPI();
    }

    /**
     * Prevent caching of WooEasyLife API responses
     * This ensures cache plugins don't cache our API responses
     */
    public function prevent_api_caching($served, $result, $request, $server) {
        // Only apply to our API namespace
        $route = $request->get_route();
        if (strpos($route, '/' . __API_NAMESPACE) === 0) {
            // Send no-cache headers
            if (!headers_sent()) {
                // Prevent browser caching
                @header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                @header('Cache-Control: post-check=0, pre-check=0', false);
                @header('Pragma: no-cache');
                @header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
                
                // Prevent proxy/CDN caching
                @header('Surrogate-Control: no-store');
                
                // Add timestamp to prevent caching
                @header('X-WEL-Timestamp: ' . time());
            }
        }
        
        return $served;
    }

    /**
     * Disable cache for WooEasyLife APIs at REST API init
     * Prevents caching by all major WordPress cache plugins
     */
    public function disable_cache_for_apis() {
        // Additional cache prevention for popular cache plugins
        if (!headers_sent()) {
            // Check if this is a WooEasyLife API request
            if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/' . __API_NAMESPACE) !== false) {
                // Prevent WP Rocket caching
                if (!defined('DONOTROCKETOPTIMIZE')) {
                    define('DONOTROCKETOPTIMIZE', true);
                }
                
                // Prevent WP Super Cache
                if (!defined('DONOTCACHEPAGE')) {
                    define('DONOTCACHEPAGE', true);
                }
                
                // Prevent W3 Total Cache
                if (!defined('DONOTCACHCEOBJECT')) {
                    define('DONOTCACHCEOBJECT', true);
                }
                if (!defined('DONOTMINIFY')) {
                    define('DONOTMINIFY', true);
                }
                
                // Prevent LiteSpeed Cache
                if (!defined('LSCACHE_NO_CACHE')) {
                    define('LSCACHE_NO_CACHE', true);
                }
                
                // Prevent WP Fastest Cache
                if (!defined('WPFC_DONOTCACHEPAGE')) {
                    define('WPFC_DONOTCACHEPAGE', true);
                }
                
                // Prevent Swift Performance
                if (!defined('SWIFT_PERFORMANCE_DISABLE_CACHING')) {
                    define('SWIFT_PERFORMANCE_DISABLE_CACHING', true);
                }
                
                // Prevent Breeze Cache
                if (!defined('BREEZE_DISABLE_CACHE')) {
                    define('BREEZE_DISABLE_CACHE', true);
                }
                
                // Prevent FlyingPress
                if (!defined('FLYING_PRESS_CACHE_DISABLED')) {
                    define('FLYING_PRESS_CACHE_DISABLED', true);
                }
                
                // Prevent NitroPack
                if (!defined('NITROPACK_CACHE_SKIP')) {
                    define('NITROPACK_CACHE_SKIP', true);
                }
                
                // Prevent SiteGround Optimizer
                if (!defined('SG_OPTIMIZER_DISABLE_CACHE')) {
                    define('SG_OPTIMIZER_DISABLE_CACHE', true);
                }
                
                // Comprehensive no-cache headers
                @header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
                @header('Pragma: no-cache');
                @header('Expires: 0');
                
                // Cloudflare specific headers
                @header('CF-Cache-Status: BYPASS');
                @header('CDN-Cache-Control: no-cache');
                
                // Redis Object Cache - no constant needed, respects headers
                // Just ensure no-cache headers are sent
            }
        }
    }
}
