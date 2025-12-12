<?php

namespace WooEasyLife\API\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class CustomOrderStatusAPI extends WP_REST_Controller
{

    public function __construct()
    {
        add_action('init', [$this, 'wooeasylife_register_custom_order_statuses']);
        add_filter('wc_order_statuses', [$this, 'wooeasylife_add_custom_order_statuses']);
        add_filter('bulk_actions-edit-shop_order', [$this, 'wooeasylife_add_custom_bulk_actions'], 10);
        // Compatibility with new orders page on HPOS/COT
        add_filter('bulk_actions-woocommerce_page_wc-orders', [$this, 'wooeasylife_add_custom_bulk_actions'], 10);
        add_action('admin_head', [$this, 'wooeasylife_highlight_custom_statuses']);

        add_action('rest_api_init', [$this, 'register_routes']);
    }



    /**
     * 1. Dynamically Register Custom Statuses
     * Make sure all your custom statuses are registered using register_post_status. This ensures WooCommerce recognizes your custom statuses.
     */
    public function wooeasylife_register_custom_order_statuses()
    {
        $custom_statuses = get_option(__PREFIX . 'custom_order_statuses', []);

        foreach ($custom_statuses as $slug => $details) {
            register_post_status('wc-' . $slug, [
                'label'                     => $details['title'],
                'public'                    => true,
                'show_in_admin_status_list' => true,
                'show_in_admin_all_list'    => true,
                'exclude_from_search'       => false,
                'label_count'               => _n_noop(
                    $details['title'] . ' (%s)',
                    $details['title'] . ' (%s)',
                    'wooeasylife'
                ),
            ]);
        }
    }

    /**
     * 2. Add Custom Statuses to WooCommerce Order Filters
     * You must hook into the wc_order_statuses filter to add your custom statuses to the WooCommerce orders filter dropdown.
     */

    public function wooeasylife_add_custom_order_statuses($statuses)
    {
        $custom_statuses = get_option(__PREFIX.'custom_order_statuses', []);
        $new_order_statuses = [];
        foreach ($statuses as $key => $status) {
            if ('wc-processing' === $key) {
                foreach ($custom_statuses as $_key => $_status) {
                    $new_order_statuses['wc-' . $_key] = $_status['title'];
                }
                $status = 'New Order'; // modify "Processing" status text by "New Order" 
            }

            $new_order_statuses[$key] = $status;
        }

        return $new_order_statuses;
    }

    /**
     * 3. Add Custom Statuses to Bulk Actions
     * To include your custom statuses in the bulk actions dropdown (e.g., "Mark as Shipped"), use the following code:
     */
    public function wooeasylife_add_custom_bulk_actions($bulk_actions)
    {
        // Retrieve custom statuses from options
        $custom_statuses = get_option(__PREFIX.'custom_order_statuses', []);
        $new_order_statuses = [];

        foreach ($bulk_actions as $key => $status) {
            if ('mark_processing' === $key) {
                foreach ($custom_statuses as $_key => $_status) {
                    $new_order_statuses['mark_' . $_key] = $_status['title'];
                }
                $status = 'New Order'; // modify "Processing" status text by "New Order" 
            }
            $new_order_statuses[$key] = $status;
        }

        return $new_order_statuses;
    }

    /**
     * 4. Style the Custom Statuses in the Orders Table
     * To make the custom statuses visually identifiable, apply custom colors in the orders table:
     */
    public function wooeasylife_highlight_custom_statuses()
    {
        $custom_statuses = get_option(__PREFIX . 'custom_order_statuses', []);
        echo '<style>';
        foreach ($custom_statuses as $_key => $_status) {
            echo '
                tr.status-' . $_key . ' {
                    background-color:' . $_status["color"] . '22 !important;
                }
                .order-status.status-' . $_key . ' {
                    background-color:' . $_status["color"] . ' !important;
                    color: ' . get_contrast_color($_status["color"]) . ' !important;
                }
                .button.order-status.status-' . $_key . ' {
                    border-radius: 2px !important;
                    height: 30px !important;
                    display: inline-flex !important;
                    align-items: center !important;
                }
            ';
        }
        echo '
            tr.status-processing {
                background-color: #027bff22 !important;
            }
            .order-status.status-processing {
                background-color:#027bff !important;
                color: ' . get_contrast_color('#027bff') . ' !important;
            }
            .button.order-status.status-processing {
                border-radius: 2px !important;
                height: 30px !important;
                display: inline-flex !important;
                align-items: center !important;
            }
            tr.status-cancelled {
                background-color: #DC354522 !important;
            }
            .order-status.status-cancelled {
                background-color:#DC3545 !important;
                color: ' . get_contrast_color('#DC3545') . ' !important;
            }
            .button.order-status.status-cancelled {
                border-radius: 2px !important;
                height: 30px !important;
                display: inline-flex !important;
                align-items: center !important;
            }
                
            tr.status-on-hold {
                background-color: #976e6e22 !important;
            }
            .order-status.status-on-hold {
                background-color:#976e6e !important;
                color: ' . get_contrast_color('#976e6e') . ' !important;
            }
                
            tr.status-cancelled {
                background-color: #ff000022 !important;
            }
            .order-status.status-cancelled {
                background-color:#ff0000 !important;
                color: ' . get_contrast_color('#ff0000') . ' !important;
            }
            tr.status-completed {
                background-color: #0022ff22 !important;
            }
            .order-status.status-completed {
                background-color:#0022ff !important;
                color: ' . get_contrast_color('#0022ff') . ' !important;
            }
        </style>
        ';
    }



