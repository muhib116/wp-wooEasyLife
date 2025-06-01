<?php
namespace WooEasyLife\Admin;
if (!defined('ABSPATH')) exit;

class PixelHooks {
    public static function init() {
        // Core Pixel Base
        add_action('wp_footer', [self::class, 'output_pixel_js']);

        // Main Commerce Events
        add_action('woocommerce_thankyou', [self::class, 'track_purchase_event']);
        add_action('woocommerce_order_status_payment-received', [self::class, 'track_purchase_event']);
        add_action('woocommerce_order_status_confirmed', [self::class, 'track_lead_event']);
        add_action('woocommerce_order_status_pending-payment', [self::class, 'track_lead_event']);
        add_action('woocommerce_order_status_returned', [self::class, 'track_refund_event']);
        add_action('woocommerce_order_status_refunded', [self::class, 'track_refund_event']);
        add_action('woocommerce_order_status_cancelled', [self::class, 'track_refund_event']);

        // Engagement/Browsing Events
        add_action('woocommerce_after_single_product', [self::class, 'view_content_event']);
        add_action('woocommerce_after_cart', [self::class, 'add_to_cart_event']);
        add_action('woocommerce_before_checkout_form', [self::class, 'initiate_checkout_event']);
        add_action('woocommerce_register_form', [self::class, 'lead_event']);
        add_action('woocommerce_created_customer', [self::class, 'complete_registration_event']);
        add_action('woocommerce_product_loop_start', [self::class, 'maybe_search_event']);
        add_action('woocommerce_thankyou', [self::class, 'maybe_subscribe_event'], 20);
    }

    // PIXEL BASE
    public static function output_pixel_js() {
        $pixel_id = get_option('woo_easy_life_fb_pixel_id');
        if (!$pixel_id) return;
        ?>
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?php echo esc_js($pixel_id); ?>');
        fbq('track', 'PageView');
        </script>
        <noscript>
        <img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=<?php echo esc_attr($pixel_id); ?>&ev=PageView&noscript=1"/>
        </noscript>
        <?php
    }

    // ----- COMMERCE EVENTS -----
    public static function track_purchase_event($order_id) {
        self::fire_order_event($order_id, 'Purchase');
    }
    public static function track_lead_event($order_id) {
        self::fire_order_event($order_id, 'AddPaymentInfo');
    }
    public static function track_refund_event($order_id) {
        self::fire_order_event($order_id, 'Refund', true);
    }

    private static function fire_order_event($order_id, $event_type = 'Purchase', $is_refund = false) {
        $order = wc_get_order($order_id);
        if (!$order) return;

        $event_id = $event_type . '_' . $order->get_id() . '_' . time();
        $data = self::get_order_event_data($order, $event_type, $event_id, $is_refund);

        // Pixel (browser)
        self::pixel_event_js($event_type, $data['pixel_data']);

        // CAPI (server-side)
        if (function_exists('WooEasyLife\Admin\Manager::send_facebook_capi_event')
            && get_option('woo_easy_life_fb_pixel_server')) {
            \WooEasyLife\Admin\Manager::send_facebook_capi_event($event_type, $data['capi_data']);
        }
    }

