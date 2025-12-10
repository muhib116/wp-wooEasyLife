<?php
namespace WooEasyLife\Init;

class BootClass {
    public $manifest_path;
    public $manifest;
    public $css_file_name;
    public $js_file_name;
    public $assets_file_name;
    public $svg_icon_wel = '<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#a)" fill="#FFC149"><path d="M12.565 1.879H9.21a.838.838 0 1 0 0 1.677h1.33L7.535 6.562l-2.761-2.76a.84.84 0 0 0-1.185 0L.235 7.154A.838.838 0 0 0 1.421 8.34l2.76-2.76 2.761 2.76a.84.84 0 0 0 1.185 0l3.6-3.599v1.33a.838.838 0 1 0 1.676 0V2.717a.84.84 0 0 0-.838-.838"/><path d="M7.743 12.099q0 .835-.3 1.56-.298.716-.814 1.246a3.9 3.9 0 0 1-1.211.828q-.69.3-1.483.3-.787 0-1.483-.3a4 4 0 0 1-1.211-.828 4 4 0 0 1-.815-1.246 4 4 0 0 1-.3-1.56q0-.849.3-1.573.3-.725.815-1.247a3.8 3.8 0 0 1 1.211-.828q.696-.3 1.483-.3.793 0 1.483.286.696.28 1.211.8.516.516.815 1.247.3.723.3 1.615m-1.914 0q0-.459-.154-.828a1.9 1.9 0 0 0-.403-.641 1.7 1.7 0 0 0-.606-.41 1.8 1.8 0 0 0-.731-.147q-.39 0-.738.146-.34.14-.599.411-.251.264-.397.64a2.2 2.2 0 0 0-.146.829q0 .432.146.8.147.37.397.641.258.271.599.432.348.153.738.153t.73-.146a1.82 1.82 0 0 0 1.01-1.051q.154-.376.154-.829m10.17 0q0 .835-.3 1.56-.298.716-.814 1.246a3.9 3.9 0 0 1-1.211.828q-.69.3-1.483.3-.787 0-1.483-.3a4 4 0 0 1-1.211-.828 4 4 0 0 1-.815-1.246 4 4 0 0 1-.3-1.56q0-.849.3-1.573.3-.725.815-1.247a3.8 3.8 0 0 1 1.21-.828q.698-.3 1.484-.3.793 0 1.483.286.696.28 1.211.8.516.516.815 1.247.3.723.299 1.615m-1.915 0q0-.459-.153-.828a1.9 1.9 0 0 0-.404-.641 1.7 1.7 0 0 0-.605-.41 1.8 1.8 0 0 0-.731-.147q-.39 0-.738.146-.341.14-.599.411-.251.264-.397.64a2.2 2.2 0 0 0-.146.829q0 .432.146.8.147.37.397.641.258.271.599.432.348.153.738.153t.73-.146a1.82 1.82 0 0 0 1.01-1.051q.153-.376.153-.829"/></g><defs><clipPath id="a"><path fill="#fff" d="M0 .956h16v16H0z"/></clipPath></defs></svg>';

    public function __construct()
    {
        $this->manifest_path = plugin_dir_path(__DIR__) . 'vue-project/dist/.vite/manifest.json';
        $this->manifest = json_decode(file_get_contents($this->manifest_path), true);
        $this->css_file_name = $this->manifest['src/main.ts']['css'][0] ?? null;
        $this->js_file_name = $this->manifest['src/main.ts']['file'] ?? null;
        $this->assets_file_name = $this->manifest['src/main.ts']['assets'][0] ?? null;

        add_action('admin_menu', [$this, 'wel_add_menu']);
        add_action('admin_head', [$this, 'wel_custom_menu_icon']);

        add_action('admin_bar_menu', [$this, 'wel_add_bar_menu'], 60);
        add_action('admin_enqueue_scripts', [$this, 'wel_enqueue_scripts']);
        add_filter('script_loader_tag', function ($tag, $handle, $src) {
            if ('woo-easy-life' === $handle) {
                $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
            }
            return $tag;
        }, 10, 3);
        add_action('admin_notices', [$this, 'check_woocommerce_installed']);
    }

