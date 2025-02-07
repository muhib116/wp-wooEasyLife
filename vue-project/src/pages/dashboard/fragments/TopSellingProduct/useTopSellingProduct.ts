import { getTopSellingProduct } from "@/api/dashboard"
import { onMounted, ref } from "vue"


export const useTopSellingProduct = () => {
    const topSellingProducts = ref([])
    const isLoading = ref(false)
    
    const productLimit = ref(10)
    const productLimitOptions = [
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
    
    const loadTopSellingProduct = async (limit:number=10) => {
        try {
            isLoading.value = true
            const { data } = await getTopSellingProduct(limit)
            topSellingProducts.value = data
        } finally {
            isLoading.value = false
        }
    }


    onMounted(() => {
        loadTopSellingProduct()
    })
    return {
        isLoading,
        productLimit,
        topSellingProducts,
        productLimitOptions,
        loadTopSellingProduct   
    }
}