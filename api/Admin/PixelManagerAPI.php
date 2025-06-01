<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

class PixelManagerAPI extends WP_REST_Controller
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register the REST API routes for Facebook Pixel settings.
     */
    public function register_routes()
    {
        $namespace = defined('__API_NAMESPACE') ? __API_NAMESPACE : 'wooeasylife/v1';

        register_rest_route($namespace, '/pixel-settings', [
            [
                'methods'             => ['GET', 'POST'],
                'callback'            => [$this, 'pixel_settings_endpoint'],
                'permission_callback' => api_permission_check()
            ]
        ]);

        register_rest_route($namespace, '/send-capi-event', [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'send_capi_event'],
                'permission_callback' => api_permission_check()
            ]
        ]);
    }

    /**
     * Get or update Facebook Pixel settings.
     */
    public function pixel_settings_endpoint(WP_REST_Request $request)
    {
        if ($request->get_method() === 'POST') {
            $params = $request->get_json_params();
            update_option('woo_easy_life_fb_pixel_id', sanitize_text_field($params['pixel_id'] ?? ''));
            update_option('woo_easy_life_fb_capi_token', sanitize_text_field($params['capi_token'] ?? ''));
            update_option('woo_easy_life_fb_pixel_server', !empty($params['server_side']) ? 1 : 0);

            return new WP_REST_Response(['success' => true], 200);
        }

        // GET
        return new WP_REST_Response([
            'pixel_id'    => get_option('woo_easy_life_fb_pixel_id', ''),
            'capi_token'  => get_option('woo_easy_life_fb_capi_token', ''),
            'server_side' => !!get_option('woo_easy_life_fb_pixel_server', 0)
        ], 200);
    }

    /**
     * Handle server-side event sending to Facebook Conversion API.
     */
    public function send_capi_event(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        $event_name = $params['event_name'] ?? '';
        $event_data = $params['event_data'] ?? [];

        $result = $this->send_facebook_capi_event($event_name, $event_data);
        return new WP_REST_Response($result, 200);
    }

    /**
     * Send data to Facebook Conversion API.
     */
    public function send_facebook_capi_event($event_name, $event_data = [])
    {
        $pixel_id = get_option('woo_easy_life_fb_pixel_id');
        $access_token = get_option('woo_easy_life_fb_capi_token');
        if (!$pixel_id || !$access_token) {
            return ['sent' => false, 'reason' => 'not_configured'];
        }

        $api_url = "https://graph.facebook.com/v18.0/{$pixel_id}/events?access_token={$access_token}";

        $payload = [
            'data' => [
                array_merge([
                    'event_name'        => $event_name,
                    'event_time'        => time(),
                    'action_source'     => 'website',
                    'event_source_url'  => home_url($_SERVER['REQUEST_URI'])
                ], $event_data)
            ]
        ];

        $response = wp_remote_post($api_url, [
            'body'    => wp_json_encode($payload),
            'headers' => ['Content-Type' => 'application/json']
        ]);

        return is_wp_error($response)
            ? ['sent' => false, 'reason' => $response->get_error_message()]
            : ['sent' => true, 'response' => wp_remote_retrieve_body($response)];
    }
}