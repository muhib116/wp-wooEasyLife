import axios from "axios"
import {
    localApiBaseURL
} from './init'
import { getOrderList } from "@/api"


export const getOrderStatusStatistics = async (payload: {
    startDate: string,
    endDate: string
}) => {
    const { data } = await axios.get(`${localApiBaseURL}/order-stats`, { params: payload })
    return data
}

export const getSalesSummary = async (payload: {
    startDate: string,
    endDate: string,
    status: string
}) => {
    const { data } = await axios.get(`${localApiBaseURL}/sales-summary`, { params: payload })
    return data
}

export const getRecentOrders = async (limit?: number) => {
    const { data } = await getOrderList({per_page: limit})
    return data
}
export const getTopSellingProduct = async (limit?: number) => {
    const { data } = await axios.get(`${localApiBaseURL}/top-selling-products?limit=${limit}`)
    return data
}
export const getSalesProgressData = async (date: {start_date: string, end_date:string}) => {
    const { data } = await axios.get(`${localApiBaseURL}/sales-progress`, {
        params: date
    })
    return data
}
export const getOrderProgressData = async (date: {start_date: string, end_date:string}) => {
    const { data } = await axios.get(`${localApiBaseURL}/order-progress`, {
        params: date
    })
    return data
}
export const getOrderSourceData = async (date: {start_date: string, end_date:string}) => {
    const { data } = await axios.get(`${localApiBaseURL}/orders-grouped-by-created-via`, {
        params: date
    })
    return data
}
export const getOrderCycleTimeData = async (date: {start_date: string, end_date:string}) => {
    const { data } = await axios.get(`${localApiBaseURL}/order-cycle-time`, {
        params: date
    })
    return data
}


export const getSalesProgressForTarget = async (payload: {
    start_date: string | Date
    end_date: string | Date
}) => {
    const { data } = await axios.get(`${localApiBaseURL}/sales-progress-by-date`, {
        params: payload
    })
    return data
}

export const getCourierDeliveryReport = async (payload: {
    start_date: string | Date
    end_date: string | Date
}) => {
    const { data } = await axios.get(`${localApiBaseURL}/orders-grouped-by-status`, {
        params: payload
    })
    return data
}

export const getCustomerData = async (payload: {
    start_date: string | Date
    end_date: string | Date
    status: string
}) => {
    const { data } = await axios.get(`${localApiBaseURL}/customer-data`, {
        params: payload
    })
    return data
}