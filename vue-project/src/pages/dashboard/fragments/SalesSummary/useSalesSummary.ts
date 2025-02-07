import { getSalesSummary } from "@/api/dashboard"
import { ref } from "vue"

export const useSalesSummary = () => {
    const salesSummaryData = ref<{
        total_sale_amount: string,
        total_discount_amount: string,
        total_sale: string,
        total_shipping_cost: string
    }>()
    const isLoading = ref(false)
    const chartKey = ref(Date.now()) // Unique key

    const loadSalesSummaryData = async (date: {start_date: string, end_date: string}, status: string) => {
        try {
            isLoading.value = true
            const { data } = await getSalesSummary({...date, status})
            salesSummaryData.value = data
        } finally {
            isLoading.value = false
            chartKey.value = Date.now();
        }
    }

    return {
        isLoading,
        salesSummaryData,
        loadSalesSummaryData   
    }
}