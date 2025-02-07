<?php
namespace WooEasyLife\Frontend;

class IP_block {
    public function __construct()
    {
        add_action('init', [$this, 'block_non_bangladeshi_users']);
    }

    /**
     * this api work in live server properly.
     * other wise you need to change IP manually
     */

    public function block_non_bangladeshi_users() {
        global $config_data;
    
        if ($config_data['only_bangladeshi_ip'] ?? false) 
        {
            // Get the user's IP address
            $user_ip = $_SERVER['REMOTE_ADDR'];
            $user_ip = ($user_ip === '::1') ? '103.204.210.233' : $user_ip; 
            // bd_ip:103.204.210.233, sg_ip:23.106.249.37 (Localhost case)
    
            // Check if we have cached data for this IP
            $cache_key = 'geo_ip_' . md5($user_ip);
            $cached_data = get_transient($cache_key);
            
            if ($cached_data !== false) {
                $country_code = $cached_data;
            } else {
                // Use an IP geolocation API (e.g., ip-api.com)
                $api_url = "http://ip-api.com/json/{$user_ip}";
                $response = wp_safe_remote_get($api_url);
    
                // Ensure the API response is valid
                if (is_wp_error($response)) {
                    error_log('GeoIP API Error: ' . $response->get_error_message());
                    return; // Fail silently
                }
    
                $data = json_decode(wp_remote_retrieve_body($response), true);
    
                // Validate API response
                if (!isset($data['countryCode'])) {
                    error_log('GeoIP API Invalid Response: ' . wp_remote_retrieve_body($response));
                    return; // Fail silently
                }
    
                $country_code = $data['countryCode'];
    
                // Store result in a transient for 12 hours to reduce API calls
                set_transient($cache_key, $country_code, 12 * HOUR_IN_SECONDS);
            }
    
            // Check if the user's country is Bangladesh
            if ($country_code !== 'BD') {
                wp_die(
                    __('Access restricted. This site is only accessible from Bangladesh.', 'woo-easy-life'),
                    __('Access Denied', 'woo-easy-life'),
                    array('response' => 403)
                );
            }
        }
    }
    
}