    ///////////////////////API Start/////////////////////////

    /**
     * Register REST API routes
     */
    public function register_routes()
    {
        register_rest_route(__API_NAMESPACE, '/statuses', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_statuses'],
                'permission_callback' => api_permission_check(),
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_status'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_status_schema(false), // No 'id' required for creation
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'update_status'],
                'permission_callback' => api_permission_check(),
                'args'                => $this->get_status_schema(false), // ID required in request body
            ]
        ]);

        register_rest_route(__API_NAMESPACE, '/statuses/(?P<id>[^/]+)', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_status'],
                'permission_callback' => api_permission_check(),
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [$this, 'delete_status'],
                'permission_callback' => api_permission_check(),
            ],
        ]);
    }

    /**
     * Get all custom statuses
     */
    public function get_statuses()
    {
        $statuses = get_option(__PREFIX . 'custom_order_statuses', []);
        $statuses_desc = $statuses; // true to preserve keys

        return new WP_REST_Response([
            'status' => 'success',
            'data'   => $statuses_desc,
        ], 200);
    }

    /**
     * Get a single custom status
     */
    public function get_status(WP_REST_Request $request)
    {
        $statuses = get_option(__PREFIX . 'custom_order_statuses', []);
        $status_id = $request->get_param('id');

        if (!isset($statuses[$status_id])) {
            return new WP_Error('not_found', 'Status not found', ['status' => 404]);
        }

        return new WP_REST_Response([
            'status' => 'success',
            'data'   => $statuses[$status_id]
        ], 200);
    }

    /**
     * Create a new custom status
     */
    public function create_status(WP_REST_Request $request)
    {
        $statuses = get_option(__PREFIX . 'custom_order_statuses', []);

        // Sanitize and validate the title and slug
        $title = sanitize_text_field($request->get_param('title'));
        if (empty($title)) {
            return new WP_Error('missing_title', 'The title field is required.', ['status' => 400]);
        }

        $slug = generateSlug($title);

        // Check for duplicate title or slug
        foreach ($statuses as $status) {
            if (strtolower($status['title']) === strtolower($title)) {
                return new WP_Error('status_exists', 'A status with this title already exists.', ['status' => 400]);
            }
        }

        if (isset($statuses[$slug])) {
            return new WP_Error('slug_exists', 'A status with this slug already exists.', ['status' => 400]);
        }

        // Create the status data
        $data = [
            'title'       => $title,
            'slug'        => $slug,
            'is_default' => false,
            'color'       => sanitize_hex_color($request->get_param('color')),
            'description' => sanitize_textarea_field($request->get_param('description')),
        ];

        // Save the new status
        $statuses[$slug] = $data;
        update_option(__PREFIX . 'custom_order_statuses', $statuses);

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Status created successfully',
            'data'    => [
                $slug => $data
            ],
        ], 201);
    }


    /**
     * Update an existing custom status
     */
    public function update_status(WP_REST_Request $request)
    {
        $statuses = get_option(__PREFIX . 'custom_order_statuses', []);
        $slug = $request->get_param('slug'); // gere ID actually is slug

        if (!isset($statuses[$slug])) {
            return new WP_Error('not_found', 'Status not found', ['status' => 404]);
        }

        $new_title = sanitize_text_field($request->get_param('title'));
        $new_slug = generateSlug($new_title);

        // Validate uniqueness of the title (excluding the current item)
        foreach ($statuses as $key => $status) {
            if ($key !== $slug && strtolower($status['slug']) === strtolower($new_slug)) {
                return new WP_Error('duplicate_title', 'A status with this title already exists.', ['status' => 400]);
            }
        }

        unset($statuses[$slug]); // Remove the old slug
        $statuses[$new_slug] = [
            'title'       => $new_title,
            'slug'        => $new_slug,
            'color'       => sanitize_hex_color($request->get_param('color')),
            'description' => sanitize_textarea_field($request->get_param('description')),
        ];

        update_option(__PREFIX . 'custom_order_statuses', $statuses);

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Status updated successfully',
            'data'    => $statuses[$new_slug],
        ], 200);
    }

    /**
     * Delete a custom status
     */
    public function delete_status(WP_REST_Request $request)
    {
        $statuses = get_option(__PREFIX . 'custom_order_statuses', []);
        $slug = urldecode($request->get_param('id')); // Decode URL-encoded Unicode characters

        if (!isset($statuses[$slug])) {
            // Debug: Return available slugs if not found
            return new WP_Error('not_found', 'Status not found. Requested: ' . $slug . '. Available: ' . implode(', ', array_keys($statuses)), ['status' => 404]);
        }

        unset($statuses[$slug]);
        update_option(__PREFIX . 'custom_order_statuses', $statuses);

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'Status deleted successfully',
        ], 200);
    }

    /**
     * Schema for status input validation
     */
    public function get_status_schema($require_id = true)
    {
        $schema = [
            'title' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'Title for the status.',
            ],
            'slug' => [
                'required'    => false,
                'type'        => 'string',
                'description' => 'Slug for the status.',
            ],
            'color' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'Hex color for the status.',
            ],
            'is_default' => [
                'required'    => false,
                'type'        => 'boolean',
                'description' => 'Hex color for the status.',
            ],
            'description' => [
                'required'    => false,
                'type'        => 'string',
                'description' => 'Description for the status.',
            ],
        ];

        if ($require_id) {
            $schema['id'] = [
                'required'    => true,
                'type'        => 'string',
                'description' => 'Unique identifier (slug) for the status.',
            ];
        }

        return $schema;
    }
}
