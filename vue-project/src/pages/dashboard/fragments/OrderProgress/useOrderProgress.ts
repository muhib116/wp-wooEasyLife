import { getOrderProgressData } from "@/api/dashboard"
import { ref } from "vue"

export const useOrderProgress = () => {
    const orderProgressData = ref([])
    const isLoading = ref(false)
    const chartKey = ref(Date.now()) // Unique key

    const loadOrderProgressData = async (date: {start_date: string, end_date: string}) => {
        try {
            isLoading.value = true
            const { data } = await getOrderProgressData(date)
            orderProgressData.value = data
        } finally {
            isLoading.value = false
            chartKey.value = Date.now();
        }
    }

    return {
        chartKey,
        isLoading,
        orderProgressData,
        loadOrderProgressData   
    }
}