    // ----- EVENT DATA BUILDER -----
    private static function get_order_event_data($order, $event_type, $event_id, $is_refund = false) {
        $items = [];
        $content_ids = [];
        $total = $order->get_total();
        $is_digital = false;
        $is_subscribe = false;

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) continue;
            $pid = $product->get_id();
            $items[] = [
                'id' => $pid,
                'name' => $product->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => $product->get_price(),
                'category' => wc_get_product_category_list($pid, ','),
                'type' => $product->get_type(),
            ];
            $content_ids[] = $pid;
            if ($product->is_virtual() || $product->is_downloadable()) $is_digital = true;
            if (stripos($product->get_name(), 'subscribe') !== false) $is_subscribe = true;
        }

        $num_items = array_sum(array_column($items, 'quantity'));
        $content_type = $is_subscribe ? 'subscription' : ($is_digital ? 'digital_product' : 'product');
        $pixel_total = $is_refund ? -$total : $total;

        // Pixel data (browser)
        $pixel_data = [
            'value' => (float)$pixel_total,
            'currency' => $order->get_currency(),
            'contents' => $items,
            'content_ids' => $content_ids,
            'content_type' => $content_type,
            'num_items' => $num_items,
            'order_id' => $order->get_id(),
            'eventID' => $event_id
        ];

        // CAPI data (server-side)
        $user_data = self::get_user_data_from_order($order);
        $capi_data = [
            'event_id' => $event_id,
            'user_data' => $user_data,
            'custom_data' => [
                'currency' => $order->get_currency(),
                'value'    => $is_refund ? -$total : $total,
                'order_id' => $order->get_id(),
                'contents' => $items,
                'content_type' => $content_type,
                'num_items' => $num_items,
            ]
        ];

        return compact('pixel_data', 'capi_data');
    }

    // PIXEL JS OUTPUT
    private static function pixel_event_js($event, $params = []) {
        ?>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof fbq === 'function') {
                fbq('track', <?php echo wp_json_encode($event); ?>, <?php echo wp_json_encode($params); ?>);
            }
        });
        </script>
        <?php
    }

    // USER MATCH DATA (EMQ)
    private static function get_user_data_from_order($order) {
        $email = $order->get_billing_email();
        $phone = preg_replace('/\\D/', '', $order->get_billing_phone());
        $fn = $order->get_billing_first_name();
        $ln = $order->get_billing_last_name();
        $city = $order->get_billing_city();
        $zip = $order->get_billing_postcode();
        $country = $order->get_billing_country();

        $user_data = [];
        if ($email)   $user_data['em']      = hash('sha256', trim(strtolower($email)));
        if ($phone)   $user_data['ph']      = hash('sha256', $phone);
        if ($fn)      $user_data['fn']      = hash('sha256', trim(strtolower($fn)));
        if ($ln)      $user_data['ln']      = hash('sha256', trim(strtolower($ln)));
        if ($city)    $user_data['ct']      = hash('sha256', trim(strtolower($city)));
        if ($zip)     $user_data['zip']     = hash('sha256', trim(strtolower($zip)));
        if ($country) $user_data['country'] = hash('sha256', trim(strtolower($country)));
        return $user_data;
    }

    // PRODUCT/BROWSE EVENTS
    public static function view_content_event() {
        if (!is_product()) return;
        global $product;
        if (!$product) return;
        $params = [
            'content_ids'   => [$product->get_id()],
            'content_name'  => $product->get_name(),
            'content_type'  => $product->is_virtual() || $product->is_downloadable() ? "digital_product" : "product",
            'value'         => (float)$product->get_price(),
            'currency'      => get_woocommerce_currency()
        ];
        self::pixel_event_js('ViewContent', $params);
    }

    public static function add_to_cart_event() {
        if (!is_cart()) return;
        $cart = WC()->cart;
        $items = [];
        $content_ids = [];
        $num_items = 0;
        $cart_total = 0;
        if ($cart && $cart->get_cart()) {
            foreach ($cart->get_cart() as $cart_item) {
                $product = $cart_item['data'];
                $pid = $product->get_id();
                $items[] = [
                    'id' => $pid,
                    'name' => $product->get_name(),
                    'quantity' => $cart_item['quantity'],
                    'price' => $product->get_price(),
                    'category' => wc_get_product_category_list($pid, ','),
                    'type' => $product->get_type(),
                ];
                $content_ids[] = $pid;
                $num_items += $cart_item['quantity'];
                $cart_total += $product->get_price() * $cart_item['quantity'];
            }
        }
        $params = [
            'contents'      => $items,
            'content_ids'   => $content_ids,
            'content_type'  => 'product',
            'num_items'     => $num_items,
            'value'         => $cart_total,
            'currency'      => get_woocommerce_currency()
        ];
        self::pixel_event_js('AddToCart', $params);
    }

    public static function initiate_checkout_event() {
        if (!is_checkout()) return;
        $cart = WC()->cart;
        $items = [];
        $content_ids = [];
        $num_items = 0;
        $cart_total = 0;
        if ($cart && $cart->get_cart()) {
            foreach ($cart->get_cart() as $cart_item) {
                $product = $cart_item['data'];
                $pid = $product->get_id();
                $items[] = [
                    'id' => $pid,
                    'name' => $product->get_name(),
                    'quantity' => $cart_item['quantity'],
                    'price' => $product->get_price(),
                    'category' => wc_get_product_category_list($pid, ','),
                    'type' => $product->get_type(),
                ];
                $content_ids[] = $pid;
                $num_items += $cart_item['quantity'];
                $cart_total += $product->get_price() * $cart_item['quantity'];
            }
        }
        $params = [
            'contents'      => $items,
            'content_ids'   => $content_ids,
            'content_type'  => 'product',
            'num_items'     => $num_items,
            'value'         => $cart_total,
            'currency'      => get_woocommerce_currency()
        ];
        self::pixel_event_js('InitiateCheckout', $params);
    }

    public static function lead_event() {
        self::pixel_event_js('Lead', []);
    }

    public static function complete_registration_event($customer_id) {
        self::pixel_event_js('CompleteRegistration', []);
        if (get_option('woo_easy_life_fb_pixel_server')) {
            $user = get_user_by('ID', $customer_id);
            if ($user) {
                // Get meta fields if they exist
                $phone = get_user_meta($user->ID, 'billing_phone', true);
                $fn = get_user_meta($user->ID, 'billing_first_name', true);
                $ln = get_user_meta($user->ID, 'billing_last_name', true);
                $city = get_user_meta($user->ID, 'billing_city', true);
                $zip = get_user_meta($user->ID, 'billing_postcode', true);
                $country = get_user_meta($user->ID, 'billing_country', true);

                $user_data = [
                    'em' => hash('sha256', trim(strtolower($user->user_email))),
                ];
                if ($phone)   $user_data['ph'] = hash('sha256', preg_replace('/\\D/', '', $phone));
                if ($fn)      $user_data['fn'] = hash('sha256', trim(strtolower($fn)));
                if ($ln)      $user_data['ln'] = hash('sha256', trim(strtolower($ln)));
                if ($city)    $user_data['ct'] = hash('sha256', trim(strtolower($city)));
                if ($zip)     $user_data['zip'] = hash('sha256', trim(strtolower($zip)));
                if ($country) $user_data['country'] = hash('sha256', trim(strtolower($country)));

                $event_data = [
                    'event_id' => 'registration_' . $user->ID . '_' . time(),
                    'user_data' => $user_data,
                ];
                if (function_exists('WooEasyLife\Admin\Manager::send_facebook_capi_event')) {
                    \WooEasyLife\Admin\Manager::send_facebook_capi_event('CompleteRegistration', $event_data);
                }
            }
        }
    }

    public static function maybe_search_event() {
        if (!isset($_GET['s']) || empty($_GET['s'])) return;
        $query = sanitize_text_field($_GET['s']);
        self::pixel_event_js('Search', ['search_string' => $query]);
    }

    public static function maybe_subscribe_event($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if ($product && (stripos($product->get_name(), 'subscribe') !== false || $product->is_virtual() || $product->is_downloadable())) {
                $event_id = 'subscribe_' . $order->get_id() . '_' . time();
                $params = [
                    'content_ids'   => [$product->get_id()],
                    'value'         => (float)$order->get_total(),
                    'currency'      => $order->get_currency(),
                    'order_id'      => $order->get_id(),
                    'eventID'       => $event_id
                ];
                self::pixel_event_js('Subscribe', $params);

                if (function_exists('WooEasyLife\Admin\Manager::send_facebook_capi_event')
                    && get_option('woo_easy_life_fb_pixel_server')) {
                    $user_data = self::get_user_data_from_order($order);
                    $event_data = [
                        'event_id'   => $event_id,
                        'user_data'  => $user_data,
                        'custom_data'=> [
                            'currency'    => $order->get_currency(),
                            'value'       => $order->get_total(),
                            'order_id'    => $order->get_id(),
                            'content_ids' => [$product->get_id()],
                        ]
                    ];
                    \WooEasyLife\Admin\Manager::send_facebook_capi_event('Subscribe', $event_data);
                }
            }
        }
    }
}