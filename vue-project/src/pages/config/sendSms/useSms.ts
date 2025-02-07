import {
    getOrderList,
    getWoocomerceStatuses,
    getSMSHistoryData,
    deleteSMSHistory,
} from "@/api"
import { ref } from "vue"
import List from './List.vue'
import Create from './Create.vue'
import { sendSMS } from "@/remoteApi"
import { normalizePhoneNumber } from "@/helper"

export const useSms = () => {
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
            title: 'SMS History',
            slug: 'list'
        },
        {
            title: 'Send SMS',
            slug: 'create'
        },
    ])
    const components = ref({
        list: List,
        create: Create
    })

    const defaultFormData: {
        status: string | null,
        message: string,
        phone_numbers: string
    } = {
        status: null,
        message: '',
        phone_numbers: ''
    }

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

    const loadPhoneNumbers = async (status: string) => {
        try {
            isLoading.value = true
            const { data } = await getOrderList({
                status: status
            })

            const phoneNumbers = data.map(item => normalizePhoneNumber(item.billing_address.phone))

            form.value.phone_numbers = [...new Set(phoneNumbers)].join(',').replaceAll(',,', ',')
        } finally {
            isLoading.value = false
        }
    }
    const handleSendSMS = async (btn, payload: {
        phone_numbers: string
        message: string
        status?: string
    }) => {
        if (payload.message == '' || payload.phone_numbers == '') {
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

        let phones: any = new Set(payload.phone_numbers.replaceAll(/\s+/g, '').split(','))
        phones = [...phones]

        try {
            isLoading.value = true
            btn.isLoading = true
            const { data } = await sendSMS({
                phone: phones.join(','),
                content: payload.message,
                status: payload.status
            })

            alertMessage.value.message = data.error_message || data.success_message
            alertMessage.value.type = data.error_message ? 'warning' : 'success'
        } finally {
            isLoading.value = false
            btn.isLoading = false
            form.value = { ...defaultFormData }
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
            const res = await deleteSMSHistory(id)

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
        const { data } = await getSMSHistoryData()
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
        form,
        tabs,
        messages,
        isLoading,
        activeTab,
        components,
        wooStatuses,
        alertMessage,
        hasUnsavedData,
        personalizations,
        loadSMS,
        tabChange,
        handleSendSMS,
        handleDeleteSMS,
        loadWooStatuses,
        loadPhoneNumbers
    }
}