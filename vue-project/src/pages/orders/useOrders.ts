import { computed, inject, onMounted, ref, watch } from "vue";
import {
  changeStatus,
  getOrderList,
  getOrderStatusListWithCounts,
  getWoocomerceStatuses,
  ip_phone_email_or_device_block_bulk_entry,
  checkFraudCustomer,
  updateCourierData,
  includePastNewOrdersToWELPlugin,
  includeMissingNewOrdersOfFailedBalanceCut,
  toggleIsDone,
  toggleIsFollowing,
  updateOrder,
  updateShippingMethod,
  getShippingMethods,
  getPaymentMethods
} from "@/api";
import { updateCourierDataBulk, changeStatusBulk } from "@/api/courier"

import { manageCourier } from "./useHandleCourierEntry";
import { filterOrderById, formatInvoice, normalizePhoneNumber, printProductDetails, showNotification } from "@/helper";
import { steadfastBulkStatusCheck } from "@/remoteApi";
import { storeBulkRecordsInToOrdersMeta } from "@/api/courier";
import { useRoute } from "vue-router";
import {
    shippingMethods, 
    paymentMethods
} from "@/storage"

export const useOrders = () => {
  const route = useRoute();

  interface Order {
    id: string | number;
    courier_data?: {
      consignment_id?: string;
      status?: string;
      invoice?: string;
    };
    billing_address?: {
      phone?: string;
      email?: string;
    };
    customer_report?: {
      success_rate?: string;
    };
    customer_custom_data?: {
      fraud_score?: string;
      first_name?: string;
      last_name?: string;
      phone?: string;
      address?: string;
    };
    product_info?: {
      total?: number;
    };
    status?: string;
    is_done?: boolean | number | string;
    need_follow?: boolean | number | string;
    customer_device_token?: string;
    customer_ip?: string;
    [key: string]: any;
  }
  let timeoutId: any = null;
  const orders = ref<Order[]>([]);
  const totalRecords = ref(0);
  const orderStatusWithCounts = ref([]);
  const activeOrder = ref();
  const selectedOrders = ref<Set<Order>>(new Set<Order>());
  const isShippingEditing = ref(false);
  const selectAll = ref(false);
  const isLoading = ref(false);
  const orderListLoading = ref(false);
  const showInvoices = ref(false);
  const toggleNewOrder = ref(false);
  const wooCommerceStatuses = ref([]);
  const selectedStatus = ref(null);
  const { userData, loadUserData } = inject('useServiceProvider');

  const courierStatusInfo = {
    pending: "Consignment is not delivered or cancelled yet.",
    delivered_approval_pending: "Consignment is delivered but waiting for admin approval.",
    partial_delivered_approval_pending: "Consignment is delivered partially and waiting for admin approval.",
    cancelled_approval_pending: "Consignment is cancelled and waiting for admin approval.",
    unknown_approval_pending: "Unknown Pending status. Need contact with the support team.",
    delivered: "Consignment is delivered and balance added.",
    partial_delivered: "Consignment is partially delivered and balance added.",
    cancelled: "Consignment is cancelled and balance updated.",
    hold: "Consignment is held.",
    in_review: "Order is placed and waiting to be reviewed.",
    unknown: "Unknown status. Need contact with the support team.",
  };

  const orderFilter = ref({
    page: 1,
    per_page: 20,
    status: "",
    search: "",
    is_done: undefined as boolean | undefined,
    need_follow: undefined as boolean | undefined,
  });

  // --- START: New code for DSP Filter ---
  const selectedDspFilter = ref(0); // Default to 'All' (0)
  const dspFilterOptions = ref([
    { label: 'All Probabilities', value: 0 },
    { label: '>= 90%', value: 90 },
    { label: '>= 80%', value: 80 },
    { label: '>= 70%', value: 70 },
    { label: '>= 60%', value: 60 },
    { label: '>= 50%', value: 50 },
  ]);

  const loadPaymentMethods = async () => {
    try {
      const { data } = await getPaymentMethods();
      paymentMethods.value = data;
    } catch (error) {
      console.error("Error loading payment methods:", error);
    }
  }

  const getDeliveryProbability = (order) => {
    let successRate = order?.customer_report?.success_rate;
    if (isNaN(parseFloat(successRate))) {
      successRate = '0';
    }
    const courierSuccessRate = parseFloat(successRate.replace('%', '')) || 0;
    const systemFraudScore = parseFloat(order?.customer_custom_data?.fraud_score) || 0;
    let probability = courierSuccessRate / 100;
    if (systemFraudScore > 80) probability *= 0.5;
    else if (systemFraudScore > 50) probability *= 0.7;
    else if (systemFraudScore > 20) probability *= 0.9;
    probability = Math.max(0, Math.min(probability * 100, 100));
    return Math.round(probability) || 'Unpredicted';
  }

  const filteredOrders = computed(() => {
    if (selectedDspFilter.value === 0) {
      return orders.value; // If 'All' is selected, return all orders.
    }
    if (!orders.value || orders.value.length === 0) {
      return [];
    }
    return orders.value.filter(order => {
      const probability = getDeliveryProbability(order);
      if (typeof probability !== 'number') {
        return false;
      }
      return probability >= selectedDspFilter.value;
    });
  });
  // --- END: New code for DSP Filter ---

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
      clearSelectedOrders()
    }
  }

  const clearSelectedOrders = () => {
    selectedOrders.value.clear();
  }

  const loadShippingMethods = async () => {
    const { data: _shippingMethods } = await getShippingMethods();
    shippingMethods.value = _shippingMethods
  }

  const handleFraudCheck = async (button: any, order?: any) => {
    if (!order && ![...selectedOrders.value].length) {
      alert("Please select at least one item.");
      return;
    }
    let _selectedOrders = [...selectedOrders.value];
    if (order) {
      _selectedOrders = [order];
    }
    const chunkSize = 10;
    const orderChunks: any[] = [];
    for (let i = 0; i < _selectedOrders.length; i += chunkSize) {
      orderChunks.push(_selectedOrders.slice(i, i + chunkSize));
    }
    const processChunks = async (index = 0) => {
      if (index >= orderChunks.length) return;
      orderChunks[index].forEach((item) => { item.fraudDataLoading = true; });
      const payload = { data: orderChunks[index].map((item) => ({ id: item.id, phone: normalizePhoneNumber(item.billing_address.phone) })) };
      try {
        const { data } = await checkFraudCustomer(payload);
        if (data.length) {
          data.forEach((item) => {
            _selectedOrders.forEach((_item) => { if (item.id === _item.id) _item.customer_report = item.report; });
          });
        }
      } catch (error) {
        console.error("API Error:", error);
      } finally {
        orderChunks[index].forEach((item) => { item.fraudDataLoading = false; });
      }
      await processChunks(index + 1);
    };
    try {
      button.isLoading = true;
      await processChunks();
    } finally {
      button.isLoading = false;
    }
  };

  const getOrders = async (shouldClear: boolean = true) => {
    try {
      isLoading.value = true;
      orderListLoading.value = true;
      if (orderFilter.value.page == 0) orderFilter.value.page = 1;
      const { data, total } = await getOrderList(orderFilter.value);
      orders.value = data;
      totalRecords.value = total;
      if (shouldClear) selectedOrders.value.clear();
    } finally {
      isLoading.value = false;
      orderListLoading.value = false;
    }
  };

  const handleUpdateShippingMethod = async (payload: { shipping_instance_id: string | number; order_id: string | number; }) => {
    try {
      isLoading.value = true;
      await updateShippingMethod(payload);
      await getOrders();
      showNotification({ type: 'success', message: 'Shipping method updated.' });
    } finally {
      isLoading.value = false;
    }
  }

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

  const handleFilter = async (status: string, btn) => {
    try {
      btn.isLoading = true
      orderFilter.value.status = status
      await getOrders()
    } finally {
      btn.isLoading = false
    }
  }

  const handlePhoneNumberBlock = async (btn, reloadOrders: boolean = true) => {
    if (![...selectedOrders.value].length) {
      alert("Please select at least one item.");
      return { success: false, errors: ['No order selected'] };
    }
    const payload = [...selectedOrders.value].map((item) => ({ type: "phone_number", ip_phone_email_or_device: item?.billing_address?.phone }));
    const errors: string[] = [];
    let blockCount = 0;
    try {
      btn.isLoading = true;
      const response = await ip_phone_email_or_device_block_bulk_entry(payload);

      (response || []).forEach(({ status, message }) => {
        showNotification({ type: status === 'success' ? "success" : "danger", message });
        if (status === 'success') blockCount++;
        else errors.push(message);
      });

      if (reloadOrders) {
        await getOrders();
      }
      return { success: errors.length === 0, errors, blockCount };
    } catch (err) {
      errors.push('Phone number block failed.');
      showNotification({ type: "danger", message: 'Phone number block failed.' });
      return { success: false, errors, blockCount };
    } finally {
      btn.isLoading = false;
    }
  };

  const handleEmailBlock = async (btn, reloadOrders: boolean = true) => {
    if (![...selectedOrders.value].length) {
      alert("Please select at least one item.");
      return { success: false, errors: ['No order selected'] };
    }
    const payload = [...selectedOrders.value].map((item) => {
      if (!item?.billing_address?.email) {
        showNotification({ type: 'warning', message: 'Email is missing.' });
        return;
      } else {
        return { type: "email", ip_phone_email_or_device: item?.billing_address?.email };
      }
    }).filter(Boolean);
    const errors: string[] = [];
    let blockCount = 0;
    try {
      btn.isLoading = true;
      const response = await ip_phone_email_or_device_block_bulk_entry(payload);

      if (reloadOrders) {
        (response || []).forEach(({ status, message }) => {
          showNotification({ type: status === 'success' ? "success" : "danger", message });
          if (status === 'success') blockCount++;
          else errors.push(message);
        });
        await getOrders();
      }
      return { success: errors.length === 0, errors, blockCount };
    } catch (err) {
      errors.push('Email block failed.');
      showNotification({ type: "danger", message: 'Email block failed.' });
      return { success: false, errors, blockCount };
    } finally {
      btn.isLoading = false;
    }
  };

  const handleDeviceBlock = async (btn, reloadOrders: boolean = true) => {
    if (!selectedOrders.value.size) {
      showNotification({ type: 'warning', message: 'Please select at least one order to block the device.' });
      return { success: false, errors: ['No order selected'] };
    }

    const payload = [...selectedOrders.value].map((item) => {
      if (!item?.customer_device_token) {
        showNotification({ type: 'warning', message: 'Device token is missing. May be custom order.' });
        return;
      } else {
        return { type: "device_token", ip_phone_email_or_device: item?.customer_device_token }
      }
    }).filter(Boolean);

    const errors: string[] = [];
    let blockCount = 0;
    try {
      btn.isLoading = true;
      const response = await ip_phone_email_or_device_block_bulk_entry(payload);
      if (reloadOrders) {
        (response || []).forEach(({ status, message }) => {
          showNotification({ type: status === 'success' ? "success" : "danger", message });
          if (status === 'success') blockCount++;
          else errors.push(message);
        });
        await getOrders();
      }
      return { success: errors.length === 0, errors, blockCount };
    } catch (err) {
      errors.push('Device block failed.');
      showNotification({ type: "danger", message: 'Device block failed.' });
      return { success: false, errors, blockCount };
    } finally {
      btn.isLoading = false;
    }
  };

  const handleIPBlock = async (btn, reloadOrders: boolean = true) => {
    if (![...selectedOrders.value].length) {
      alert("Please select at least one item.");
      return { success: false, errors: ['No order selected'] };
    }
    const payload = [...selectedOrders.value].map((item) => ({ type: "ip", ip_phone_email_or_device: item?.customer_ip }));
    const errors: string[] = [];
    let blockCount = 0;
    try {
      btn.isLoading = true;
      const response = await ip_phone_email_or_device_block_bulk_entry(payload);

      if (reloadOrders) {
        (response || []).forEach(({ status, message }) => {
          showNotification({ type: status === 'success' ? "success" : "danger", message });
          if (status === 'success') blockCount++;
          else errors.push(message);
        });
        await getOrders();
      }
      return { success: errors.length === 0, errors, blockCount };
    } catch (err) {
      errors.push('IP block failed.');
      showNotification({ type: "danger", message: 'IP block failed.' });
      return { success: false, errors, blockCount };
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
      const payload = [...selectedOrders.value].map((item) => ({ new_status: selectedStatus.value, order_id: item?.id }));
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

    if ([...selectedOrders.value].length > 500 && courierPartner == 'steadfast') {
      showNotification({
        type: 'warning',
        message: 'Maximum 500 items are allowed.'
      })
    }

    try {
      btn.isLoading = true;
      await manageCourier(selectedOrders, courierPartner, async () => {
        await loadOrderStatusList();
        await getOrders();
      });
    } catch ({ response }) {
      const { status, message } = response?.data;
      if (!status) {
        showNotification({ type: "warning", message: message });
      }
    } finally {
      btn.isLoading = false;
    }
  };

  const updateConsignmentIdInOrder = async (order: { id: any; courier_data: any; }) => {
    if (timeoutId) clearTimeout(timeoutId);
    timeoutId = setTimeout(async () => {
      try {
        isLoading.value = true;
        const payload = {
          order_id: order.id,
        courier_data: order.courier_data
      };
        await updateCourierData(payload);
        showNotification({ type: 'success', message: 'Consignment ID updated successfully.' });
      } catch (err) {
        showNotification({ type: 'danger', message: 'Failed to update Consignment ID.' });
      } finally {
        isLoading.value = false;
      }
    }, 600);
  }

  const refreshBulkCourierData = async (btn, courierPartner = 'steadfast') => {
    try {
      btn.isLoading = true;
      let courierData = selectedOrders.value && selectedOrders.value.size ? Array.from(selectedOrders.value) : (orders.value || []);

      courierData = courierData.filter(item => {
        const inv = item?.courier_data?.invoice;
        return typeof inv === 'string' ? inv.trim().length > 0 : Boolean(inv);
      });

      // Prepare payload: consignment_ids if available, otherwise invoice_ids
      const payload = {
        consignment_ids: courierData
          .filter(item => item && item.courier_data && item.courier_data.consignment_id && item.courier_data.consignment_id !== 'not-available')
          .map(item => item.courier_data.consignment_id),
        invoice_ids: courierData
          .filter(item => item && (!item.courier_data?.consignment_id || item.courier_data.consignment_id === 'not-available'))
          .map(item => formatInvoice(item.id || ''))
      };

      // If nothing to refresh, show warning and exit
      if (
        (!payload.consignment_ids.length && !payload.invoice_ids.length)
      ) {
        showNotification({ type: "warning", message: "There is no data available to refresh." });
        return;
      }

      // Fetch updated statuses from courier API
      const { data: statuses } = await steadfastBulkStatusCheck(payload);

      // Prepare bulk update arrays
      const courierDataUpdates = [];
      const statusUpdates = [];
      let metaNeedsUpdate = false;

      for (const order of courierData) {
        let orderId = order.id;
        // Determine the key to use for status lookup
        const statusKey = order.courier_data?.consignment_id
          ? order.courier_data.consignment_id
          : formatInvoice(orderId);

        let courierUpdatedStatus = statuses[statusKey] || 'unknown';

        if (courierUpdatedStatus) {
          // If courier entry data is missing, store it
          if (!order.courier_data?.consignment_id) {
            storeBulkRecordsInToOrdersMeta([{
              order_id: order.id,
              invoice: formatInvoice(order.id || ''),
              recipient_name: (order.customer_custom_data.first_name || '') + (order.customer_custom_data.last_name || ''),
              recipient_phone: order.customer_custom_data.phone,
              recipient_address: order.customer_custom_data.address,
              cod_amount: order.product_info.total,
              partner: courierPartner,
              status: courierUpdatedStatus
            }]);
            metaNeedsUpdate = true;
            continue;
          }

          // Collect for bulk update
          order.courier_data.status = courierUpdatedStatus;
          courierDataUpdates.push({ order_id: order.id, courier_data: order.courier_data });

          let statusName = get_status(courierUpdatedStatus);
          statusUpdates.push({ order_id: order.id, new_status: statusName });
          order.status = typeof statusName === 'string' ? statusName.replace('wc-', '') : '';
        }
      }

      // Bulk update API calls
      if (courierDataUpdates.length) {
        await updateCourierDataBulk(courierDataUpdates);
      }
      if (statusUpdates.length) {
        try {
          await changeStatusBulk(statusUpdates);
        } catch (err) {
          console.error(err);
        }
      }

      // If any meta was updated for orders without consignment_id, refresh UI
      if (metaNeedsUpdate) {
        await loadOrderStatusList();
        await getOrders();
        showNotification({ type: "success", message: "Order data synced with courier platform." });
      }

      showNotification({ type: "success", message: "Courier data refresh done." });
      await loadOrderStatusList();
      await getOrders();
    } finally {
      btn.isLoading = false;
    }
  };

  const get_status = (courier_status: string): string => {
    const statuses = { in_review: "wc-courier-entry", pending: "wc-courier-hand-over", cancelled: "wc-returned", unknown: "wc-unknown", delivered_approval_pending: "wc-pending", delivered: "wc-completed", hold: "wc-on-hold" };
    return statuses[courier_status];
  }

  const markAsDone = async (order, btn: { isLoading: boolean }) => {
    const isDone = Number(!Number(order.is_done));
    if (!isDone && !confirm('Are you sure to make this undone?')) return
    btn.isLoading = true;
    const payload = { order_id: order.id, is_done: isDone };
    try {
      await toggleIsDone(payload);
      order.is_done = isDone;
      showNotification({ type: isDone ? 'success' : 'warning', message: `Marked as ${isDone ? 'done!' : 'undone'}` });
    } catch (err) {
      console.error(err);
      showNotification({ type: 'danger', message: 'Something went wrong!' });
    } finally {
      btn.isLoading = false;
    }
  }

  const markAsFollowing = async (order, btn: { isLoading: boolean }) => {
    const need_follow = Number(!Number(order.need_follow));
    if (!need_follow && !confirm('Are you sure ?')) return
    btn.isLoading = true;
    const payload = { order_id: order.id, need_follow: need_follow };
    try {
      await toggleIsFollowing(payload);
      order.need_follow = need_follow;
      showNotification({ type: need_follow ? 'success' : 'warning', message: `Marked as ${need_follow ? 'following!' : 'normal'}` });
    } catch (err) {
      console.error(err);
      showNotification({ type: 'danger', message: 'Something went wrong!' });
    } finally {
      btn.isLoading = false;
    }
  }

  const handleUpdateOrder = async (product, btn) => {
    const payload = { order_id: activeOrder.value.id, product_id: product.id, quantity: product.product_quantity };
    try {
      btn.isLoading = true
      if (product.from == 'new-product') {
        payload.quantity++;
        product.product_quantity = payload.quantity;
      }
      const response = await updateOrder(payload);
      await getOrders();
      if (response) {
        const updatedOrder = filterOrderById(activeOrder.value.id, orders.value)
        setActiveOrder(updatedOrder)
      }
    } catch (err) {
      console.error(err);
    } finally {
      btn.isLoading = false;
    }
  }

  const totalPages = computed(() => orderFilter.value.per_page ? Math.ceil(totalRecords.value / orderFilter.value.per_page) : 1);
  const debouncedGetOrders = (btn) => {
    orderFilter.value.page = orderFilter.value.page > totalPages.value ? totalPages.value : orderFilter.value.page
    clearTimeout(timeoutId)
    timeoutId = setTimeout(async () => {
      btn.isLoading = true
      await getOrders()
      btn.isLoading = false
    }, 500)
  }

  const currentPage = computed(() => orderFilter.value.page > totalPages.value ? totalPages.value : orderFilter.value.page);


  const include_past_new_orders_thats_not_handled_by_wel_plugin = async (totalNewOrders: number, btn: { isLoading: boolean }) => {
    let alertMsg = `Are you sure you want to include your past new orders? \nIf you confirm, a total of ${totalNewOrders} will be deducted from your balance.`;
    if (!confirm(alertMsg)) return
    if (totalNewOrders > userData.value.remaining_order) {
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

  const include_balance_cut_failed_new_orders = async (totalNewOrders: Number, btn: { isLoading: boolean }) => {
    let alertMsg = `Are you sure you want to include your missing new orders? \nIf you confirm, a total of ${totalNewOrders} will be deducted from your balance.`;
    if (!confirm(alertMsg)) return
    if (totalNewOrders > userData.value.remaining_order) {
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

  const handleLabelPrint = (invoiceLogo, btn) => {
    if (orders.value.length === 0) {
      showNotification({
        type: 'warning',
        message: 'No orders available for label printing.'
      });
      return;
    }

    const printableOrders = orders.value.filter(order => {
      return (!Number.parseInt(order?.is_done) && order?.courier_data?.consignment_id);
    });

    if (!printableOrders?.length) {
      showNotification({
        type: 'warning',
        message: 'No printable orders found. Please ensure orders are not marked as done and have valid courier data.'
      });
      return;
    }

    let index = 0
    const printNextOrder = () => {
      if (index < printableOrders.length) {
        const order = printableOrders[index];
        printProductDetails(order, async () => {
          await markAsDone(order, btn)
          index++;
          printNextOrder();
        }, invoiceLogo);
      }
    };

    printNextOrder();

  }

  watch(() => selectedOrders, (newVal) => { selectAll.value = selectedOrders.value.size === orders.value.length; }, { deep: true });

  onMounted(async () => {
    if (route.query.status) {
      orderFilter.value.status = String(route.query.status);
    }
    loadOrderStatusList();
    loadAllStatuses();
    await loadShippingMethods();
    await loadPaymentMethods();
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
    shippingMethods,
    orderListLoading,
    courierStatusInfo,
    isShippingEditing,
    wooCommerceStatuses,
    orderStatusWithCounts,
    getOrders,
    markAsDone,
    markAsFollowing,
    handleFilter,
    handleIPBlock,
    setActiveOrder,
    setSelectedOrder,
    toggleSelectAll,
    handleFraudCheck,
    handleEmailBlock,
    handleDeviceBlock,
    handleUpdateOrder,
    handleStatusChange,
    debouncedGetOrders,
    handleCourierEntry,
    loadOrderStatusList,
    clearSelectedOrders,
    handlePhoneNumberBlock,
    refreshBulkCourierData,
    getDeliveryProbability,
    handleUpdateShippingMethod,
    include_balance_cut_failed_new_orders,
    include_past_new_orders_thats_not_handled_by_wel_plugin,
    loadPaymentMethods,
    handleLabelPrint,
    updateConsignmentIdInOrder,
    paymentMethods,
    selectedDspFilter,
    dspFilterOptions,
    filteredOrders
  };
};