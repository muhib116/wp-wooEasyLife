<?php
namespace WooEasyLife\Frontend;

class Frontend_Class_Register{
    public function __construct()
    {
        new OTPValidatorForOrderPlace();
        new IP_block();
        new OrderBlockForBlockedUser();
        new Order_limit();
        new TrackAbandonCart();
        new CheckoutFormValidation();
        new CustomerHandler();
        new Remote_UsePackageHistory();
    }
}