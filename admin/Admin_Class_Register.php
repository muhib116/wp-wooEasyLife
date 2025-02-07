<?php
namespace WooEasyLife\Admin;

class Admin_Class_Register{
    public function __construct()
    {
        new ShowCustomerFraudDataToOrderDetailsPage();
        new AddCustomColumnInOrderList();
        new StatusChangeAction();
        new StoreStatusChangeHistory();
    }
}