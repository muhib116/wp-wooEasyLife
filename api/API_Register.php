<?php
namespace WooEasyLife\API;

use WooEasyLife\API\Admin\AbandonedOrderAPI;
use WooEasyLife\API\Admin\NewOrderNotificationAPI;

class API_Register {
    public function __construct() {
        /**
         * API Path
         * /wp-json/wooeasylife/v1/orders
         * method: get
         */
        new \WooEasyLife\API\Admin\OrderListAPI();

        /**
         * API Path
         * /wp-json/wooeasylife/v1/payment-methods
         * method: get
         */
        new \WooEasyLife\API\Admin\PaymentMethodsAPI();

        /**
         * API Path
         * /wp-json/wooeasylife/v1/shipping-methods
         * method: get
         */
        new \WooEasyLife\API\Admin\ShippingMethodsAPI();

        /**
         * API Path
         * /wp-json/wooeasylife/v1/validate-coupon
         * method: get
         */
        new \WooEasyLife\API\Admin\ValidateCouponAPI();

        /**
         * API Path
         * /wp-json/wooeasylife/v1/update-address/{order_id}
         * method: post
         * payload:
         * {
         *       "billing": {
         *           "address_1": "123 Main St",
         *           "city": "New York",
         *           "state": "NY",
         *           "postcode": "10001",
         *           "country": "US",
         *           "email": "customer@example.com",
         *           "phone": "555-1234"
         *       },
         *       "shipping": {
         *           "address_1": "123 Main St",
         *           "city": "New York",
         *           "state": "NY",
         *           "postcode": "10001",
         *           "country": "US"
         *       }
         *   }
         */
        new \WooEasyLife\API\Admin\UpdateAddressAPI();

        /**
         * customer ip, phone block, list, and edit
         */
        // new WooEasyLife\Admin\BlockFakeCustomer();


        new \WooEasyLife\API\Frontend\OTPHandlerAPI();
        new \WooEasyLife\API\Admin\OrderStatisticsAPI();
        new \WooEasyLife\API\Admin\CustomOrderStatusAPI();
        new \WooEasyLife\API\Admin\WPOptionAPI();
        new \WooEasyLife\API\Admin\SMSConfigAPI();
        new \WooEasyLife\API\Admin\BlockListAPI();
        new \WooEasyLife\API\Admin\SMSHistoryAPI();
        new \WooEasyLife\API\Admin\CustomOrderHandleAPI();
        new \WooEasyLife\API\Admin\CourierHandleAPI();
        new \WooEasyLife\API\Admin\NewOrderNotificationAPI();
        new \WooEasyLife\API\Admin\AbandonedOrderAPI();
    }
}