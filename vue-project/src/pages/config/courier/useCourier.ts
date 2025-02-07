import { getCourierCompanies, getCourierConfig, saveCourierConfig } from "@/remoteApi"
import { onMounted, ref } from "vue"

export const useCourier = () => {
    const activeTab = ref<string>('steadfast')
    const isLoading = ref(false)
    const form = ref()
    const hasUnsavedData = ref(false)
    const courierCompanyNames = ref<{
        title: string
        slug: string
    }[]>([])

    const courierConfigs = ref<{
        id: string | number
        api_key: string,
        secret_key: string,
        logo: string
    }[]>([])


    const changeTab = (tabSlug: string) => {
        if(hasUnsavedData.value && tabSlug != activeTab.value){
            alert('You have unsaved changes. Please save before switching tabs!');
            return
        }

        activeTab.value = tabSlug
        const currentConfig = courierConfigs.value[tabSlug]

        if (!Object.keys(currentConfig).length) {
            const defaultConfig = {
                ...courierCompanyNames.value.find(item => item.slug == tabSlug),
                api_key: '', 
                secret_key: '',
                is_active: false,
            }
            form.value = defaultConfig
        } else {
            form.value = currentConfig
        }
    }


    const loadCourierConfig = async () => {
        const { data } = await getCourierConfig()
        courierConfigs.value = data
    }

    const handleSaveCourierConfig = async (payload, button) => {
        if(!payload.api_key || !payload.secret_key){
            alert("API Key and Secret Key must not be empty!");
            return
        }


        try {
            button.isLoading = true
            isLoading.value = true
            await saveCourierConfig(payload)
            await loadCourierConfig()
        } finally {
            hasUnsavedData.value = false
            button.isLoading = false
            isLoading.value = false
        }
    }

    const loadCourierConfigData = async () => {
        try {
            isLoading.value = true
            const { data } = await getCourierCompanies()
            courierCompanyNames.value = data

            await loadCourierConfig()
        } finally {
            isLoading.value = false
        }
        changeTab('steadfast')
    }

    return {
        form,
        activeTab,
        isLoading,
        courierConfigs,
        hasUnsavedData,
        courierCompanyNames,
        changeTab,
        loadCourierConfigData,
        handleSaveCourierConfig,
    }
}