<?php
if(HPOSp()){
    add_filter( 'manage_edit-shop_order_sortable_columns', 'add_custom_order_column' );
}else{
    add_filter( 'manage_woocommerce_page_wc-orders_custom_column', 'add_custom_order_column' );
}

function add_custom_order_column( $columns ) {
    return $columns;
}
