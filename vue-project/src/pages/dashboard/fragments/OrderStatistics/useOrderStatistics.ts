import { getOrderStatusStatistics } from "@/api/dashboard"
import { inject, onMounted, ref } from "vue"
import { getOrderStatuses } from "@/api"

export const useOrderStatistics = () => {
    const {
        orderStatuses
    } = inject('useDashboard');
    
    const isLoading = ref(false)
    const chartKey = ref(Date.now()) // Unique key
    const orderStatistics = ref({})

    
    const loadOrderStatisticsData = async (date: {start_date: string, end_date: string}) => {
        try {
            isLoading.value = true
            const { data } = await getOrderStatusStatistics(date)
            orderStatistics.value = data
        } finally {
            isLoading.value = false
        }
    }

    return {
        chartKey,
        isLoading,
        orderStatuses,
        orderStatistics,
        loadOrderStatisticsData
    }
}