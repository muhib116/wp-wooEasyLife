<?php
namespace WooEasyLife\Extension;

class URLReplacer {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_submenu'], 20);
    }

    public function add_submenu() {
        add_submenu_page(
            'woo-easy-life',           // Parent slug
            'URL Replacer',             // Page title
            'URL Replacer',             // Menu title
            'manage_options',           // Capability
            'wel-url-replacer',         // Menu slug
            [$this, 'render_page']      // Callback function
        );
    }

    public function render_page() {
        $errors = [];
        $success = false;

        if (isset($_POST['run_replacer']) && check_admin_referer('wel_url_replacer_action', 'wel_url_replacer_nonce')) {
            $old_url = isset($_POST['old_url']) ? sanitize_text_field($_POST['old_url']) : '';
            $new_url = isset($_POST['new_url']) ? sanitize_text_field($_POST['new_url']) : '';

            // Validation
            if (empty($old_url)) {
                $errors[] = 'Old URL is required.';
            } elseif (!filter_var($old_url, FILTER_VALIDATE_URL)) {
                $errors[] = 'Old URL is not a valid URL.';
            }

            if (empty($new_url)) {
                $errors[] = 'New URL is required.';
            } elseif (!filter_var($new_url, FILTER_VALIDATE_URL)) {
                $errors[] = 'New URL is not a valid URL.';
            }

            if ($old_url === $new_url) {
                $errors[] = 'Old URL and New URL cannot be the same.';
            }

            // If no errors, proceed with replacement
            if (empty($errors)) {
                try {
                    $this->run_url_replacement($old_url, $new_url);
                    $success = true;
                } catch (\Exception $e) {
                    $errors[] = 'An error occurred: ' . $e->getMessage();
                }
            }
        }
        ?>
        <div class="wrap">
            <h1>🔗 URL Replacer - WooEasyLife</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="notice notice-error">
                    <ul style="margin: 0.5em 0;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>✅ Success!</strong> URLs have been replaced successfully throughout your database!</p>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 800px;">
                <h2>Replace URLs in Database</h2>
                <p>This powerful tool will search and replace URLs throughout your entire WordPress database. Perfect for moving sites or changing domains.</p>
                
                <form method="post" style="margin-top: 20px;">
                    <?php wp_nonce_field('wel_url_replacer_action', 'wel_url_replacer_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="old_url">Old URL *</label>
                            </th>
                            <td>
                                <input type="url" 
                                       name="old_url" 
                                       id="old_url" 
                                       class="regular-text" 
                                       style="min-width: 400px;"
                                       placeholder="https://old-domain.com"
                                       value="<?php echo isset($_POST['old_url']) ? esc_attr($_POST['old_url']) : ''; ?>"
                                       required>
                                <p class="description">Enter the URL you want to replace (e.g., http://localhost:8080/old-site)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="new_url">New URL *</label>
                            </th>
                            <td>
                                <input type="url" 
                                       name="new_url" 
                                       id="new_url" 
                                       class="regular-text" 
                                       style="min-width: 400px;"
                                       placeholder="https://new-domain.com"
                                       value="<?php echo isset($_POST['new_url']) ? esc_attr($_POST['new_url']) : ''; ?>"
                                       required>
                                <p class="description">Enter the new URL (e.g., http://localhost:8080/new-site)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" 
                               name="run_replacer" 
                               id="submit" 
                               class="button button-primary button-large" 
                               value="🔄 Replace URLs Now"
                               onclick="return confirm('⚠️ WARNING: This will modify your database!\n\nAre you sure you want to replace all occurrences of the old URL with the new URL?\n\nThis action cannot be undone automatically. Make sure you have a backup!\n\nClick OK to proceed or Cancel to abort.');">
                        <span style="margin-left: 15px; color: #666;">
                            <strong>Note:</strong> Always backup your database before proceeding.
                        </span>
                    </p>
                </form>
            </div>

            <div class="card" style="max-width: 800px; margin-top: 20px; background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px;">
                <h3 style="margin-top: 0;">⚠️ Important Safety Information</h3>
                <ul style="margin-bottom: 0;">
                    <li><strong>🔒 Always backup your database</strong> before running this tool</li>
                    <li><strong>📊 Database tables affected:</strong> wp_options (home, siteurl), wp_posts (content, GUID), wp_postmeta</li>
                    <li><strong>✅ Double-check both URLs</strong> are entered correctly before submitting</li>
                    <li><strong>⏱️ Processing time:</strong> May take several minutes for large databases</li>
                    <li><strong>🔄 Serialized data:</strong> This tool safely handles WordPress serialized data</li>
                    <li><strong>🚀 Best practice:</strong> Test on a staging site first if possible</li>
                </ul>
            </div>

            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h3>💡 Common Use Cases</h3>
                <ul style="margin-bottom: 0;">
                    <li>Moving from localhost to production: <code>http://localhost → https://example.com</code></li>
                    <li>Changing domain names: <code>https://oldsite.com → https://newsite.com</code></li>
                    <li>Switching HTTP to HTTPS: <code>http://example.com → https://example.com</code></li>
                    <li>Moving to subdirectory: <code>https://example.com → https://example.com/blog</code></li>
                </ul>
            </div>
        </div>
        <style>
            .wrap h1 { margin-bottom: 20px; }
            .card { padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
            .card h2 { margin-top: 0; }
            .card h3 { color: #23282d; }
            .form-table th { padding-top: 20px; }
            .form-table input[type="url"] { font-family: monospace; }
        </style>
        <?php
    }

    private function run_url_replacement($old_url, $new_url) {
        global $wpdb;

        // Remove trailing slashes for consistency
        $old_url = rtrim($old_url, '/');
        $new_url = rtrim($new_url, '/');

        // Update wp_options (home and siteurl)
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->options} SET option_value = REPLACE(option_value, %s, %s) WHERE option_name = 'home' OR option_name = 'siteurl'",
                $old_url,
                $new_url
            )
        );

        // Update wp_posts GUID
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->posts} SET guid = REPLACE(guid, %s, %s)",
                $old_url,
                $new_url
            )
        );

        // Update post content safely (batch processing to avoid memory issues)
        $batch_size = 100;
        $offset = 0;
        
        while (true) {
            $posts = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT ID, post_content FROM {$wpdb->posts} 
                    WHERE post_content LIKE %s 
                    LIMIT %d OFFSET %d",
                    '%' . $wpdb->esc_like($old_url) . '%',
                    $batch_size,
                    $offset
                )
            );

            if (empty($posts)) {
                break;
            }

            foreach ($posts as $post) {
                $content = str_replace($old_url, $new_url, $post->post_content);
                $wpdb->update(
                    $wpdb->posts, 
                    ['post_content' => $content], 
                    ['ID' => $post->ID],
                    ['%s'],
                    ['%d']
                );
            }

            $offset += $batch_size;
        }

        // Update postmeta safely (handle serialized data)
        $offset = 0;
        
        while (true) {
            $postmeta = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT meta_id, meta_value FROM {$wpdb->postmeta} 
                    WHERE meta_value LIKE %s 
                    LIMIT %d OFFSET %d",
                    '%' . $wpdb->esc_like($old_url) . '%',
                    $batch_size,
                    $offset
                )
            );

            if (empty($postmeta)) {
                break;
            }

            foreach ($postmeta as $meta) {
                $value = maybe_unserialize($meta->meta_value);
                
                if (is_string($value)) {
                    $value = str_replace($old_url, $new_url, $value);
                } elseif (is_array($value) || is_object($value)) {
                    $value = $this->recursive_url_replace($old_url, $new_url, $value);
                }
                
                $wpdb->update(
                    $wpdb->postmeta, 
                    ['meta_value' => maybe_serialize($value)], 
                    ['meta_id' => $meta->meta_id],
                    ['%s'],
                    ['%d']
                );
            }

            $offset += $batch_size;
        }

        // Clear all caches
        wp_cache_flush();

        return true;
    }

    /**
     * Recursively replace URLs in arrays and objects
     */
    private function recursive_url_replace($old_url, $new_url, $data) {
        if (is_string($data)) {
            return str_replace($old_url, $new_url, $data);
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->recursive_url_replace($old_url, $new_url, $value);
            }
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->recursive_url_replace($old_url, $new_url, $value);
            }
        }

        return $data;
    }
}
