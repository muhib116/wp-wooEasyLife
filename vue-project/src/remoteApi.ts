import axios from "axios";
import { createSMSHistory } from "./api";
import { computed } from "vue";
import { normalizePhoneNumber } from "./helper";
import {
  licenseKey,
  isValidLicenseKey,
  licenseAlertMessage,
  redirectToLicensePage
} from '@/service/useServiceProvider'

export const remoteApiBaseURL = "https://api.wpsalehub.com/api";

const headers = computed(() => ({
  headers: {
    Authorization: "Bearer " + licenseKey.value,
  }
}));

// remote function
export const checkFraudCustomer = async (payload: {
  phone?: string;
  data?: { id: number; phone: string }[];
}) => {
  try {
    const _payload = {
      data: payload?.data?.map((item) => ({
        id: item.id,
        phone: normalizePhoneNumber(item.phone),
      })),
    };
    const { data } = await axios.post(
      `${remoteApiBaseURL}/fraud-check`,
      _payload,
      headers.value
    );
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
};

// courier start
export const getCourierCompanies = async () => {
  try {
    const { data } = await axios.post(
      `${remoteApiBaseURL}/courier/list`,
      null,
      headers.value
    );
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
};

export const saveCourierConfig = async (payload: {
  title: "steadfast" | "paperfly" | "steadfast" | "redx";
  logo: "string";
  api_key: "string";
  secret_key: "string";
  is_active: boolean;
}) => {
  try {
    const { data } = await axios.post(
      `${remoteApiBaseURL}/courier/save-configuration`,
      payload,
      headers.value
    );
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
};

export const getCourierConfig = async () => {
  try {
    const { data } = await axios.post(
      `${remoteApiBaseURL}/courier/get-configuration`,
      null,
      headers.value
    );
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
};

export const steadfastBulkOrderCreate = async (payload: {
  orders: {
    invoice: number | string;
    recipient_name: string;
    recipient_phone: string;
    recipient_address: string;
    cod_amount: number | string;
  }[];
}) => {
  try {
    const { data } = await axios.post(
      `${remoteApiBaseURL}/steadfast/create-bulk-order`,
      payload,
      headers.value
    );
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
};

export const steadfastStatusCheck = async (payload: {
  consignment_ids: string;
}) => {
  try {
    const { data } = await axios.post(
      `${remoteApiBaseURL}/steadfast/check-status`,
      payload,
      headers.value
    );
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
};

export const steadfastBulkStatusCheck = async (payload: {
  consignment_ids: string[];
}) => {
  try {
    const { data } = await axios.post(
      `${remoteApiBaseURL}/steadfast/bulk-check-status`,
      payload,
      headers.value
    );
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
};
// courier end

// sms integration start
export const smsRecharge = async (payload: {
  account_number: string,
  total_amount: number,
  total_charge: number,
  transaction_method: string,
  transaction_id: string,
}) => {
  try {
    const { data } = await axios.post(
      `${remoteApiBaseURL}/sms/recharge`,
      payload,
      headers.value
    );
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
}
export const sendSMS = async (payload: {
  phone: string;
  content: string;
  status?: string;
}) => {
  try {
    const { data } = await axios.post(
      `${remoteApiBaseURL}/sms/send`,
      payload,
      headers.value
    );
    await createSMSHistory({
      phone_number: payload.phone,
      message: payload.content,
      status: data.data.response_code == 202 ? payload.status || "" : "failed",
      error_message: data.data.error_message,
    });
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
};
// sms integration end

export const checkCourierStatus = async (
  partnerName: string,
  consignmentId: string
) => {
  try {
    const { data } = await axios.post(
      `${remoteApiBaseURL}/${partnerName.toLowerCase()}/check-status`,
      {
        consignment_id: consignmentId,
      },
      headers.value
    );
    return data;
  } catch (err) {
    
    handleLicenseValidations(err)
  }
};

export const checkCourierBalance = async () => {
  try {
    const { data } = await axios.get(
      `${remoteApiBaseURL}/check-courier-balance`,
      headers.value
    );
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
};

export const getUser = async () => {
  try {
    const { data } = await axios.get(
      `${remoteApiBaseURL}/get-user`,
      headers.value
    );
    isValidLicenseKey.value = true
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
}

export const getTutorials = async () => {
  try {
    const { data } = await axios.get(
      `${remoteApiBaseURL}/get-tutorials`,
      headers.value
    )
    return data;
  } catch (err) {
    handleLicenseValidations(err)
  }
}


const handleLicenseValidations = (err) => 
{
  const msg = err.response?.data?.message.replace('.', '').trim()

  if(msg == 'Invalid domain') {
    handleRedirect(err.response.status)

    licenseKey.value = ''
    isValidLicenseKey.value = false;
    localStorage.removeItem('license_key')

    return 
  }
  
  switch (msg) {
    case 'Expired': 
      licenseAlertMessage.value = {
        title: `
          <strong style="font-size: 18px;">Your License Key Has Expired!</strong>
          <br />
          <p>
              It looks like your license key is no longer active. Don’t worry—we’re here to help! Contact us to renew your key and regain access, or check out our renewal options to get started right away.
          </p>
        `,
        type: 'danger'
      };
    break;

    case 'Invalid Token':
      licenseAlertMessage.value = {
        title: `
          <strong style="font-size: 18px;">Invalid License!</strong>
          <br />
          <p>
              The license key you entered is invalid. Don’t worry—we’re here to assist! Reach out to us to obtain your license key.
          </p>
        `,
        type: 'danger'
      };
    break;

    case 'Unauthenticated':
    case 'Token not found': 
      licenseAlertMessage.value = {
        title: `
          <strong style="font-size: 18px;">License key not found!</strong>
          <br />
          <p>
              The license key you entered is invalid. Don’t worry—we’re here to assist! Reach out to us to obtain your license key.
          </p>
        `,
        type: 'danger'
      };
    break;

    default:
      // Optional: Handle unexpected cases
      licenseAlertMessage.value = {
        title: `
          <strong style="font-size: 18px;">Unknown License Issue</strong>
          <br />
          <p>
              An unknown issue has occurred with your license. Please contact support for assistance.
          </p>
        `,
        type: 'warning'
      };
    break;
  }

  handleRedirect(err.response.status)
}

const handleRedirect = (code) => {
  if(code == 401) {
    redirectToLicensePage()
  }
}
