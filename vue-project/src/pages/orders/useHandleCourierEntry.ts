import { steadfastBulkOrderCreate } from "@/remoteApi";
import { getSteadFastPayload, getSteadFastResponsePayload } from "./payload";
import { storeBulkRecordsInToOrdersMeta } from "@/api/courier";
import { showNotification } from "@/helper";
import type { Ref } from 'vue';

// Type definitions
interface CourierOrder {
    id: number | string;
    invoice: string;
    recipient_name: string;
    recipient_phone: string;
    recipient_address: string;
    cod_amount: string | number;
    note?: string | null;
}

interface SteadFastResponse extends CourierOrder {
    consignment_id: string | null;
    tracking_code: string | null;
    status: string | null;
    error: string | string[] | null;
    created_at: string;
    updated_at: string;
}

type CourierPartner = 'steadfast' | 'pathao' | 'paperfly' | 'redx';

type SteadFastErrorType = 
    | 'THIS_INVOICE_ALREADY_EXISTS'
    | 'INVALID_PHONE_NUMBER'
    | 'INVALID_ADDRESS'
    | 'INSUFFICIENT_BALANCE'
    | 'INVALID_COD_AMOUNT'
    | string;

interface ErrorMessage {
    type: SteadFastErrorType;
    message: string;
}

// Error messages mapping
const ERROR_MESSAGES: Record<string, (order: SteadFastResponse) => string> = {
    'THIS_INVOICE_ALREADY_EXISTS': (order) => 
        `Invoice ${order.invoice} already exists in Steadfast. Please use a different invoice number.`,
    'INVALID_PHONE_NUMBER': (order) => 
        `Invalid phone number for invoice ${order.invoice}: ${order.recipient_phone}`,
    'INVALID_ADDRESS': (order) => 
        `Invalid address for invoice ${order.invoice}`,
    'INSUFFICIENT_BALANCE': (order) => 
        `Insufficient balance in Steadfast account for invoice ${order.invoice}`,
    'INVALID_COD_AMOUNT': (order) => 
        `Invalid COD amount for invoice ${order.invoice}`,
};

/**
 * Main function to manage courier operations
 */
export const manageCourier = async (
    selectedOrders: Ref<any[]>, 
    courierPartner: CourierPartner, 
    cb?: Function
): Promise<void> => {
    const handlers: Record<CourierPartner, Function> = {
        steadfast: handleSteadfast,
        pathao: handlePathao,
        paperfly: handlePaperfly,
        redx: handleRedx,
    };

    const handler = handlers[courierPartner];
    if (handler) {
        await handler(selectedOrders, cb);
    } else {
        console.error(`Unknown courier partner: ${courierPartner}`);
        showNotification({
            type: 'danger',
            message: `Unsupported courier partner: ${courierPartner}`,
        });
    }
};

/**
 * Handle Steadfast courier submission
 */
const handleSteadfast = async (
    selectedOrders: Ref<any[]>, 
    cb?: Function
): Promise<void> => {
    try {
        // Prepare payload
        const payload = getSteadFastPayload([...selectedOrders.value]);
        
        if (!payload?.orders || payload.orders.length === 0) {
            showNotification({
                type: 'warning',
                message: 'No orders to submit.',
            });
            return;
        }

        // Call Steadfast API
        const { data, status } = await steadfastBulkOrderCreate(payload);
        
        if (!status || !data) {
            showNotification({
                type: 'danger',
                message: 'Steadfast API call failed. Please try again.',
            });
            return;
        }

        // Process results
        const { successfulOrders, failedOrders, hasErrors } = processOrderResults(data);
        
        // Store successful orders
        if (successfulOrders.length > 0) {
            await storeSuccessfulOrders(successfulOrders);
        }
        
        // Show notifications based on results
        showResultNotifications(successfulOrders.length, failedOrders.length, hasErrors);
        
        // Execute callback
        if (cb && typeof cb === 'function') {
            await cb();
        }
        
    } catch (error: any) {
        console.error('Steadfast API error:', error);
        showNotification({
            type: 'danger',
            message: error?.message || 'Steadfast API call failed. Check console for details.',
            duration: 5000,
        });
    }
};

/**
 * Process order results and separate successful from failed
 */
