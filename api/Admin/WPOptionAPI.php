<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class WPOptionAPI extends WP_REST_Controller
{
    public $option_prefix = __PREFIX;
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes()
    {
        register_rest_route(__API_NAMESPACE, '/wp-option', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_wp_option_data'],
                'permission_callback' => api_permission_check(),
                'args'                => [
                    'option_name' => [
                        'required'    => true,
                        'type'        => 'string',
                        'description' => 'The name of the WP Option in the wp_options table.',
                    ]
                ]
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_or_update_wp_option_data'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_schema(),
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [$this, 'delete_wp_option_data'],
                'permission_callback' => api_permission_check(),
            ],
        ]);
        register_rest_route(__API_NAMESPACE, '/wp-option-item', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_wp_option_item'],
                'permission_callback' => api_permission_check(),
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_or_update_option_item'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_option_item_schema(),
            ],
        ]);
    }
    /**
     * GET: Retrieve the JSON data
     */
    public function get_wp_option_data(WP_REST_Request $request)
    {
        $option_name = $this->option_prefix . $request->get_param('option_name');

        if (!$option_name) {
            return new WP_Error('missing_option_name', 'The option_name parameter is required.', ['status' => 400]);
        }

        $data = get_option($option_name, false);
        $decoded_data = decode_json_if_string($data);

        return new WP_REST_Response([
            'status' => 'success',
            'data'   => $decoded_data,
        ], 200);
    }


    /**
     * DELETE: Remove the JSON data
     */
    public function delete_wp_option_data(WP_REST_Request $request)
    {
        $option_name = $this->option_prefix . $request->get_param('option_name');

        if (!$option_name) {
            return new WP_Error('missing_option_name', 'The option_name parameter is required.', ['status' => 400]);
        }

        delete_option($option_name);

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Data deleted successfully.',
        ], 200);
    }



    /**
     * POST: Create or Update JSON data
     */
    public function create_or_update_wp_option_data(WP_REST_Request $request)
    {
        $option_name = $this->option_prefix . $request->get_param('option_name');

        // Validate the option_name parameter
        if (!$option_name) {
            return new WP_Error(
                'missing_option_name',
                'The option_name parameter is required.',
                ['status' => 400]
            );
        }

        // Retrieve and validate the data from the request body
        $data = $request->get_json_params()['data'] ?? null;

        if (!$data || !is_array($data)) {
            return new WP_Error(
                'invalid_data',
                'The "data" parameter is required and must be a valid JSON object.',
                ['status' => 400]
            );
        }

        // Save the data in wp_options
        update_option($option_name, safe_json_encode($data));

        // Return success response
        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Data saved successfully.',
            'data'    => [
                'option_name' => $option_name,
                'data'        => $data,
            ],
        ], 200);
    }





    /**
     * get: Retrieve the item from inside JSON data
     */
    public function get_wp_option_item(WP_REST_Request $request)
    {
        $option_name = $this->option_prefix . $request->get_param('option_name');
        $key = $request->get_param('key');

        // Validate required parameters
        if (!$option_name || !$key) {
            return new WP_Error(
                'missing_parameters',
                'Both "option_name" and "key" are required.',
                ['status' => 400]
            );
        }

        // Fetch the existing JSON data
        $data = get_option($option_name, []);
        $decoded_data = is_string($data) ? json_decode($data, true) : $data;

        // Ensure data is an array
        if (!is_array($decoded_data)) {
            return new WP_Error(
                'invalid_data_format',
                'The WP Option data is not in a valid JSON format.',
                ['status' => 500]
            );
        }

        // Check if the key exists
        if (!array_key_exists($key, $decoded_data)) {
            return new WP_Error(
                'key_not_found',
                'The specified key does not exist.',
                ['status' => 404]
            );
        }

        // Return the key-value pair
        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Key retrieved successfully.',
            'data'    => [
                'key'   => $key,
                'value' => $decoded_data[$key],
            ],
        ], 200);
    }

    /**
     * POST: Add or Update item inside JSON data
     */
    public function create_or_update_option_item(WP_REST_Request $request)
    {
        $option_name = $this->option_prefix . $request->get_param('option_name');
        $key = $request->get_param('key');
        $value = $request->get_param('value');

        // Validate the required parameters
        if (!$key || $value === null) {
            return new WP_Error('missing_parameters', 'Both "key" and "value" are required.', ['status' => 400]);
        }

        // Fetch the existing JSON data
        $data = get_option($option_name, []);
        $decoded_data = is_string($data) ? json_decode($data, true) : $data;

        if (!is_array($decoded_data)) {
            $decoded_data = [];
        }

        // Check if the key already exists
        $is_update = array_key_exists($key, $decoded_data);

        // Add or update the key-value pair
        $decoded_data[$key] = $value;

        // Save the updated data back to the database
        update_option($option_name, json_encode($decoded_data));

        // Return appropriate response
        return new WP_REST_Response([
            'status'  => 'success',
            'message' => $is_update ? 'Item updated successfully.' : 'Item created successfully.',
            'data'    => $decoded_data,
        ], $is_update ? 200 : 201);
    }



    /**
     * Schema for input validation
     */
    public function get_schema()
    {
        return [
            'option_name' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'The name of the option in the wp_options table.',
                'validate_callback' => function ($param) {
                    return is_string($param) && !empty($param);
                },
            ],
            'data' => [
                'required'    => true,
                'type'        => 'object',
                'description' => 'The JSON object containing the key-value pairs to store in the option.',
                'validate_callback' => function ($param) {
                    return is_array($param);
                },
            ],
        ];
    }

    public function get_option_item_schema()
    {
        return [
            'option_name' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'The name of the WP Option in the wp_options table.',
            ],
            'key' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'The key to be created or updated in the JSON object.',
            ],
            'value' => [
                'required'    => true,
                'type'        => 'mixed', // Can be string, number, array, etc.
                'description' => 'The value associated with the key to be created or updated.',
            ],
        ];
    }
}