    public function wel_add_menu() {
        add_menu_page(
            'WEL',
            'WooEasyLife',
            // 'manage_options',
            'manage_woocommerce', // Capability
            'woo-easy-life',
            [$this, 'wel_render_admin_page'],
            '',
            6
        );

        // Add Dashboard submenu
        add_submenu_page(
            'woo-easy-life',           // Parent slug
            'Dashboard',                // Page title
            'Dashboard',                // Menu title
            'manage_woocommerce',       // Capability
            'woo-easy-life',            // Menu slug (same as parent to make it the first submenu)
            [$this, 'wel_render_admin_page'] // Callback function
        );

        // Add Orders submenu
        add_submenu_page(
            'woo-easy-life',
            'Orders',
            'Orders',
            'manage_woocommerce',
            'woo-easy-life#/orders',
            [$this, 'wel_render_admin_page']
        );

        // Add Missing Orders submenu
        add_submenu_page(
            'woo-easy-life',
            'Missing Orders',
            'Missing Orders',
            'manage_woocommerce',
            'woo-easy-life#/missing-orders',
            [$this, 'wel_render_admin_page']
        );

        // Add Black List submenu
        add_submenu_page(
            'woo-easy-life',
            'Black List',
            'Black List',
            'manage_woocommerce',
            'woo-easy-life#/config/custom-black-list',
            [$this, 'wel_render_admin_page']
        );
    }

