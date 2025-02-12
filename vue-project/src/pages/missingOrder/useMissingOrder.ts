import { createOrder, getAbandonedOrders, updateAbandonedOrderStatus } from "@/api"
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

    const totalRecords = ref(0)
    const currentPage = ref(1)
    const totalPages = ref(0)
    const orderFilter = ref({
        page: 1,
        per_page: 30,
        status: "",
        search: "",
    });

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
            totalAbandonedOrder: abandonOrders.value?.length || 0,
            remainingAbandonedOrder: 0,
            totalRecoveredOrder: 0,
            totalCallNotReceived: 0,
            recoveredAmount: 0,
        }

        abandonOrders.value.forEach(item => {
            if(item.status == 'confirmed'){
                data.totalCallNotReceived += 1
            }
            
            if(item.status == 'confirmed'){
                data.totalRecoveredOrder += 1
                data.recoveredAmount +=  +item.total_value
                return
            }else {
                data.remainingAbandonedOrder += 1
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
            await createOrderFromAbandonedData(item)
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
    }

    const createOrderFromAbandonedData = () => {
        
    }

    const loadAbandonedOrder = async () => {
        try {
            isLoading.value = true
            if (orderFilter.value.page == 0) {
                orderFilter.value.page = 1;
            }
            const { data, pagination } = await getAbandonedOrders(orderFilter.value)
            totalRecords.value = pagination.total_count
            currentPage.value = pagination.current_page
            totalPages.value = pagination.total_pages
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
        totalPages,
        orderFilter,
        currentPage,
        totalRecords,
        abandonOrders,
        selectedOption,
        getDashboardData,
        filteredAbandonOrders,
        updateStatus,
        handleFilter,
        loadAbandonedOrder,
    }
}