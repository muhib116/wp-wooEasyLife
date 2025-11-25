import { normalizePhoneNumber, showNotification, formatInvoice } from "@/helper"

type SteadFastPayload = {
    orders: {
        invoice: number | string
        recipient_name: string
        recipient_phone: string
        recipient_address: string
        cod_amount: number | string
    }[]
}


export const getSteadFastPayload = (selectedOrders: SteadFastPayload) => {
    if (!Array.isArray(selectedOrders)) return { orders: [] }; // Safety check

    const orders = selectedOrders.map(item => {
        const {
            id,
            customer_name = '',
            shipping_address = {},
            billing_address = {},
            total = 0,
            order_notes = {}
        } = item || {};

        // Build addresses safely
        const shipping_address_str = [
            shipping_address?.address_1?.trim(),
            shipping_address?.address_2?.trim()
        ].filter(Boolean).join(' ');

        const billing_address_str = [
            billing_address?.address_1?.trim(),
            billing_address?.address_2?.trim()
        ].filter(Boolean).join(' ');

        if (!shipping_address_str && !billing_address_str) {
            showNotification({
                type: 'danger',
                message: 'Address not found.'
            })
        }

        return {
            invoice: formatInvoice(id || ''), // Apply prefix to invoice
            recipient_name: customer_name,
            recipient_phone: normalizePhoneNumber(
                shipping_address?.phone || billing_address?.phone || ''
            ),
            recipient_address: shipping_address_str || billing_address_str,
            cod_amount: total,
            note: order_notes?.courier_note || '',
        };
    });

    return { orders };
}

/**
 * Extract order ID from invoice string
 * Examples:
 * - "LOC-293" -> "293"
 * - "EXM-12345" -> "12345"
 * - "123" -> "123"
 */
const extractOrderId = (invoice: string | number): string => {
    const invoiceStr = String(invoice);
    
    // Check if invoice contains a hyphen (formatted invoice)
    if (invoiceStr.includes('-')) {
        // Split by hyphen and return the last part
        const parts = invoiceStr.split('-');
        return parts[parts.length - 1];
    }
    
    // If no hyphen, return as is
    return invoiceStr;
};
export const getSteadFastResponsePayload = (data) => {
    return data.map(item => {
        return {
            order_id: extractOrderId(item.invoice),
            invoice: item.invoice,
            recipient_name: item.recipient_name,
            recipient_phone: item.recipient_phone,
            recipient_address: item.recipient_address,
            cod_amount: item.cod_amount,
            partner: 'steadfast',
            consignment_id: item.consignment_id,
            status: item.status,
            tracking_code: item.tracking_code,
            parcel_tracking_link: item.tracking_code ? `https://steadfast.com.bd/t/${item.tracking_code}` : null,
            created_at: item.created_at,
            updated_at: item.updated_at
        }
    })
}