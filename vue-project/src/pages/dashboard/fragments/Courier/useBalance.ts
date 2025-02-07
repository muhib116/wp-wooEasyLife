import { checkCourierBalance } from "@/remoteApi"
import { ref } from "vue"

export const useBalance = () => {
    const courierBalanceData = ref()
    const isLoading = ref(false)

    const loadCourierBalance = async () => {
        try {
            isLoading.value = true
            const { data } = await checkCourierBalance()
            courierBalanceData.value = data
        } finally {
            isLoading.value = false
        }
    }

    return {
        isLoading,
        courierBalanceData,
        loadCourierBalance 
    }
}