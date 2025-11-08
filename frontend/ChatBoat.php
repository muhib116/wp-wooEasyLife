<?php
namespace WooEasyLife\Frontend;

class ChatBoat {

    public function __construct() {
        // Check if chatbot is enabled in settings
        if ($this->is_chatbot_enabled()) {
            add_action('wp_footer', [$this, 'render_chatbot']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_chatbot_assets']);
            
            // Make the chatbot script a module
            add_filter('script_loader_tag', [$this, 'add_type_module_to_chatbot'], 10, 3);
        }
    }

    /**
     * Check if chatbot is enabled in plugin settings
     * 
     * @return bool
     */
    private function is_chatbot_enabled() {
        // Return false for now to avoid errors during testing
        // You can enable this later in the plugin settings
        return true;
        
        /* Uncomment when ready to use:
        $option_key = (defined('__PREFIX') ? __PREFIX : 'woo_easy_life_') . 'config';
        
        if (!function_exists('decode_json_if_string')) {
            return false;
        }
        
        $existing_config = decode_json_if_string(get_option($option_key));
        
        // Default to false if not explicitly enabled
        return isset($existing_config['chatboat_enabled']) && $existing_config['chatboat_enabled'] === true;
        */
    }

    /**
     * Enqueue chatbot assets (JS and CSS)
     * 
     * @return void
     */
    public function enqueue_chatbot_assets() {
        $manifest_path = plugin_dir_path(dirname(__FILE__)) . 'vue-project/dist/.vite/manifest.json';
        
        if (!file_exists($manifest_path)) {
            return;
        }

        $manifest = json_decode(file_get_contents($manifest_path), true);
        
        // The manifest key is 'src/chatBoat/main.ts', not 'chatbot'
        if (!$manifest || !isset($manifest['src/chatBoat/main.ts'])) {
            return;
        }

        $chatbot_entry = $manifest['src/chatBoat/main.ts'];
        $plugin_url = plugin_dir_url(dirname(__FILE__));

        // Enqueue chatbot JavaScript
        if (isset($chatbot_entry['file'])) {
            wp_enqueue_script(
                'wel-chatbot-js',
                $plugin_url . 'vue-project/dist/' . $chatbot_entry['file'],
                [],
                null,
                true
            );
        }

        // Enqueue chatbot CSS if exists
        if (isset($chatbot_entry['css']) && is_array($chatbot_entry['css'])) {
            foreach ($chatbot_entry['css'] as $css_file) {
                wp_enqueue_style(
                    'wel-chatbot-css',
                    $plugin_url . 'vue-project/dist/' . $css_file,
                    [],
                    null
                );
            }
        }
    }

    /**
     * Render chatbot HTML in footer
     * 
     * @return void
     */
    public function render_chatbot() {
        echo '<div id="wel-chatbot-app"></div>';
    }

    /**
     * Add type="module" to chatbot script tag
     * 
     * @param string $tag
     * @param string $handle
     * @param string $src
     * @return string
     */
    public function add_type_module_to_chatbot($tag, $handle, $src) {
        if ('wel-chatbot-js' === $handle) {
            $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
        }
        return $tag;
    }
}