import { onMounted, ref } from "vue"
import List from "./fragments/List.vue"
import Create from "./fragments/Create.vue"
import { 
    updateCustomStatus,
    deleteCustomStatus, 
    getCustomStatusList, 
    createCustomStatus 
} from "@/api"

export const useCustomStatus = () => {
    const isLoading = ref(false)
    const hasUnsavedData = ref(false)
    const alertMessage = ref<{
        message: string
        type: "success" | "danger" | "warning" | "info"
    }>({
        message: '',
        type: 'danger'
    })
    const activeTab = ref('list')
    const defaultFormData = {
        title: '',
        color: '',
        description: ''
    }

    const statusList = ref<{
        title: string,
        slug: string,
        color: string,
        description: string
    }>({})

    const form = ref<{
        title: string
        color: string
        description: string
    }>({...defaultFormData})

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

    const tabChange = (slug: string) => {
        activeTab.value = slug
        form.value = {...defaultFormData}
    }

    const handleCustomStatusCreate = async (btn) => {
        const { title, color } = form.value
        if(!title || !color) {
            alertMessage.value.message = 'The fields for Title, and Color are mandatory!'
            alertMessage.value.type = 'danger'
            return
        }
        try {
            isLoading.value = true
            btn.isLoading = true
            const { data } = await createCustomStatus(form.value)
            statusList.value = {
                ...data,
                ...statusList.value,
            }
            alertMessage.value.message = "Status created successfully!"
            alertMessage.value.type = 'success'
            form.value = {
                ...defaultFormData
            }
        } catch ({ response }) {
            alertMessage.value.message = response.data.message
            alertMessage.value.type = 'danger'
        } finally{
            isLoading.value = false
            btn.isLoading = false
        }

        setTimeout(() => {
            alertMessage.value.message = ''
        }, 5000)
    }

    const loadCustomStatusList = async () => {
        try {
            isLoading.value = true
            const { data } = await getCustomStatusList()
            statusList.value = data
        } finally{
            isLoading.value = false
        }
    }

    const handleCustomStatusUpdate = async (item: object, id: string, btn: {isLoading: boolean}) => {
        const { title, color } = form.value
        if(!title || !color) {
            alertMessage.value.message = 'The fields for Title, and Color are mandatory!'
            alertMessage.value.type = 'danger'
            return
        }

        try {
            isLoading.value = true
            btn.isLoading = true
            await updateCustomStatus(item, id)
            loadCustomStatusList()
            alertMessage.value.message = 'Status updated successfully!'
            alertMessage.value.type = 'success'
            
        } catch ({ response }) {
            alertMessage.value.message = response.data.message
            alertMessage.value.type = 'danger'
        } finally{
            isLoading.value = false
            btn.isLoading = false
        }

        setTimeout(() => {
            alertMessage.value.message = ''
        }, 5000)
    }

    const handleCustomStatusDelete = async (id: string, btn: {isLoading: boolean}) => {
        if(!confirm("Are you sure to delete this status?")) return
        try {
            isLoading.value = true
            btn.isLoading = true
            await deleteCustomStatus(id)
            delete statusList.value[id]
            alertMessage.value.message = 'Status deleted successfully!'
            alertMessage.value.type = 'success'
        } catch ({ response }) {
            alertMessage.value.message = response.data.message
            alertMessage.value.type = 'danger'
        } finally{
            isLoading.value = false
            btn.isLoading = false
        }

        setTimeout(() => {
            alertMessage.value.message = ''
        }, 5000)
    }

    onMounted(() => {
        loadCustomStatusList()
    })


    return {
        form,
        tabs,
        activeTab,
        isLoading,
        statusList,
        components,
        alertMessage,
        hasUnsavedData,
        tabChange,
        loadCustomStatusList,
        handleCustomStatusUpdate,
        handleCustomStatusDelete,
        handleCustomStatusCreate
    }
}