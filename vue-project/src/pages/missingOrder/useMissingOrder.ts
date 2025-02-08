import { getAbandonedOrders, updateAbandonedOrderStatus } from "@/api"
import { showNotification } from "@/helper"
import { computed, onMounted, ref } from "vue"

export const useMissingOrder = () => {
    const isLoading = ref()
    const abandonOrders = ref([])
    const options = ref([
        {
          title: 'Abandoned',
          id: 'abandoned',
          color: '#8cc520'
        },
        {
          title: 'Call not received',
          id: 'call-not-received',
          color: '#f97315'
        },
        {
          title: 'Confirmed',
          id: 'confirmed',
          color: '#00b002'
        },
        {
          title: 'Delete',
          id: 'canceled',
          color: '#e82661'
        }
    ])

    const selectedOption = ref(options.value[0])

    const filteredAbandonOrders = computed(() => {
        let filteredOrders = [];
        filteredOrders = abandonOrders.value.filter(
            (item) => item?.status?.toLowerCase() == selectedOption.value.id
        )
        return filteredOrders
    })
    

    const getDashboardData = computed(() => {
        const data = {
            loosedAmount: 0,
            totalAbandonedOrder: 0,
            totalRecoveredOrder: 0,
            recoveredAmount: 0,
        }

        abandonOrders.value.forEach(item => {
            if(item.status == 'confirmed'){
                data.totalRecoveredOrder += 1
                data.recoveredAmount +=  +item.total_value
                return
            }else {
                data.totalAbandonedOrder += 1
                data.loosedAmount +=  +item.total_value
            }
        })

        return data
    })

    const updateStatus = async (item, selectedStatus: string, btn) => {
        try {
            isLoading.value = true
            btn.isLoading = true
            const payload = {
                ...item,
                status: selectedStatus
            }
            const { message, status } = await updateAbandonedOrderStatus(item.id, payload)
            showNotification({
                type: 'success',
                message: message
            })

            await loadAbandonedOrder()
        } catch({response}) {
            showNotification({
                type: 'danger',
                message: response?.data?.message
            })
        } finally {
            isLoading.value = false
            btn.isLoading = false
        }
    }

    const handleFilter = (item) => {
        selectedOption.value = item
        // loadAbandonedOrder()
    }

    const loadAbandonedOrder = async (date?: {start_date: string, end_date: string}) => {
        try {
            isLoading.value = true
            const { data } = await getAbandonedOrders(date)
            abandonOrders.value = data
        } finally {
            isLoading.value = false

        }
    }

    onMounted(() => {
        loadAbandonedOrder()
    })

    return {
        options,
        isLoading,
        abandonOrders,
        selectedOption,
        getDashboardData,
        filteredAbandonOrders,
        updateStatus,
        handleFilter,
        loadAbandonedOrder,
    }
}