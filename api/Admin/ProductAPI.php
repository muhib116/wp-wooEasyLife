<?php
namespace WooEasyLife\Api\Admin;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

class ProductAPI extends WP_REST_Controller {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register product routes
     */
    public function register_routes() {
        // Get single product
        register_rest_route(__API_NAMESPACE, '/products/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_product'],
                'permission_callback' => api_permission_check(),
                'args'                => [
                    'id' => [
                        'required'          => true,
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        },
                        'sanitize_callback' => 'absint',
                    ],
                ],
            ],
        ]);
        
        // Get multiple products (search/list)
        register_rest_route(__API_NAMESPACE, '/products', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_products'],
                'permission_callback' => api_permission_check(),
                'args'                => [
                    'search' => [
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                        'default'           => '',
                    ],
                    'per_page' => [
                        'required'          => false,
                        'default'           => 20,
                        'sanitize_callback' => 'absint',
                    ],
                    'page' => [
                        'required'          => false,
                        'default'           => 1,
                        'sanitize_callback' => 'absint',
                    ],
                ],
            ],
        ]);
    }
    
    /**
     * Get single product by ID
     */
    public function get_product(WP_REST_Request $request) {
        try {
            $product_id = $request->get_param('id');
            
            // Check if WooCommerce is active
            if (!class_exists('WooCommerce')) {
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'WooCommerce is not active',
                ], 500);
            }
            
            // Get WooCommerce product
            $product = wc_get_product($product_id);
            
            if (!$product || !is_object($product)) {
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'Product not found',
                ], 404);
            }
            
            // Return product data even if not published (for admin purposes)
            // You can add status check if needed: $product->get_status() !== 'publish'
            
            // Get product image URL safely
            $image_id = $product->get_image_id();
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
            
            // Return product data
            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'Product retrieved successfully',
                'data'    => [
                    'id'              => $product->get_id(),
                    'name'            => $product->get_name(),
                    'slug'            => $product->get_slug(),
                    'type'            => $product->get_type(),
                    'status'          => $product->get_status(),
                    'price'           => $product->get_price(),
                    'regular_price'   => $product->get_regular_price(),
                    'sale_price'      => $product->get_sale_price(),
                    'stock_status'    => $product->get_stock_status(),
                    'stock_quantity'  => $product->get_stock_quantity(),
                    'manage_stock'    => $product->get_manage_stock(),
                    'in_stock'        => $product->is_in_stock(),
                    'on_sale'         => $product->is_on_sale(),
                    'purchasable'     => $product->is_purchasable(),
                    'image_id'        => $image_id,
                    'image_url'       => $image_url,
                    'sku'             => $product->get_sku(),
                    'categories'      => $this->get_product_categories($product),
                    'attributes'      => $this->get_product_attributes($product),
                ],
            ], 200);
            
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log('ProductAPI get_product error: ' . $e->getMessage());
            
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Error fetching product: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get products list (with search)
     */
    public function get_products(WP_REST_Request $request) {
        try {
            $search = $request->get_param('search');
            $per_page = $request->get_param('per_page');
            $page = $request->get_param('page');
            
            // Check if WooCommerce is active
            if (!class_exists('WooCommerce')) {
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'WooCommerce is not active',
                ], 500);
            }
            
            $args = [
                'post_type'      => 'product',
                'posts_per_page' => $per_page,
                'paged'          => $page,
                'post_status'    => 'publish',
                'orderby'        => 'title',
                'order'          => 'ASC',
            ];
            
            if (!empty($search)) {
                $args['s'] = $search;
            }
            
            $query = new \WP_Query($args);
            $products = [];
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $product = wc_get_product(get_the_ID());
                    
                    if (!$product || !is_object($product)) {
                        // Skip this product if not found or invalid
                        continue;
                    }
                    
                    $image_id = $product->get_image_id();
                    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
                    
                    $products[] = [
                        'id'            => $product->get_id(),
                        'name'          => $product->get_name(),
                        'price'         => $product->get_price(),
                        'regular_price' => $product->get_regular_price(),
                        'stock_status'  => $product->get_stock_status(),
                        'in_stock'      => $product->is_in_stock(),
                        'image_url'     => $image_url,
                        'sku'           => $product->get_sku(),
                        'type'          => $product->get_type(),
                    ];
                }
                wp_reset_postdata();
            }
            
            return new WP_REST_Response([
                'status'  => 'success',
                'message' => 'Products retrieved successfully',
                'data'    => $products,
                'pagination' => [
                    'total'         => $query->found_posts,
                    'total_pages'   => $query->max_num_pages,
                    'current_page'  => $page,
                    'per_page'      => $per_page,
                ],
            ], 200);
            
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log('ProductAPI get_products error: ' . $e->getMessage());
            
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Error fetching products: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get product categories
     */
    private function get_product_categories($product) {
        $category_ids = $product->get_category_ids();
        $categories = [];
        
        if (!empty($category_ids) && is_array($category_ids)) {
            foreach ($category_ids as $cat_id) {
                $category = get_term($cat_id, 'product_cat');
                if ($category && !is_wp_error($category)) {
                    $categories[] = [
                        'id'   => $category->term_id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                }
            }
        }
        
        return $categories;
    }
    
    /**
     * Get product attributes
     */
    private function get_product_attributes($product) {
        $attributes = [];
        
        if ($product->is_type('variable')) {
            $product_attributes = $product->get_attributes();
            
            if (!empty($product_attributes) && is_array($product_attributes)) {
                foreach ($product_attributes as $attribute) {
                    if (is_object($attribute)) {
                        $attributes[] = [
                            'name'      => $attribute->get_name(),
                            'options'   => $attribute->get_options(),
                            'visible'   => $attribute->get_visible(),
                            'variation' => $attribute->get_variation(),
                        ];
                    }
                }
            }
        }
        
        return $attributes;
    }
}