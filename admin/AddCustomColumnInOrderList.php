<?php
namespace WooEasyLife\Admin;

class AddCustomColumnInOrderList {
    public function __construct()
    {
        // Add custom column order list table. WooCommerce - 7.0.0 version
        add_filter('manage_edit-shop_order_columns', [$this, 'wooeasylife_add_custom_order_column']);
        add_action('manage_shop_order_posts_custom_column', [$this, 'wooeasylife_populate_custom_order_column']);
        
        // Add custom column content order list table. WooCommerce- Latest version
        add_filter('woocommerce_shop_order_list_table_columns', [$this, 'wooeasylife_add_custom_order_column'] );
        add_action( 'woocommerce_shop_order_list_table_custom_column', [$this, 'wooeasylife_populate_custom_order_column'], 10, 2 );
        add_action('admin_footer', [$this, 'wooeasylife_add_preview_popup_html']);
    }

    /**
     * Add a custom column to the WooCommerce Orders table.
     */
    public function wooeasylife_add_custom_order_column($columns) {
        // Insert a new column after the Order Status column
        $new_columns = [];
        foreach ($columns as $key => $column) {
            $new_columns[$key] = $column;
            if ('order_status' === $key) {
                $new_columns['fraud-data'] = __('Fraud Data', 'wooeasylife');
            }
        }
        return $new_columns;
    }

    /**
     * Populate the custom column with data.
     */
    public function wooeasylife_populate_custom_order_column($column, $post_id) 
    {
        $order = wc_get_order($post_id);

        if($column == 'order_number'){
            $isHandledByWELPlugin =  $order->get_meta('is_wel_order_handled');
            if($isHandledByWELPlugin){
                echo "<img 
                        src='https://api.wpsalehub.com/app-logo' 
                        title='Handled by WooEasyLife Plugin'
                        style='height: 15px;display:block;filter: drop-shadow(1px 1px 1px #0002);background: #fff;padding: 2px 4px;border-radius: 4px;'
                    />
                ";
            }
        }

        if ('fraud-data' === $column) {
            global $wpdb;
    
            // Get the order object
            if (!$order) {
                echo __('N/A', 'wooeasylife');
                return;
            }
    
            // Retrieve the customer ID associated with the order
            $billing_phone = $order->get_billing_phone();
            $status = $order->get_status();

            if (!$billing_phone) {
                echo __('Guest Order', 'wooeasylife');
                return;
            }

            $total_order_per_customer_for_current_order_status = get_total_orders_by_billing_phone_or_email_and_status($order);
            if($total_order_per_customer_for_current_order_status>1)
            {
                echo "<button 
                        class='woo_easy_multi_order_btn'
                        style='
                            padding: 1px 5px;
                            background: red;
                            border: none;
                            color: #fff;
                            border-radius: 2px;
                            font-size: 13px;
                            cursor: pointer;
                        '
                        title='Multiple order placed'
                        data-billing_phone='".$billing_phone."'
                        data-order_status='".$status."'
                    >
                        $total_order_per_customer_for_current_order_status
                    </button>
                ";
            }
    
            // Fetch fraud data from the custom table
            $table_name = $wpdb->prefix . __PREFIX.'fraud_customers';
            $fraud_data = $wpdb->get_row(
                $wpdb->prepare("SELECT report FROM $table_name WHERE customer_id = %d", $billing_phone),
                ARRAY_A
            );
    
            if ($fraud_data && isset($fraud_data['report'])) {
                // Decode the JSON report
                $report = json_decode($fraud_data['report'], true);

                if(empty($report)) {
                    echo 'n/a';
                    return;
                }

                $report = $report['report'];
                $success_rate = $report['success_rate'];
                
                $progress_bar = '
                    <style>
                        .fraud-history-container .progress-bar{
                            background: red;
                            height: 3px;
                            margin: 25px 0 25px;
                            position: relative;
                            div{
                                height: 100%;
                                width: 10%;
                                background: #22c55d;
                                position: relative;
                            }

                            .
                        }
                    </style>

                    <div class="fraud-history-container"><div class="progress-bar">
                        <div style="width: '.$success_rate.'"></div>
                        </div>
                    </div>';
                $progress_bar .= "
                    <div
                        style='
                            font-size: 12px;
                            margin-top: -22px;
                            line-height: 16px;
                        '
                    >
                        Total: ".$report['total_order']." | 
                        <span style='color: #22c55d;'>Delivered: ".$report['confirmed']."</span> | 
                        <span style='color: red;'>Canceled: ".$report['cancel']."</span>
                    </div>
                ";

                echo $progress_bar;
            } else {
                echo __('No fraud data found', 'wooeasylife');
            }
        }
    }


    public function wooeasylife_add_preview_popup_html() {
        $current_screen = get_current_screen();
        if ($current_screen && $current_screen->id === 'woocommerce_page_wc-orders') {
            include_once plugin_dir_path(__DIR__) . 'includes/orderList/OrderDetails.php';
        }
    }
    

    /**
    * Make the custom column sortable (optional).
    */
    public function wooeasylife_make_custom_column_sortable($columns) {
        $columns['custom_column'] = 'custom_column';
        return $columns;
    }

    /**
     * Adjust the query for sorting the custom column.
     */
    function wooeasylife_sort_custom_order_column($query) {
        if (!is_admin() || 'shop_order' !== $query->get('post_type')) {
            return;
        }

        $orderby = $query->get('orderby');
        if ('custom_column' === $orderby) {
            $query->set('meta_key', '_custom_meta_key'); // Replace with your custom meta key
            $query->set('orderby', 'meta_value');
        }
    }
}

