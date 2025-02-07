import { getRecentOrders } from "@/api/dashboard"
import { onMounted, ref } from "vue"

export const useRecentOrder = () => {
    const recentOrders = ref([])
    const isLoading = ref(false)
    const orderLimit = ref(10)
    const orderLimitOptions = [
        {
            id: 10,
            title: 10,
        },
        {
            id: 15,
            title: 15,
        },
        {
            id: 20,
            title: 20,
        },
        {
            id: 25,
            title: 25,
        },
        {
            id: 30,
            title: 30,
        },
    ]


    const loadRecentOrders = async (limit:number=10) => {
        try {
            isLoading.value = true
            const data = await getRecentOrders(limit)
            recentOrders.value = data
        } finally {
            isLoading.value = false
        }
    }

    onMounted(() => {
        loadRecentOrders()
    })
    return {
        isLoading,
        recentOrders,
        orderLimit,
        orderLimitOptions,
        loadRecentOrders   
    }
}