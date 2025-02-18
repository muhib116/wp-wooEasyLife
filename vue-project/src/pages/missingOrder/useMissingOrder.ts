import { getDashboardData, getAbandonedOrders, updateAbandonedOrderStatus, createOrder } from "@/api"
import { normalizePhoneNumber, showNotification, validateBDPhoneNumber } from "@/helper"
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

    const dashboardData = ref({
        total_abandoned_orders: 0,
        total_remaining_abandoned: 0,
        lost_amount: 0,
        total_recovered_orders: 0,
        recovered_amount: 0,
        total_active_carts: 0,
        total_confirmed_orders: 0,
        total_call_not_received_orders: 0,
        average_cart_value: 0
    })
    const totalRecords = ref(0)
    const currentPage = ref(1)
    const totalPages = ref(0)
    const orderFilter = ref({
        page: 1,
        per_page: 20,
        status: "abandoned",
        search: "",
        start_date: null,
        end_date: null
    });

    const selectedOption = ref(options.value[0])

    const updateStatus = async (item, selectedStatus: string, btn) => {
        try {
            isLoading.value = true
            btn.isLoading = true
            const payload = {
                ...item,
                status: selectedStatus
            }
            
            if(selectedStatus == 'confirmed'){
                await createOrderFromAbandonedData(item, btn)
            }
            
            const { message, status } = await updateAbandonedOrderStatus(item.id, payload)
            showNotification({
                type: 'success',
                message: message
            })

            await loadAbandonedOrder()
        } catch ({ response }) {
            showNotification({
                type: 'danger',
                message: response?.data?.message
            })
        } finally {
            isLoading.value = false
            btn.isLoading = false
        }
    }

    const handleFilter = (option) => {
        orderFilter.value.status = option.id //this id==status
        selectedOption.value = option
        loadAbandonedOrder()
    }

    const createOrderFromAbandonedData = async (form, btn) => 
    {
        if (!validateBDPhoneNumber(normalizePhoneNumber(form.customer_phone.trim()))) {
            showNotification({
                type: 'danger',
                message: 'Phone number is not valid bangladeshi number!'
            })
            return
        }


        try {
            btn.isLoading = true
            const products = form.cart_contents.products.map(item => {
                return {
                    id: item.product_id,
                    quantity: item.quantity
                }
            })

            const address = [
                { first_name: form.customer_name },
                { last_name: '' },
                { address_1: form.billing_address },
                { address_2: '' },
                { phone: form.customer_phone },
                { email: form.customer_email }
            ]

            const payload = {
                products: products,
                address,
                payment_method_id: form?.cart_contents?.payment_method_id || 'cod',
                shipping_method_id: form?.cart_contents?.shipping_method || '',
                shipping_cost: form?.cart_contents?.shipping_cost || '',
                customer_note: form?.cart_contents?.customer_note || '',
                order_source: 'abandoned',
                order_status: 'wc-confirmed',
                coupon_codes: form?.cart_contents?.coupon_codes || ''
            }

            try {
                const { data } = await createOrder(payload)
                if (data.order_id) {
                    showNotification({
                        type: 'success',
                        message: 'Order created successfully!'
                    })
                }
            } catch(err) {
                console.error(err)
                showNotification({
                    type: 'danger',
                    message: 'Order not created!'
                })
            }
        } catch (err) {
            console.log({ err })
        } finally {
            btn.isLoading = false
            isLoading.value = false
        }
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

    const loadDashboardData = async (date) => {
        try {
            isLoading.value = true
            const { data } = await getDashboardData(date)
            dashboardData.value = data
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
        dashboardData,
        abandonOrders,
        selectedOption,
        updateStatus,
        handleFilter,
        loadDashboardData,
        loadAbandonedOrder,
        createOrderFromAbandonedData,
    }
}