import { computed, inject, onMounted, ref, watch } from "vue";
import {
  changeStatus,
  getOrderList,
  getOrderStatusListWithCounts,
  getWoocomerceStatuses,
  ip_phone_or_email_block_bulk_entry,
  checkFraudCustomer,
  updateCourierData,
  includePastNewOrdersToWELPlugin,
  includeMissingNewOrdersOfFailedBalanceCut,
  toggleIsDone,
  updateOrder
} from "@/api";
import { manageCourier } from "./useHandleCourierEntry";
import { normalizePhoneNumber, showNotification } from "@/helper";
import { steadfastBulkStatusCheck } from "@/remoteApi";
import { isEmpty } from "lodash";

export const useOrders = () => {
  const orders = ref([]);
  const totalRecords = ref(0);
  const orderStatusWithCounts = ref([]);
  const activeOrder = ref();
  const selectedOrders = ref(new Set([]));
  const selectAll = ref(false);
  const isLoading = ref(false);
  const showInvoices = ref(false);
  const toggleNewOrder = ref(false);
  const wooCommerceStatuses = ref([]);
  const selectedStatus = ref(null);
  const { userData, loadUserData } = inject('useServiceProvider')
  const courierStatusInfo = {
    pending: "Consignment is not delivered or cancelled yet.",
    delivered_approval_pending:
      "Consignment is delivered but waiting for admin approval.",
    partial_delivered_approval_pending:
      "Consignment is delivered partially and waiting for admin approval.",
    cancelled_approval_pending:
      "Consignment is cancelled and waiting for admin approval.",
    unknown_approval_pending:
      "Unknown Pending status. Need contact with the support team.",
    delivered: "Consignment is delivered and balance added.",
    partial_delivered: "Consignment is partially delivered and balance added.",
    cancelled: "Consignment is cancelled and balance updated.",
    hold: "Consignment is held.",
    in_review: "Order is placed and waiting to be reviewed.",
    unknown: "Unknown status. Need contact with the support team.",
  };

  const orderFilter = ref({
    page: 1,
    per_page: 30,
    status: "",
    search: "",
  });

  const setActiveOrder = (item) => {
    activeOrder.value = item;
  };
  const setSelectedOrder = (item) => {
    if (!selectedOrders.value.has(item)) {
      selectedOrders.value.add(item);
    } else {
      selectedOrders.value.delete(item);
    }
  };

  const toggleSelectAll = () => {
    if (selectAll.value) {
      selectedOrders.value = new Set(orders.value);
    } else {
      selectedOrders.value.clear();
    }
  };
  
  const handleFraudCheck = async (button) => {
    if (![...selectedOrders.value].length) {
      alert("Please select at least one item.");
      return;
    }

    const _selectedOrders = [...selectedOrders.value];
    const chunkSize = 10; // Process 10 orders at a time
    const orderChunks = [];

    // Step 1: Slice `_selectedOrders` into chunks of 10
    for (let i = 0; i < _selectedOrders.length; i += chunkSize) {
      orderChunks.push(_selectedOrders.slice(i, i + chunkSize));
    }

    // Step 2 & 3: Process each chunk sequentially
    const processChunks = async (index = 0) => {
      if (index >= orderChunks.length) return; // Stop when all chunks are processed

      // Enable `fraudDataLoading` for orders in this chunk
      orderChunks[index].forEach((item) => {
        item.fraudDataLoading = true;
      });

      const payload = {
        data: orderChunks[index].map((item) => ({
          id: item.id, // ID for tracking report data
          phone: normalizePhoneNumber(item.billing_address.phone),
        })),
      };

      try {
        const { data } = await checkFraudCustomer(payload);

        if (data.length) {
          data.forEach((item) => {
            _selectedOrders.forEach((_item) => {
              if (item.id === _item.id) {
                _item.customer_report = item.report;
              }
            });
          });
        }
      } catch (error) {
        console.error("API Error:", error);
      } finally {
        // Disable `fraudDataLoading` after API response
        orderChunks[index].forEach((item) => {
          item.fraudDataLoading = false;
        });
      }

      // Process the next chunk
      await processChunks(index + 1);
    };

    try {
      button.isLoading = true;
      await processChunks(); // Start processing the chunks
    } finally {
      button.isLoading = false;
    }
  };

  const getOrders = async (shouldClear: boolean = true) => {
    try {
      isLoading.value = true;
      if (orderFilter.value.page == 0) {
        orderFilter.value.page = 1;
      }
      const { data, total } = await getOrderList(orderFilter.value);
      orders.value = data;
      totalRecords.value = total;
      if (shouldClear) {
        selectedOrders.value.clear();
      }
    } finally {
      isLoading.value = false;
    }
  };

  const loadAllStatuses = async () => {
    try {
      isLoading.value = true;
      const { data } = await getWoocomerceStatuses();
      wooCommerceStatuses.value = data;
    } finally {
      isLoading.value = false;
    }
  };

  const loadOrderStatusList = async () => {
    isLoading.value = true;
    const { data } = await getOrderStatusListWithCounts();
    orderStatusWithCounts.value = data;
    isLoading.value = false;
  };

  const getDeliveryProbability = (order) => {
    // Get success rate and ensure it's a valid number
    let successRate = order?.customer_report?.success_rate;

    if (isNaN(parseFloat(successRate))) {
        successRate = '0'; // Default to 0% if it's an invalid value
    }

    // Remove '%' if present and parse it as a float
    const courierSuccessRate = parseFloat(successRate.replace('%', '')) || 0;

    // Ensure fraud score is a number
    const systemFraudScore = parseFloat(order?.customer_custom_data?.fraud_score) || 0;

    // Normalize success rate to a 0-1 scale
    let probability = courierSuccessRate / 100;

    // Adjust probability based on fraud score
    if (systemFraudScore > 80) {
        probability *= 0.5; // High fraud risk, reduce probability significantly
    } else if (systemFraudScore > 50) {
        probability *= 0.7; // Medium fraud risk, moderate reduction
    } else if (systemFraudScore > 20) {
        probability *= 0.9; // Low fraud risk, slight reduction
    }

    // Ensure probability stays within 0-100%
    probability = Math.max(0, Math.min(probability * 100, 100));

    return Math.round(probability) || 'Unpredicted'; // Return probability as a rounded percentage
}

  const handleFilter = async (status: string, btn) => {
    try {
        btn.isLoading = true
        orderFilter.value.status = status
        await getOrders()
    } finally {
        btn.isLoading = false
    }
  }

  const handlePhoneNumberBlock = async (btn) => {
    if (![...selectedOrders.value].length) {
      alert("Please select at least on item.");
      return;
    }

    const payload: {
      customer_id: string | number;
      type: "phone_number";
      ip_phone_or_email: string;
    }[] = [...selectedOrders.value].map((item) => ({
      type: "phone_number",
      ip_phone_or_email: item?.billing_address?.phone,
    }));

    try {
      btn.isLoading = true;
      await ip_phone_or_email_block_bulk_entry(payload);
      await getOrders();
    } finally {
      btn.isLoading = false;
    }
  };

  const handleEmailBlock = async (btn) => {
    if (![...selectedOrders.value].length) {
      alert("Please select at least on item.");
      return;
    }

    const payload: {
      customer_id: string | number;
      type: "email";
      ip_phone_or_email: string;
    }[] = [...selectedOrders.value].map((item) => ({
      type: "email",
      ip_phone_or_email: item?.billing_address?.email,
    }));

    try {
      btn.isLoading = true;
      await ip_phone_or_email_block_bulk_entry(payload);
      await getOrders();
    } finally {
      btn.isLoading = false;
    }
  };

  const handleIPBlock = async (btn) => {
    if (![...selectedOrders.value].length) {
      alert("Please select at least on item.");
      return;
    }

    const payload: {
      customer_id: string | number;
      type: "ip";
      ip_phone_or_email: string;
    }[] = [...selectedOrders.value].map((item) => ({
      type: "ip",
      ip_phone_or_email: item?.customer_ip,
    }));

    try {
      btn.isLoading = true;
      await ip_phone_or_email_block_bulk_entry(payload);
      await getOrders();
    } finally {
      btn.isLoading = false;
    }
  };

  const handleStatusChange = async (btn) => {
    if (![...selectedOrders.value].length) {
      alert("Please select at least on item.");
      return;
    }

    if (!selectedStatus.value) {
      alert("Please select status from dropdown.");
    }

    try {
      btn.isLoading = true;
      const payload: {
        order_id: number;
        new_status: string;
      }[] = [...selectedOrders.value].map((item) => ({
        new_status: selectedStatus.value,
        order_id: item?.id,
      }));

      await changeStatus(payload);
      loadOrderStatusList();
      await getOrders();
    } catch (err) {
      console.log(err);
    } finally {
      btn.isLoading = false;
    }
  };

  const handleCourierEntry = async (courierPartner: string, btn) => {
    if (![...selectedOrders.value].length) {
      alert("Please select at least on item.");
      return;
    }

    try {
      btn.isLoading = true;
      await manageCourier(selectedOrders, courierPartner, async () => {
        await getOrders();
        showNotification({
          type: "success",
          message: "Your order information has been submitted to the courier platform.",
        })
      });
    } catch ({ response }) {
      const { status, message } = response?.data;
      if (!status) {
        showNotification({
          type: "warning",
          message: message,
        })
      }
    } finally {
      btn.isLoading = false;
    }
  };

  const refreshBulkCourierData = async (btn) => {
    try {
      btn.isLoading = true;
      let courierData = selectedOrders.value?.size
        ? [...selectedOrders.value]
        : orders.value;
      courierData = courierData.filter((item) => !isEmpty(item.courier_data));

      const consignment_ids = courierData.map(
        (item) => item.courier_data.consignment_id
      );
      const payload = {
        consignment_ids: consignment_ids,
      };

      if(!consignment_ids?.length) {
        showNotification({
          type: "warning",
          message: "There is no data available to refresh."
        })
        return
      }

      // status: {consignment_id: string}
      const { data: statuses } = await steadfastBulkStatusCheck(payload);

      orders.value.forEach(async (order) => {
        let orderConsignmentId = order.courier_data.consignment_id;
        let courierUpdatedStatus = statuses[orderConsignmentId];

        if (courierUpdatedStatus) {
          order.courier_data.status = courierUpdatedStatus;

          const { data } = await updateCourierData({
            order_id: order.id,
            courier_data: order.courier_data,
          });

          let statusName = get_status(courierUpdatedStatus)
          try {
            await changeStatus([{
              order_id: order.id,
              new_status: statusName,
            }]);
  
            order.status =  statusName.replace('wc-', '')
          } catch (err) {
            console.error(err)
          }
        }
      })

      showNotification({
        type: "success",
        message: "Courier data refresh done."
      })
    } finally {
      btn.isLoading = false;
    }
  };

  const get_status = (courier_status: string): string => {
    /**
     * Name             Description
     * --------------------------------------
     * pending: Consignment is not delivered or cancelled yet.
     * delivered_approval_pending: Consignment is delivered but waiting for admin approval.
     * partial_delivered_approval_pending: Consignment is delivered partially and waiting for admin approval.
     * cancelled_approval_pending: Consignment is cancelled and waiting for admin approval.
     * unknown_approval_pending: Unknown Pending status. Need contact with the support team.
     * -delivered: Consignment is delivered and balance added.
     * -partial_delivered: Consignment is partially delivered and balance added.
     * -cancelled: Consignment is cancelled and balance updated.
     * -hold: Consignment is held.
     * -in_review: Order is placed and waiting to be reviewed.
     * -unknown: Unknown status. Need contact with the support team.
     */
    const statuses: {
      in_review: string;
      pending: string;
      cancelled: string;
      delivered_approval_pending: string;
      delivered: string;
      hold: string;
      unknown: string;
      cancelled_approval_pending: string;
    } = {
      in_review: "wc-courier-entry",
      pending: "wc-courier-hand-over",
      cancelled: "wc-returned",
      unknown: "wc-unknown",
      delivered_approval_pending: "wc-pending",
      delivered: "wc-complete",
      hold: "wc-on-hold",
    };
    return statuses[courier_status];
  }

  const markAsDone = async (order, btn: { isLoading: boolean }) => 
  {
    const isDone = Number(!Number(order.is_done));
    if(!isDone && !confirm('Are sure to make this undone?')) return
    btn.isLoading = true;
  
    const payload = { order_id: order.id, is_done: isDone };
  
    try {
      await toggleIsDone(payload);
      order.is_done = isDone;
  
      showNotification({
        type: isDone ? 'success' : 'warning',
        message: `Marked as ${isDone ? 'done!' : 'undone'}`,
      });
    } catch (err) {
      console.error(err);
      showNotification({ type: 'danger', message: 'Something went wrong!' });
    } finally {
      btn.isLoading = false;
    }
  }
  
  const handleUpdateOrder = async (product, btn) => {
    const payload: {
      order_id: number | string
      product_id: number | string
      quantity: number
    } = {
      order_id: activeOrder.value.id,
      product_id: product.id,
      quantity: product.product_quantity
    }
    
    try {
      btn.isLoading = true

      if(product.from == 'new-product'){
        payload.quantity ++
        product.product_quantity = payload.quantity
      }
      
      const response = await updateOrder(payload)
      if(response) {
        /**
         * if user set quantity to 0, then remove the item from product list,
         * after removing this product from order in DB
         * updateOrder function remove the product from order if quant set to 0
         */
        activeOrder.value.product_info = response
      }

      getOrders()
    } catch (err) {
      console.error(err)
    } finally {
      btn.isLoading = false
    }
  }

  let timeoutId: any;
  const totalPages = computed(() =>
      orderFilter.value.per_page ? Math.ceil(totalRecords.value / orderFilter.value.per_page) : 1
  )
  const debouncedGetOrders = (btn) => {
    orderFilter.value.page = orderFilter.value.page > totalPages.value ? totalPages.value : orderFilter.value.page
    clearTimeout(timeoutId)
    timeoutId = setTimeout(async () => {
      btn.isLoading = true
      await getOrders()
      btn.isLoading = false
    }, 500)
  }
  // Pagination logic
  const currentPage = computed(() =>
    orderFilter.value.page > totalPages.value ? totalPages.value : orderFilter.value.page
  )

  const include_past_new_orders_thats_not_handled_by_wel_plugin = async (totalNewOrders: number, btn: { isLoading: boolean }) => 
  {
    let alertMsg = `Are you sure you want to include your past new orders? \nIf you confirm, a total of ${totalNewOrders} will be deducted from your balance.`;
    if(!confirm(alertMsg)) return
    if(totalNewOrders > userData.value.remaining_order) {
      showNotification({
        type: 'info',
        message: `
          <h3 class="text-lg">You don’t have enough balance to complete this action.</h3>

          <p>Your current balance is <strong>${userData.value.remaining_order}</strong>, your minimum balance should <strong>${totalNewOrders}</strong>.</p>
          <hr class="border-[currentColor] my-2" />
          <p>
            Recharge your balance and try again!
            <br />
            Thank you.
          </p>

        `
      })
      return;
    }

    try {
      btn.isLoading = true;
      const data = await includePastNewOrdersToWELPlugin();
      showNotification({
        type: 'success',
        message: data.message,
      })
      
      await loadUserData();
      loadOrderStatusList();
      await getOrders();

    } catch (err) {
      console.error("Error including past new orders:", err);
      
      showNotification({
        type: 'danger',
        message: "Failed to update orders. Please try again.",
      }) 
    } 
    finally {
      btn.isLoading = false;
    }
  }

  const include_balance_cut_failed_new_orders = async (totalNewOrders: Number, btn: { isLoading: boolean}) => {
    let alertMsg = `Are you sure you want to include your missing new orders? \nIf you confirm, a total of ${totalNewOrders} will be deducted from your balance.`;
    if(!confirm(alertMsg)) return
    if(totalNewOrders > userData.value.remaining_order) {
      showNotification({
        type: 'info',
        message: `
          <h3 class="text-lg">You don’t have enough balance to complete this action.</h3>

          <p>Your current balance is <strong>${userData.value.remaining_order}</strong>, your minimum balance should <strong>${totalNewOrders}</strong>.</p>
          <hr class="border-[currentColor] my-2" />
          <p>
            Recharge your balance and try again!
            <br />
            Thank you.
          </p>

        `
      })
      return;
    }

    try {
      btn.isLoading = true;
      const data = await includeMissingNewOrdersOfFailedBalanceCut();
      
      showNotification({
        type: 'success',
        message: data.message,
      })
      
      await loadUserData();
      loadOrderStatusList();
      await getOrders();

    } catch (err) {
      console.error("Error including missing new orders:", err);
      
      showNotification({
        type: 'danger',
        message: "Failed to update orders. Please try again.",
      })
    } 
    finally {
      btn.isLoading = false;
    }
  }

  watch(
    () => selectedOrders,
    (newVal) => {
      selectAll.value = selectedOrders.value.size === orders.value.length;
    },
    {
      deep: true,
    }
  );

  onMounted(() => {
    loadOrderStatusList();
    loadAllStatuses();
    getOrders();
  });

  return {
    orders,
    selectAll,
    isLoading,
    totalPages,
    currentPage,
    activeOrder,
    orderFilter,
    showInvoices,
    totalRecords,
    toggleNewOrder,
    selectedStatus,
    selectedOrders,
    courierStatusInfo,
    wooCommerceStatuses,
    orderStatusWithCounts,
    getOrders,
    markAsDone,
    handleUpdateOrder,
    handleFilter,
    handleIPBlock,
    setActiveOrder,
    setSelectedOrder,
    toggleSelectAll,
    handleFraudCheck,
    handleEmailBlock,
    handleStatusChange,
    debouncedGetOrders,
    handleCourierEntry,
    loadOrderStatusList,
    handlePhoneNumberBlock,
    refreshBulkCourierData,
    getDeliveryProbability,
    include_past_new_orders_thats_not_handled_by_wel_plugin,
    include_balance_cut_failed_new_orders
  };
};
