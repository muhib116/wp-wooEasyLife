import { getSalesProgressData } from "@/api/dashboard"
import { ref } from "vue"

export const useSalesProgress = () => {
    const salesProgressData = ref([])
    const isLoading = ref(false)
    const chartKey = ref(Date.now()) // Unique key

    const loadSalesProgressData = async (date: {start_date: string, end_date: string}) => {
        try {
            isLoading.value = true
            const { data } = await getSalesProgressData(date)
            salesProgressData.value = data
        } finally {
            isLoading.value = false
            chartKey.value = Date.now();
        }
    }

    return {
        chartKey,
        isLoading,
        salesProgressData,
        loadSalesProgressData   
    }
}