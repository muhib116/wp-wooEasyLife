<?php
namespace WooEasyLife\Admin;

class StoreStatusChangeHistory {
    public function __construct()
    {
        add_action('woocommerce_order_status_changed', [$this, 'store_status_history'], 10, 4);
    }

    public function store_status_history ($order_id, $from_status, $to_status, $order) {
        $history = $order->get_meta('_status_history', true);
        if (empty($history)) {
            $history = [];
        }
    
        $history[] = [
            'status' => $to_status,
            'date'   => current_time('mysql'),
        ];
    
        $order->update_meta_data('_status_history', $history);
        $order->save();
    }
}