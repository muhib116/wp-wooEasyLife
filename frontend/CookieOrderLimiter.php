<?php
namespace WooEasyLife\Frontend;

class CookieOrderLimiter {

    /** @var string The name of the cookie used for tracking orders. */
    private const COOKIE_NAME = 'wel_daily_order_tracker';

    public function __construct() {
        // Check the limit before processing the order. Priority 20 to run after other checks.
        add_action('woocommerce_after_checkout_validation', [$this, 'check_order_limit_from_cookie'], 20, 2);
        
        // Set/update the cookie after a successful order.
        add_action('woocommerce_thankyou', [$this, 'update_order_limit_cookie']);
    }

    /**
     * Checks the cookie for recent order timestamps and throws an error if the limit is exceeded.
     *
     * @param array     $data   An array of posted data.
     * @param \WP_Error $errors Validation errors object.
     */
    public function check_order_limit_from_cookie($data, $errors) {
        global $config_data;

        // Get the daily limit from config.
        $order_limit = isset($config_data["daily_order_place_limit_per_customer"]) ? intval($config_data["daily_order_place_limit_per_customer"]) : 0;
        
        // If the limit is 0 or not set, this feature is disabled.
        if ($order_limit <= 0) {
            return;
        }

        if (!isset($_COOKIE[self::COOKIE_NAME])) {
            return; // No cookie, so no client-side orders to check.
        }

        $cookie_data = json_decode(sanitize_text_field(wp_unslash($_COOKIE[self::COOKIE_NAME])), true);

        if (!is_array($cookie_data) || !isset($cookie_data['timestamps'])) {
            return; // Corrupted or invalid cookie format.
        }

        $timestamps = $cookie_data['timestamps'];
        $time_window_seconds = 24 * HOUR_IN_SECONDS;

        // Filter out timestamps that are older than our 24-hour window.
        $current_time = time();
        $recent_timestamps = array_filter($timestamps, function($timestamp) use ($current_time, $time_window_seconds) {
            return ($current_time - intval($timestamp)) < $time_window_seconds;
        });
        
        $order_count = count($recent_timestamps);

        if ($order_count >= $order_limit) {
            throw new \Exception(sprintf(
                __('আপনি আপনার দৈনিক অর্ডারের (%d) সীমা অতিক্রম করেছেন। অনুগ্রহ করে ২৪ ঘণ্টা পর আবার চেষ্টা করুন।', 'woo-easy-life'),
                $order_limit
            ));
        }
    }

    /**
     * Sets or updates the order tracking cookie after a successful purchase.
     * The cookie's expiration is now dynamically tied to the configured limit.
     *
     * @param int $order_id The ID of the successful order.
     */
    public function update_order_limit_cookie($order_id) {
        global $config_data;

        // Get the daily limit from config to decide if we need to set a cookie.
        $order_limit = isset($config_data["daily_order_place_limit_per_customer"]) ? intval($config_data["daily_order_place_limit_per_customer"]) : 0;

        // If the limit is disabled (0), we should ensure any existing cookie is cleared.
        if ($order_limit <= 0) {
            if (isset($_COOKIE[self::COOKIE_NAME])) {
                // Unset the cookie by setting its expiration to the past.
                setcookie(self::COOKIE_NAME, '', time() - 3600, '/');
            }
            return;
        }

        // Get existing timestamps from the cookie.
        $timestamps = [];
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            $cookie_data = json_decode(sanitize_text_field(wp_unslash($_COOKIE[self::COOKIE_NAME])), true);
            if (is_array($cookie_data) && isset($cookie_data['timestamps'])) {
                $timestamps = $cookie_data['timestamps'];
            }
        }
        
        $time_window_seconds = 24 * HOUR_IN_SECONDS;
        $current_time = time();

        // Filter out old timestamps to keep the cookie clean.
        $recent_timestamps = array_filter($timestamps, function($timestamp) use ($current_time, $time_window_seconds) {
            return ($current_time - intval($timestamp)) < $time_window_seconds;
        });

        // Add the timestamp of the new order.
        $recent_timestamps[] = $current_time;

        // The cookie will now store the timestamps and the limit it was set with.
        $new_cookie_data = [
            'limit'      => $order_limit,
            'timestamps' => array_values($recent_timestamps), // Re-index the array
        ];
        
        // The cookie will expire in 24 hours from now.
        $cookie_expiry = $current_time + $time_window_seconds;

        setcookie(
            self::COOKIE_NAME,
            json_encode($new_cookie_data),
            [
                'expires'  => $cookie_expiry,
                'path'     => '/',
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }
}