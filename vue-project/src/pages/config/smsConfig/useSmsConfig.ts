import { createOrUpdateWPOption, createSMS, deleteSMS, getSMS, getWoocomerceStatuses, getWPOption, updateSMS } from "@/api"
import { onMounted, ref } from "vue"
import List from './List.vue'
import Create from './Create.vue'
import { validateBDPhoneNumber } from "@/helper"

export const useSmsConfig = () => {
    const isLoading = ref(false)
    const wooStatuses = ref([])
    const alertMessage = ref<{
        message: string
        type: "success" | "danger" | "warning" | "info" | ''
    }>({
        message: '',
        type: 'danger'
    })
    const hasUnsavedData = ref(false)
    const activeTab = ref('list')

    const tabs = ref([
        {
            title: 'List',
            slug: 'list'
        },
        {
            title: 'Create',
            slug: 'create'
        },
    ])
    const components = ref({
        list: List,
        create: Create
    })

    const defaultFormData = {
        status: '',
        message: '',
        phone_number: '',
        message_for: '',
        is_active: true
    }
    const messageFor = [
        {
            title: 'Admin',
            slug: 'admin'
        },
        {
            title: 'Customer',
            slug: 'customer'
        }
    ]
    const tabChange = (slug: string) => {
        activeTab.value = slug
        form.value = { ...defaultFormData }
    }
    const messages = ref([])

    const form = ref({ ...defaultFormData })

    const personalizations = [
        {
            title: 'Site name',
            slug: 'site_name'
        },
        {
            title: 'Customer name',
            slug: 'customer_name'
        },
        {
            title: 'Customer phone',
            slug: 'customer_phone'
        },
        {
            title: 'Customer email',
            slug: 'customer_email'
        },
        {
            title: 'Customer billing address',
            slug: 'customer_billing_address'
        },
        {
            title: 'Customer shipping address',
            slug: 'customer_shipping_address'
        },
        {
            title: 'Customer success rate',
            slug: 'customer_success_rate'
        },
        {
            title: 'Product name',
            slug: 'product_name'
        },
        {
            title: 'Total amount',
            slug: 'total_amount'
        },
        {
            title: 'Delivery charge',
            slug: 'delivery_charge'
        },
        {
            title: 'Payment method',
            slug: 'payment_method'
        },
        {
            title: 'Product price',
            slug: 'product_price'
        },
        {
            title: 'Product name',
            slug: 'product_name'
        },
        {
            title: 'Admin phone',
            slug: 'admin_phone'
        },
    ]

    const handleCreateSMS = async (btn, payload) => {
        if (payload.status == '' || payload.message_for == '' || (payload.message_for == 'admin' && payload.phone_number == '') || payload.message == '') {
            alertMessage.value.message = `The fields marked with an asterisk (*) are mandatory.`
            alertMessage.value.type = 'warning'
            setTimeout(() => {
                alertMessage.value = {
                    message: '',
                    type: ''
                }
            }, 6000)
            return
        }

        if (payload.message_for == 'admin' && !validateBDPhoneNumber(payload.phone_number)) {
            alertMessage.value.message = `Phone number is not valid.`
            alertMessage.value.type = 'warning'
            setTimeout(() => {
                alertMessage.value = {
                    message: '',
                    type: ''
                }
            }, 6000)
            return
        }

        try {
            isLoading.value = true
            btn.isLoading = true
            const res = await createSMS(payload)
            if (res.status == "success") {
                alertMessage.value.message = res.message
                alertMessage.value.type = 'success'
                form.value = { ...defaultFormData }
            }
        }
        catch ({ response }) {
            if (response.data.status == "error") {
                alertMessage.value.message = response.data.message
                alertMessage.value.type = 'danger'
            }
        }
        finally {
            isLoading.value = false
            btn.isLoading = false

            setTimeout(() => {
                alertMessage.value = {
                    message: '',
                    type: ''
                }
            }, 4000)
        }
    }

    const handleUpdateSMS = async (btn, payload) => {
        if (payload.status == '' || payload.message_for == '' || (payload.message_for == 'admin' && payload.phone_number == '') || payload.message == '') {
            alertMessage.value.message = `The fields marked with an asterisk (*) are mandatory.`
            alertMessage.value.type = 'warning'

            setTimeout(() => {
                alertMessage.value = {
                    message: '',
                    type: ''
                }
            }, 6000)
            return
        }

        if (payload.message_for == 'admin' && !validateBDPhoneNumber(payload.phone_number)) {
            alertMessage.value.message = `Phone number is not valid.`
            alertMessage.value.type = 'warning'

            setTimeout(() => {
                alertMessage.value = {
                    message: '',
                    type: ''
                }
            }, 6000)
            return
        }

        try {
            btn.isLoading = true
            isLoading.value = true
            const res = await updateSMS(payload)
            if (res.status == "success") {
                alertMessage.value.message = res.message
                alertMessage.value.type = 'success'
                form.value = { ...defaultFormData }
                loadSMS()
            }
        }
        catch ({ response }) {
            if (response.data.status == "error") {
                alertMessage.value.message = response.data.message
                alertMessage.value.type = 'danger'
            }
        }
        finally {
            btn.isLoading = false
            isLoading.value = false

            setTimeout(() => {
                alertMessage.value = {
                    message: '',
                    type: ''
                }
            }, 4000)
        }
    }

    const handleDeleteSMS = async (id: number, btn: any) => {
        if (!confirm('Are you sure to delete this message?')) return
        try {
            btn.isLoading = true
            const res = await deleteSMS(id)

            if (res.status == "success") {
                alertMessage.value.message = res.message
                alertMessage.value.type = 'success'
                loadSMS()
            }

            setTimeout(() => {
                alertMessage.value = {
                    message: '',
                    type: ''
                }
            }, 4000)
        } finally {
            btn.isLoading = false
        }
    }

    const loadSMS = async () => {
        isLoading.value = true
        const { data } = await getSMS()
        messages.value = data
        isLoading.value = false
    }

    const loadWooStatuses = async () => {
        try {
            isLoading.value = true
            const { data } = await getWoocomerceStatuses()
            wooStatuses.value = data
        } finally {
            isLoading.value = false
        }
    }

    return {
        tabs,
        components,
        tabChange,
        personalizations,
        form,
        messageFor,
        alertMessage,
        loadSMS,
        messages,
        isLoading,
        activeTab,
        hasUnsavedData,
        handleCreateSMS,
        handleDeleteSMS,
        handleUpdateSMS,
        wooStatuses,
        loadWooStatuses
    }
}