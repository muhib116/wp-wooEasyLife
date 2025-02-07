import { getOrderSourceData } from "@/api/dashboard"
import { ref } from "vue"

export const useOrderSource = () => {
    const orderSourceData = ref([])
    const isLoading = ref(false)
    const chartKey = ref(Date.now()) // Unique key

    const loadOrderSourceData = async (date: {start_date: string, end_date: string}) => {
        try {
            isLoading.value = true
            const { data } = await getOrderSourceData(date)
            orderSourceData.value = data
        } finally {
            isLoading.value = false
            chartKey.value = Date.now();
        }
    }

    return {
        chartKey,
        isLoading,
        orderSourceData,
        loadOrderSourceData   
    }
}