import { getCourierDeliveryReport } from "@/api/dashboard"
import { ref } from "vue"

export const useDeliveryReport = () => {
    const courierDeliveryData = ref()
    const isLoading = ref(false)

    const loadCourierDeliveryData = async (date: {
        start_date: string
        end_date: string
    }) => {
        try {
            isLoading.value = true
            const { data } = await getCourierDeliveryReport(date)
            courierDeliveryData.value = data
        } finally {
            isLoading.value = false
        }
    }

    return {
        isLoading,
        courierDeliveryData,
        loadCourierDeliveryData 
    }
}