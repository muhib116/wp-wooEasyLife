<?php
namespace WooEasyLife\Frontend;

class IP_block {
    public function __construct()
    {
        // add_action('init', [$this, 'block_non_bangladeshi_users']);
    }

    /**
     * Blocks access for non-Bangladeshi IPs on the public-facing frontend only.
     * It explicitly allows requests to /wp-admin/, AJAX calls, and REST API endpoints.
     */
    public function block_non_bangladeshi_users() {
        // STEP 1: Create a more robust check to allow backend access.
        // We will exit immediately if it's an admin page, an AJAX request, or a REST API call.
        if (is_admin() || wp_doing_ajax() || $this->is_rest_api_request()) {
            return;
        }

        global $config_data;
    
        if ($config_data['only_bangladeshi_ip'] ?? false) 
        {
            $user_ip = get_customer_ip(); 
            
            if (!$user_ip) {
                return;
            }
            
            // Localhost testing override.
            if (in_array($user_ip, ['127.0.0.1', '::1'])) {
                $user_ip = '103.204.210.233'; // Simulate a Bangladeshi IP for local testing.
            }
    
            $cache_key = 'geo_ip_' . md5($user_ip);
            $country_code = get_transient($cache_key);
            
            if (false === $country_code) {
                $api_url = "http://ip-api.com/json/{$user_ip}";
                $response = wp_safe_remote_get($api_url);
    
                if (is_wp_error($response)) {
                    error_log('WooEasyLife GeoIP API Error: ' . $response->get_error_message());
                    return;
                }
    
                $data = json_decode(wp_remote_retrieve_body($response), true);
    
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['countryCode'])) {
                    $country_code = $data['countryCode'];
                    set_transient($cache_key, $country_code, 12 * HOUR_IN_SECONDS);
                } else {
                    error_log('WooEasyLife GeoIP API Invalid Response: ' . wp_remote_retrieve_body($response));
                    return;
                }
            }
    
            // Block access if the country is not Bangladesh.
            if ($country_code !== 'BD') {
                wp_die(
                    __('Access restricted. This site is only accessible from Bangladesh.', 'woo-easy-life'),
                    __('Access Denied', 'woo-easy-life'),
                    array('response' => 403)
                );
            }
        }
    }

    /**
     * Checks if the current request is for the WordPress REST API.
     *
     * @return bool True if it's a REST API request, false otherwise.
     */
    private function is_rest_api_request() {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        // The default REST API prefix is '/wp-json/'.
        $rest_prefix = trailingslashit(rest_get_url_prefix());
        
        // Check if the request URI starts with the REST API prefix.
        return (strpos($_SERVER['REQUEST_URI'], $rest_prefix) !== false);
    }
}