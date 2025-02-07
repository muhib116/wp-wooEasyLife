import axios from "axios"
import {
    localApiBaseURL
} from './init'

export const storeBulkRecordsInToOrdersMeta =  async (payload: {
    order_id: string | number
    invoice: string | number
    recipient_name: string
    recipient_phone: string
    recipient_address: string
    cod_amount: number | string
    partner: string
    consignment_id: string
    status: string
    tracking_code: string
    parcel_tracking_link: string | null
    created_at: string
    updated_at: string
}[]) => {
    const { data } = await axios.post(`${localApiBaseURL}/courier-data/bulk`, payload)
    return data
}

export const storeRecordInToOrdersMeta =  async (payload: {
    order_id: number | string,
    tracking_code: string,
    invoice: string,
    partner: string,
    consignment_id: string,
    status: string,
    parcel_tracking_link: string,
    created_at:string,
    updated_at: string
}) => {
    const { data } = await axios.post(`${localApiBaseURL}/courier-data`, payload)
    return data
}