<?php
namespace WooEasyLife\Init;

class UpdatePlugin
{
    private $plugin_slug;
    private $update_server_url;
    private $plugin_version;
    private $license_key;

    /**
     * Constructor to initialize the updater.
     *
     * @param string $plugin_version The current version of the plugin.
     * @param string $license_key    The license key for authorization.
     */
    public function __construct($plugin_version, $license_key)
    {
        $this->plugin_slug = 'woo-easy-life';
        $this->update_server_url = 'https://api.wpsalehub.com/get-metadata';
        $this->plugin_version = $plugin_version;
        $this->license_key = $license_key;

        // Add hooks for updates and plugin information
        add_filter('site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
        add_action('admin_notices', [$this, 'woo_life_changer_update_notice']);
    }

    /**
     * Check for plugin updates.
     *
     * @param object $transient The update transient object.
     * @return object The modified transient object.
     */
    public function check_for_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $update_data = $this->get_meta_data();
        if(!$update_data){
            return $transient;
        }

        if (
            isset($update_data['version'], $update_data['download_url']) &&
            version_compare($this->plugin_version, $update_data['version'], '<')
        ) {
            $transient->response[$this->plugin_slug . '/' . $this->plugin_slug . '.php'] = (object) [
                'slug'        => $this->plugin_slug,
                'new_version' => $update_data['version'],
                'url'         => $update_data['homepage'] ?? '',
                'package'     => $update_data['download_url'],
            ];
        }

        return $transient;
    }

    /**
     * Provide plugin information for the "View Details" popup.
     *
     * @param false|object|array $result The current plugin info.
     * @param string $action            The requested action.
     * @param object $args              The plugin arguments.
     * @return false|object|array The plugin info object or false if not handled.
     */
    public function plugin_info($result, $action, $args)
    {
        if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        $response = wp_remote_get(
            $this->update_server_url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->license_key,
                    'origin' => site_url()
                ],
            ]
        );

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return $result;
        }

        $plugin_info = json_decode(wp_remote_retrieve_body($response), true);


        if (isset($plugin_info['name'])) {
            $result = (object) [
                'name'          => $plugin_info['name'],
                'slug'          => $this->plugin_slug,
                'version'       => $plugin_info['version'],
                'author'        => $plugin_info['author'],
                'homepage'      => $plugin_info['homepage'] ?? '',
                'download_link' => $plugin_info['download_url'],
                'requires'      => $plugin_info['requires'] ?? '',
                'tested'        => $plugin_info['tested'] ?? '',
                'requires_php'  => $plugin_info['requires_php'] ?? '',
                'sections'      => [
                    'description' => isset($plugin_info['sections']['description'])
                                        ? wpautop(wp_kses_post($plugin_info['sections']['description']))
                                        : '',
                    'changelog'   => isset($plugin_info['sections']['changelog'])
                                    ? wpautop(wp_kses_post($plugin_info['sections']['changelog']))
                                    : '',
                ],
                'icons' => [
                    '1x' => $plugin_info['icons']['1x'] ?? '',
                    '2x' => $plugin_info['icons']['2x'] ?? '',
                    'svg' => $plugin_info['icons']['svg'] ?? '',
                ],
            ];
        }


        if ($action === 'plugin_information' && $args->slug === $this->plugin_slug) {
            $result->banners = array(
                'high' => 'https://wpsalehub.com/wp-content/uploads/2025/01/Plugin-banner-heigh.webp',
                'low' => 'https://wpsalehub.com/wp-content/uploads/2025/01/Plugin-banner-low.webp'
            );
        }

        return $result;
    }

    /**
     * Display a WP notice if a plugin update is available.
     */
    public function woo_life_changer_update_notice() {
        $meta_data = $this->get_meta_data(); // get json data of the plugin
        if(!$meta_data){
            return;
        }


        // Define the plugin slug and file path
        $plugin_slug = $this->plugin_slug.'/'.$this->plugin_slug.'.php'; // Plugin file path relative to the plugins directory
        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_slug; // Absolute path to the plugin file


        // Get the current plugin version
        if (!file_exists($plugin_path)) {
            return; // Exit if the plugin file doesn't exist
        }

        $plugin_data = get_plugin_data($plugin_path);
        $current_version = $plugin_data['Version']; // Current version from plugin header

        // Define the latest version (this should come from your update server/API)
        $latest_version = $meta_data['version']; // Replace this with the latest version dynamically if needed

        // Check if an update is available
        if (version_compare($current_version, $latest_version, '<')) {
            // Display the notice
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>Woo Easy Life:</strong> A new version (' . esc_html($latest_version) . ') is available. You are using version ' . esc_html($current_version) . '. <a href="' . esc_url(admin_url('update-core.php')) . '">Update now</a>.</p>';
            echo '</div>';
        }
    }


    private function get_meta_data () {
        $response = wp_remote_get(
            $this->update_server_url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->license_key,
                    'origin' => site_url()
                ],
            ]
        );

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}