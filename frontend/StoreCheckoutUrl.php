<?php
namespace WooEasyLife\Frontend;

class StoreCheckoutUrl {
    public function __construct()
    {
        // add_action('woocommerce_thankyou', [$this, 'store_referrer_url_on_order'], 10, 1);
    }
    // Hook into WooCommerce order completion to store the referrer URL
    function store_referrer_url_on_order($order_id) {
        // Get the order object
        $order = wc_get_order($order_id);
    
        // Get the referrer URL from the HTTP_REFERER server variable
        $referrer_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    
        // If a referrer URL is found, store it in the order's metadata
        if (!empty($referrer_url)) {
            // Save the referrer URL in order meta
            $order->update_meta_data('_referrer_url', $referrer_url);
    
            // Optionally, save it for later use in the order completion email or anywhere else
            $order->save(); // Save the order object to persist changes
        }
    }
}