const processOrderResults = (data: SteadFastResponse[]): {
    successfulOrders: SteadFastResponse[];
    failedOrders: SteadFastResponse[];
    hasErrors: boolean;
} => {
    const successfulOrders: SteadFastResponse[] = [];
    const failedOrders: SteadFastResponse[] = [];
    let hasErrors = false;

    data.forEach(order => {
        if (order.error) {
            hasErrors = true;
            failedOrders.push(order);
            handleOrderError(order);
        } else {
            successfulOrders.push(order);
        }
    });

    return { successfulOrders, failedOrders, hasErrors };
};

/**
 * Handle individual order errors
 */
const handleOrderError = (order: SteadFastResponse): void => {
    const errorCodes = parseErrorCodes(order.error);
    
    errorCodes.forEach(errorCode => {
        const cleanError = errorCode.trim().replace(/^["']|["']$/g, '');
        const messageGenerator = ERROR_MESSAGES[cleanError];
        
        const message = messageGenerator 
            ? messageGenerator(order)
            : `Error for invoice ${order.invoice}: ${cleanError}`;
        
        showNotification({
            type: 'danger',
            message,
            duration: 5000,
        });
    });
};

/**
 * Parse error codes from various formats
 */
const parseErrorCodes = (error: string | string[] | null): string[] => {
    if (!error) return [];
    
    try {
        if (typeof error === 'string') {
            // Try to parse JSON string like "[\"ERROR_CODE\"]"
            try {
                const parsed = JSON.parse(error);
                return Array.isArray(parsed) ? parsed : [parsed];
            } catch {
                // If not JSON, treat as plain string
                return [error];
            }
        }
        
        if (Array.isArray(error)) {
            return error;
        }
        
        return [String(error)];
    } catch (e) {
        console.error('Error parsing error codes:', e);
        return [String(error)];
    }
};

/**
 * Store successful orders in database
 */
const storeSuccessfulOrders = async (successfulOrders: SteadFastResponse[]): Promise<void> => {
    try {
        const responsePayload = getSteadFastResponsePayload(successfulOrders);
        await storeBulkRecordsInToOrdersMeta(responsePayload);
    } catch (error) {
        console.error('Error storing orders:', error);
        showNotification({
            type: 'warning',
            message: 'Orders submitted but failed to save locally. Please refresh the page.',
            duration: 5000,
        });
    }
};

/**
 * Show appropriate notifications based on results
 */
const showResultNotifications = (
    successCount: number, 
    failCount: number, 
    hasErrors: boolean
): void => {
    if (successCount > 0 && !hasErrors) {
        // All successful
        showNotification({
            type: 'success',
            message: 'Your order information has been submitted to the courier platform.',
        });
    } else if (successCount > 0 && hasErrors) {
        // Partial success
        showNotification({
            type: 'success',
            message: `${successCount} order(s) submitted successfully.`,
        });
        showNotification({
            type: 'warning',
            message: `${failCount} order(s) failed. Check error messages above.`,
            duration: 5000,
        });
    } else if (successCount === 0 && hasErrors) {
        // All failed
        showNotification({
            type: 'danger',
            message: 'All orders failed to submit. Please check the errors above.',
            duration: 5000,
        });
    }
};

/**
 * Check for errors in Steadfast response (Legacy - kept for compatibility)
 * @deprecated Use processOrderResults instead
 */
const checkForSteadFastErrors = (data: SteadFastResponse[]): boolean => {
    if (!Array.isArray(data)) return false;
    
    let hasErrors = false;
    
    data.forEach(order => {
        if (order.error) {
            hasErrors = true;
            handleOrderError(order);
        }
    });
    
    return hasErrors;
};

/**
 * Handle Pathao courier submission
 * TODO: Implement Pathao integration
 */
const handlePathao = async (
    selectedOrders: Ref<any[]>, 
    cb?: Function
): Promise<void> => {
    console.log('Pathao integration - Coming soon');
    showNotification({
        type: 'info',
        message: 'Pathao integration is not yet available.',
    });
};

/**
 * Handle Paperfly courier submission
 * TODO: Implement Paperfly integration
 */
const handlePaperfly = async (
    selectedOrders: Ref<any[]>, 
    cb?: Function
): Promise<void> => {
    console.log('Paperfly integration - Coming soon');
    showNotification({
        type: 'info',
        message: 'Paperfly integration is not yet available.',
    });
};

/**
 * Handle RedX courier submission
 * TODO: Implement RedX integration
 */
const handleRedx = async (
    selectedOrders: Ref<any[]>, 
    cb?: Function
): Promise<void> => {
    console.log('RedX integration - Coming soon');
    showNotification({
        type: 'info',
        message: 'RedX integration is not yet available.',
    });
};