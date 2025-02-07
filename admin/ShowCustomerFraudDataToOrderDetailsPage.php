<?php
namespace WooEasyLife\Admin;

class ShowCustomerFraudDataToOrderDetailsPage {
    public function __construct()
    {
        add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'add_custom_heading_after_order_details']);
    }

    function add_custom_heading_after_order_details($order) {
        $billing_phone = $order->get_billing_phone();
        if(empty($billing_phone)) return;

        // Output the custom heading
        $fraud_payload = [
            "data" => [
                [
                    'id' => 1,
                    'phone' => $billing_phone
                ]
            ]
        ];

        $fraud_data = getCustomerFraudData($fraud_payload);
        $fraud_data = $fraud_data[0];

        if (is_wp_error($fraud_data)) {
            echo '<p>Error: ' . esc_html($fraud_data->get_error_message()) . '</p>';
        } else {
        //---------
    ?>
            <style> 
                .fraud-history-container {
                    text-align: center;
                    width: 490px;
                    margin: 20px auto;
                    text-align: center;
                    background-color: #ffffff;
                    border-radius: 4px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    padding: 6px;
                    overflow: hidden;
                    font-family: Arial, sans-serif;
                    border: 1px solid #4442;
                    font-size: 14px;
                }
                .fraud-history-container .fraud-history-table .text-center{
                    text-align: center;
                }
                .fraud-history-container .fraud-history-title {
                    font-size: 16px !important;
                    color: #333333 !important;
                    margin-top: 10px !important;
                    margin-bottom: 15px !important;
                }
                .fraud-history-container .fraud-history-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .fraud-history-container .fraud-history-table thead th {
                    background-color: #374151;
                    color: #f3f4f6;
                    font-weight: 400;
                    padding: 12px;
                    border-bottom: 1px solid #ddd;
                }
                .fraud-history-container .fraud-history-table tbody td {
                    padding: 10px 12px;
                    border-bottom: 1px solid #eee;
                }
                .fraud-history-container .fraud-history-table tbody tr:last-child td {
                    border-bottom: none;
                }
                .fraud-history-container .progress-bar{
                    background: red;
                    height: 15px;
                    margin: 25px 0 25px;
                    position: relative;
                    div{
                        height: 100%;
                        width: 10%;
                        background: #22c55d;
                        position: relative;
                        ::before {
                            content: '';
                            position: absolute;
                            width: 6px;
                            height: 6px;
                            bottom: calc(100% - 3px);
                            left: calc(50% - 3px);
                            background: #fff;
                            border: 1px solid #3334;
                            border-right: none;
                            border-bottom: none;
                            rotate: 45deg;
                        }
                    }
                    span{
                        position: absolute;
                        font-size: 10px;
                        background: #22c55d;
                        bottom: 100%;
                        margin-bottom: 4px;
                        left: calc(100% - 30px);
                        border: 1px solid #3334;
                        padding: 1px 4px;
                        border-radius: 2px;
                        color: white;
                    }
                    span.cancel {
                        bottom: unset;
                        top: 100%;
                        margin-top: 4px;
                        left: unset;
                        right: 10px;
                        background: #ef4444;
                        z-index: 9999;
                    }
                    span::before {
                        content: '';
                        position: absolute;
                        width: 6px;
                        height: 6px;
                        bottom: calc(100% - 3px);
                        left: calc(50% - 3px);
                        background: #22c55d;
                        border: 1px solid #3334;
                        border-left: none;
                        border-top: none;
                        rotate: 45deg;
                        top: calc(100% - 3px);
                    }
                    span.cancel::before {
                        background: #ef4444;
                        border: 1px solid #3334;
                        border-right: none;
                        border-bottom: none;
                        top: unset;
                        bottom: calc(100% - 3px);
                    }
                }
            </style>

            <div class="fraud-history-container">
                <h2 class="fraud-history-title">
                    <?php
                        if(isset($fraud_data) && $fraud_data['report']['success_rate'] == '100%'){
                            echo '🎉 The number has no fraud history! ✅';
                        }
                    ?>
                </h2>

            <?php 
                if($fraud_data && $fraud_data['report']['total_order'] > 0) { 
                    $success_rate = isset($fraud_data['report']['success_rate']) ? htmlspecialchars($fraud_data['report']['success_rate'], ENT_QUOTES, 'UTF-8') : 0;
                //------
            ?>
                    <table class="fraud-history-table">
                        <thead>
                            <tr class="header-row">
                                <th>Courier Name</th>
                                <th>Confirm</th>
                                <th>Cancel</th>
                                <th>Success Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fraud_data['report']['courier'] as $item) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-center" style="background: #dcfce7;">
                                        <?php echo htmlspecialchars($item['report']['confirmed'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="text-center" style="background: #fee2e1;">
                                        <?php echo htmlspecialchars($item['report']['cancel'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="text-center" style="background: #e0f2fe;">
                                        <?php echo htmlspecialchars($item['report']['success_rate'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfooter>
                            <tr class="total-row table_footer" style="font-weight: bold;">
                                <td class="text-center" style="background: #374151; color: #fff;">Total</td>
                                <td class="text-center" style="background: #22c55d; color: #fff;">
                                    <?php echo isset($fraud_data['report']['confirmed']) ? htmlspecialchars($fraud_data['report']['confirmed'], ENT_QUOTES, 'UTF-8') : 0; ?>
                                </td>
                                <td class="text-center" style="background: #ef4444; color: #fff;">
                                    <?php echo $fraud_data['report']['cancel']; ?>
                                </td>
                                <td class="text-center" style="background: #0ca5e9; color: #fff;">
                                    <?php echo $success_rate; ?>
                                </td>
                            </tr>
                        <tfooter>
                    </table>

                    <div class="progress-bar">
                        <div style="width: <?php echo $success_rate; ?>">
                            <span>
                                <?php echo $success_rate; ?>
                            </span>
                        </div>

                        <?php if(100 - (int)$success_rate){ ?>
                            <span class="cancel">
                                <?php echo 100 - (int)$success_rate; ?>%
                            </span>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php if($fraud_data && $fraud_data['report']['total_order'] == 0) { ?>
                    <div>
                        <h3 style="font-weight:bold; font-size: 20px; margin-bottom: 16px; text-align: center;">
                            🎉 The number has no data! ✅
                        </h3>
                    </div>
                <?php } ?>
            </div>

        <?php
        }
    }
}


