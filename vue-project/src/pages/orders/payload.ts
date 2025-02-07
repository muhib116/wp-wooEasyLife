import { normalizePhoneNumber } from "@/helper"

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
    const payload: SteadFastPayload = {
        orders: selectedOrders
        .map(item => {
            const shipping_address = `${item?.shipping_address?.address_1 || ''} ${item?.shipping_address?.address_2 || ''}`
            const billing_address = `${item?.billing_address?.address_1 || ''} ${item?.billing_address?.address_2 || ''}`
            return {
                invoice: item?.id,
                recipient_name: item?.customer_name || '',
                recipient_phone: normalizePhoneNumber(item?.shipping_address?.phone || item?.billing_address?.phone || ''),
                recipient_address: shipping_address || billing_address,
                cod_amount: item?.total,
                note: item?.order_notes?.courier_note || '',
            }
        })
    }

    return payload
}
export const getSteadFastResponsePayload = (data) => {
    return data.map(item => {
        return {
            order_id: item.invoice,
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