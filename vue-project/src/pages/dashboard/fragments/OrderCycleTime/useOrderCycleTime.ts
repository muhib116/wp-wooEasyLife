import { getOrderCycleTimeData } from "@/api/dashboard"
import { ref } from "vue"

export const useOrderCycleTime = () => {
    const orderCycleTimeData = ref([])
    const isLoading = ref(false)
    const chartKey = ref(Date.now()) // Unique key

    const loadOrderCycleTimeData = async (date: {start_date: string, end_date: string}) => {
        try {
            isLoading.value = true
            const { data } = await getOrderCycleTimeData(date)
            orderCycleTimeData.value = data
        } finally {
            isLoading.value = false
            chartKey.value = Date.now();
        }
    }

    return {
        chartKey,
        isLoading,
        orderCycleTimeData,
        loadOrderCycleTimeData   
    }
}