    public function wel_custom_menu_icon() {
        global $icon_url;
        echo '<style>
            #toplevel_page_woo-easy-life .wp-menu-image:before,
            #toplevel_page_woo-easy-life .wp-menu-image img {
                display: none; /* Hide default icon */
            }
            #toplevel_page_woo-easy-life .wp-menu-image {
                background: url("' . esc_url($icon_url) . '") no-repeat center center !important;
                background-size: 20px !important;
            }
        </style>';
    }

    public function wel_add_bar_menu($wp_admin_bar) {
        // Get order counts
        $order_counts = $this->get_cached_order_counts();
        $total_new = $order_counts['processing'];
        
        // Create badge HTML
        $badge_html = '';
        if ($total_new > 0) {
            $badge_html = '
                <span style="
                    display: grid;
                    background: #c3912c;
                    color: white;
                    border-radius: 10px;
                    width: 20px;
                    height: 20px;
                    aspect-ratio: 1 / 1;
                    font-size: 12px;
                    margin-left: 6px;
                    flex-shrink: 0;
                    place-content: center;
                ">' . $total_new . '</span>
            ';
        }
        
        // Main menu item
        $wp_admin_bar->add_node(array(
            'id'    => 'woo-easy-life-bar-menu',
            'title' => '<span style="height: 100%; display: flex; align-items: center; justify-content: center;">' 
                        . $this->svg_icon_wel 
                        . '<span style="margin-left: 8px;">WEL</span>' 
                        . $badge_html 
                        . '</span>',
            'href'  => admin_url('admin.php?page=woo-easy-life'),
            'meta'  => array(
                'title' => __('Go to WooEasyLife - ' . $total_new . ' new orders'),
            ),
        ));

        // Add submenu for call not received orders
        if ($order_counts['call-not-received'] > 0) {
            $wp_admin_bar->add_node(array(
                'parent' => 'woo-easy-life-bar-menu',
                'id'     => 'wel-call-not-received-orders',
                'title'  => sprintf(__('📞 Call Not Received (%d)'), $order_counts['call-not-received']),
                'href'   => admin_url('admin.php?page=woo-easy-life#/orders?status=call-not-received'),
            ));
        }
        
        // Add submenu for processing orders (New Orders)
        if ($order_counts['processing'] > 0) {
            $wp_admin_bar->add_node(array(
                'parent' => 'woo-easy-life-bar-menu',
                'id'     => 'wel-processing-orders',
                'title'  => sprintf(__('🆕 New Orders (%d)'), $order_counts['processing']),
                'href'   => admin_url('admin.php?page=woo-easy-life#/orders?status=processing'),
            ));
        }
        
        // Add submenu for courier entry (courier-entry status)
        if ($order_counts['courier-entry'] > 0) {
            $wp_admin_bar->add_node(array(
                'parent' => 'woo-easy-life-bar-menu',
                'id'     => 'wel-courier-entry',
                'title'  => sprintf(__('📦 Courier Entry (%d)'), $order_counts['courier-entry']),
                'href'   => admin_url('admin.php?page=woo-easy-life#/orders?status=courier-entry'),
            ));
        }
        
        // Add view all orders link
        $wp_admin_bar->add_node(array(
            'parent' => 'woo-easy-life-bar-menu',
            'id'     => 'wel-all-orders',
            'title'  => '
                            <hr style="margin-top: 5px; border: none; border-top: 1px solid #ddd5;">
                            📋 View All Orders
                        ',
            'href'   => admin_url('admin.php?page=woo-easy-life#/orders'),
        ));
    }

    private function get_cached_order_counts() {
        $cache_key = 'wel_order_counts';
        $counts = get_transient($cache_key);
        
        if (false === $counts) {
            $counts = $this->get_order_counts();
            set_transient($cache_key, $counts, 30); // Cache for 30 seconds
        }
        
        return $counts;
    }

    private function get_order_counts() {
        if (!class_exists('WooCommerce')) {
            return array(
                'call-not-received' => 0,
                'processing' => 0,
                'courier-entry' => 0
            );
        }
        
        $counts = array();

        foreach (array('call-not-received', 'processing', 'courier-entry') as $status) {
            $args = array(
                'status' => $status,
                'limit'  => -1,
                'return' => 'ids',
            );
            
            $orders = wc_get_orders($args);
            $counts[$status] = count($orders);
        }
        
        return $counts;
    }

    public function clear_orders_cache() {
        delete_transient('wel_new_orders_count');
        delete_transient('wel_order_counts');
    }

    public function wel_render_admin_page() {
        echo '
        <style>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
        </style>
        <div id="woo-easy-life" style="font-family: Poppins, sans-serif;">
        
        </div>'; // Vue app container
    }

    // load admin script
    public function wel_enqueue_scripts($hook_suffix) {
        // Allow scripts on all WooEasyLife pages
        $allowed_pages = [
            'toplevel_page_woo-easy-life',
            'wooeasylife_page_woo-easy-life-orders',
            'wooeasylife_page_woo-easy-life-missing-orders',
            'wooeasylife_page_woo-easy-life-black-list'
        ];
        
        if(!in_array($hook_suffix, $allowed_pages)) return;
    
        if (file_exists($this->manifest_path)) {
            if ($this->js_file_name) {
                wp_enqueue_script(
                    'woo-easy-life',
                    plugins_url('vue-project/dist/' . $this->js_file_name, __DIR__),
                    [],
                    null,
                    true
                );
            }
    
            if ($this->css_file_name) {
                wp_enqueue_style(
                    'woo-easy-life-style',
                    plugins_url('vue-project/dist/' . $this->css_file_name, __DIR__),
                    [],
                    null
                );
            }
        }
    
        // Pass data to Vue
        wp_localize_script('woo-easy-life', 'wooEasyLife', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('wel_nonce'),
            'dist_url' => plugins_url('vue-project/dist', __DIR__),
            'site_url' => get_site_url(),
        ]);
        wp_enqueue_script('woo-easy-life');
    }


    public function check_woocommerce_installed() {

        if ($this->assets_file_name) {
            $assets = plugins_url('vue-project/dist/' . $this->assets_file_name, __DIR__);
            echo '<div class="notice notice-warning is-dismissible">
                    '.$assets.'
                  </div>';
        }

        // Check if WooCommerce is active
        if (class_exists('WooCommerce')) {
            return; // WooCommerce is already installed and active
        }
    
        // Check if WooCommerce is installed but not activated
        $is_wooCommerce_installed = false;
        $plugins = get_plugins();
        foreach ($plugins as $plugin_file => $plugin_data) {
            if (strpos($plugin_file, 'woocommerce.php') !== false) {
                $is_wooCommerce_installed = true;
                break;
            }
        }
    
        if ($is_wooCommerce_installed) {
            // WooCommerce is installed but not activated
            echo '<div class="notice notice-warning is-dismissible">
                    <p>WooCommerce is installed but not activated. <a class="text-green-500" href="' . esc_url(admin_url('plugins.php')) . '">Activate WooCommerce now</a>.</p>
                  </div>';
        } else {
            // WooCommerce is not installed
            echo '<div class="notice notice-error is-dismissible">
                    <p>WooCommerce is not installed. 
                        <a class="text-blue-500" href="' . esc_url(admin_url('plugin-install.php?s=woocommerce&tab=search&type=term')) . '">Install WooCommerce now</a>.
                    </p>
                  </div>';
        }
        return $is_wooCommerce_installed;
    }
}