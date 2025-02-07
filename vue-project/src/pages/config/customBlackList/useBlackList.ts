import { getBlockListData, deleteBlockListData } from "@/api"
import { onMounted, ref } from "vue"

export const useBlackList = () => {

    const isLoading = ref(false)
    const alertMessage = ref({
        message: '',
        type: ''
    })
    const blackListData = ref([])

    const loadBlackListData = async () => {
        try {
            isLoading.value = true
            const { data } = await getBlockListData()
            blackListData.value = data
        } finally {
            isLoading.value = false
        }
    }

    const removeFromBlacklist = async (id: string | number, btn) => {
        if(!confirm("Are you sure to remove?")) return
        try {
            isLoading.value = true
            btn.isLoading = true
            const { data } = await deleteBlockListData(id)
            blackListData.value = data
            await loadBlackListData()
        } finally {
            isLoading.value = false
            btn.isLoading = false
        }
    }

    onMounted(() => {
        loadBlackListData()
    })

    return {
        isLoading,
        blackListData,
        alertMessage,
        removeFromBlacklist
    }
}