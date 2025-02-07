import axios from "axios";
import { localApiBaseURL, baseUrl } from "./api/init";

export { baseUrl };

export const checkHasNewOrder = async () => {
  const { data } = await axios.get(
    `${localApiBaseURL}/check-new-orders-for-notification`
  );
  return data;
};

// functions for order list
export const getPaymentMethods = async () => {
  return await axios.get(`${localApiBaseURL}/payment-methods`);
};
export const getShippingMethods = async () => {
  return await axios.get(`${localApiBaseURL}/shipping-methods`);
};
export const validateCoupon = async (payload: { coupon_code: string }) => {
  const { data } = await axios.post(
    `${localApiBaseURL}/validate-coupon`,
    payload
  );
  return data;
};
export const getOrderList = async (payload: {
  status?: string;
  per_page?: number;
  page?: number;
  billing_phone?: string;
}) => {
  const { data } = await axios.get(`${localApiBaseURL}/orders`, {
    params: payload,
  });
  return data;
};
export const getOrderStatusListWithCounts = async () => {
  const { data } = await axios.get(`${localApiBaseURL}/status-with-counts`);
  return data;
};
export const updateAddress = async (payload) => {
  return await axios.post(
    `${localApiBaseURL}/update-address/${payload.order_id}`,
    payload
  );
};
export const changeStatus = async (
  payload: {
    order_id: number;
    new_status: string;
  }[]
) => {
  return await axios.post(`${localApiBaseURL}/orders/change-status`, payload);
};

export const getOrderStatuses = async () => {
  const { data } = await axios.get(`${localApiBaseURL}/status-with-counts`);
  return data;
};
export const saveOrderNote = async (payload: {
  order_id: string | number;
  customer_note: string;
  courier_note: string;
  invoice_note: string;
}) => {
  const { data } = await axios.post(
    `${localApiBaseURL}/save-order-notes`,
    payload
  );
  return data;
};
// functions for order list

export const updateCourierData = async (payload: {
  order_id: string | number;
  courier_data: {};
}) => {
  const { data } = await axios.post(
    `${localApiBaseURL}/update-courier-data`,
    payload
  );
  return data;
};

// custom order status
export const createCustomStatus = async (payload) => {
  const { data } = await axios.post(`${localApiBaseURL}/statuses`, payload);
  return data;
};
export const updateCustomStatus = async (payload, id) => {
  const { data } = await axios.put(
    `${localApiBaseURL}/statuses/${id}`,
    payload
  );
  return data;
};
export const getCustomStatusList = async () => {
  const { data } = await axios.get(`${localApiBaseURL}/statuses`);
  return data;
};
export const deleteCustomStatus = async (id: string) => {
  const { data } = await axios.delete(`${localApiBaseURL}/statuses/${id}`);
  return data;
};

// wp_options table CRUD start
export const createOrUpdateWPOption = async (payload: {
  option_name: string;
  data: object;
}) => {
  const { data } = await axios.post(`${localApiBaseURL}/wp-option`, payload);
  return data;
};

export const createOrUpdateWPOptionItem = async (payload: {
  option_name: string;
  key: string;
  value: string;
}) => {
  const { data } = await axios.post(`${localApiBaseURL}/wp-option-item`, null, {
    params: payload,
  });
  return data;
};

export const getWPOption = async (payload: { option_name: string }) => {
  const { data } = await axios.get(`${localApiBaseURL}/wp-option`, {
    params: payload,
  });
  return data;
};

export const getWPOptionItem = async (payload: {
  option_name: string;
  key: string;
}) => {
  const { data } = await axios.get(`${localApiBaseURL}/wp-option-item`, {
    params: payload,
  });
  return data;
};

export const deleteWPOption = async (payload: { option_name: string }) => {
  const { data } = await axios.delete(`${localApiBaseURL}/wp-option`, {
    params: payload,
  });
  return data;
};
// wp_options table CRUD end

