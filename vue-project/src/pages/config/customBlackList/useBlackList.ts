import { getBlockListData, deleteBlockListData } from "@/api"
import { onMounted, ref } from "vue"

export const useBlackList = () => {

    const isLoading = ref(false)

    const selectAll = ref(false)
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
        if (!confirm("Are you sure to remove?")) return
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


    const toggleSelectAll = () => {
        blackListData.value.forEach(item => {
            item.isSelected = selectAll.value
        })
    }

    const handleBulkDelete = async () => {
        const selectedIds = blackListData.value
            .filter(item => item.isSelected)
            .map(item => item.id)

        if (selectedIds.length === 0) {
            alertMessage.value = {
                message: 'No items selected for deletion.',
                type: 'error'
            }
            return
        }

        if (!confirm(`Are you sure you want to remove ${selectedIds.length} selected items?`)) {
            return
        }

        try {
            isLoading.value = true
            await Promise.all(selectedIds.map(async id => {
                await deleteBlockListData(id)
            }))
            await loadBlackListData()
            alertMessage.value = {
                message: 'Selected items removed successfully.',
                type: 'success'
            }
        } catch (error) {
            alertMessage.value = {
                message: 'An error occurred while removing items.',
                type: 'error'
            }
        } finally {
            isLoading.value = false
        }
    }

    const hasSelectedItems = () => {
        return blackListData.value.some(item => item.isSelected)
    }

    onMounted(() => {
        loadBlackListData()
    })

    return {
        isLoading,
        blackListData,
        alertMessage,
        selectAll,
        removeFromBlacklist,
        handleBulkDelete,
        toggleSelectAll,
        hasSelectedItems
    }
}