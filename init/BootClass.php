<?php
namespace WooEasyLife\Init;

class BootClass {
    public $manifest_path;
    public $manifest;
    public $css_file_name;
    public $js_file_name;
    public $assets_file_name;

    public function __construct()
    {
        $this->manifest_path = plugin_dir_path(__DIR__) . 'vue-project/dist/.vite/manifest.json';
        $this->manifest = json_decode(file_get_contents($this->manifest_path), true);
        $this->css_file_name = $this->manifest['src/main.ts']['css'][0] ?? null;
        $this->js_file_name = $this->manifest['src/main.ts']['file'] ?? null;
        $this->assets_file_name = $this->manifest['src/main.ts']['assets'][0] ?? null;

        add_action('admin_menu', [$this, 'wel_add_menu']);
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
            'manage_options',
            'woo-easy-life',
            [$this, 'wel_render_admin_page'],
            'dashicons-admin-site-alt',
            6
        );
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
        if($hook_suffix != 'toplevel_page_woo-easy-life') return;
    
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
        return;
    }
}