// sms config CRUD start
export const getWoocomerceStatuses = async () => {
  const { data } = await axios.get(`${localApiBaseURL}/woo-statuses`);
  return data;
};

export const createSMS = async (payload: {
  status: string;
  message: string;
  message_for: string;
  phone_number: string;
  settings?: object;
  is_active: boolean;
}) => {
  const { data } = await axios.post(`${localApiBaseURL}/sms-config`, payload);
  return data;
};
export const updateSMS = async (payload: {
  status: string;
  message: string;
  message_for: string;
  phone_number: string;
  settings?: object;
  is_active: boolean;
}) => {
  const { data } = await axios.put(
    `${localApiBaseURL}/sms-config/${payload.id}`,
    payload
  );
  return data;
};

export const getSMS = async () => {
  const { data } = await axios.get(`${localApiBaseURL}/sms-config`);
  return data;
};
export const deleteSMS = async (id: number) => {
  const { data } = await axios.delete(`${localApiBaseURL}/sms-config/${id}`);
  return data;
};
// sms config CRUD end

// block list CRUD start
export const ip_phone_or_email_block_bulk_entry = async (
  payload: {
    type: "ip" | "phone_number" | "email";
    ip_phone_or_email: string;
  }[]
) => {
  const { data } = await axios.post(`${localApiBaseURL}/bulk-entry`, payload);
  return data;
};

export const getBlockListData = async () => {
  const { data } = await axios.get(`${localApiBaseURL}/block-list`);
  return data;
};

export const deleteBlockListData = async (id: string | number) => {
  const { data } = await axios.delete(`${localApiBaseURL}/block-list/${id}`);
  return data;
};
// block list CRUD end

// sms history CRUD start
export const createSMSHistory = async (
  payload: {
    phone_number: string;
    message: string;
    status: "sent" | "failed";
  }[]
) => {
  const { data } = await axios.post(`${localApiBaseURL}/sms-history`, payload);
  return data;
};

export const getSMSHistoryData = async () => {
  const { data } = await axios.get(`${localApiBaseURL}/sms-history`);
  return data;
};

export const deleteSMSHistory = async (id: string | number) => {
  const { data } = await axios.delete(`${localApiBaseURL}/sms-history/${id}`);
  return data;
};
// sms history CRUD end

// custom order start
export const getProducts = async (searchKey?: string) => {
  const { data } = await axios.get(
    `${localApiBaseURL}/custom-orders/get-products?search=${searchKey}`
  );
  return data;
};
export const createOrder = async (payload) => {
  const { data } = await axios.post(
    `${localApiBaseURL}/create-custom-order`,
    payload
  );
  return data;
};
export const checkFraudCustomer = async (payload: { phone: string[] }) => {
  const { data } = await axios.post(
    `${localApiBaseURL}/check-fraud-customer`,
    payload
  );
  return data;
};
// custom order end

// abandoned order start
export const getAbandonedOrders = async (date?: {
  start_date: string;
  end_date: string;
}) => {
  const { data } = await axios.post(
    `${localApiBaseURL}/abandoned-orders`,
    date
  );
  return data;
};


export const updateAbandonedOrderStatus = async (
  id: string,
  payload: {
    status: string;
  }
) => {
  const timestamp = new Date().getTime(); // Generate a unique timestamp
  const { data } = await axios.put(
    `${localApiBaseURL}/abandoned-orders/${id}?nocache=${timestamp}`,
    payload
  );
  return data;
};
// abandoned order start


export const includePastNewOrdersToWELPlugin = async () => {
  const timestamp = new Date().getTime(); // Generate a unique timestamp
  const { data } = await axios.put(`${localApiBaseURL}/include-past-new-orders-to-wel-plugin?nocache=${timestamp}`);
  return data;
};

export const includeMissingNewOrdersOfFailedBalanceCut = async () => {
  const timestamp = new Date().getTime(); // Generate a unique timestamp
  const { data } = await axios.put(`${localApiBaseURL}/include-missing-new-orders-for-balance-cut-failed?nocache=${timestamp}`);
  return data;
};
