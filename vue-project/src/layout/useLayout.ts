import { getWPOption } from "@/api"
import { detectInternetState } from "@/helper"
import { onMounted, ref } from "vue"


const configData = ref()
export const useLayout = () => {
    const loadConfig = async () => {
        if(configData.value) return
        const { data } = await getWPOption({ option_name: 'config' })
        configData.value = data
    }

    const internetStatusMessage = ref({
        type: '',
        title: ''
    })

    onMounted(() => {
        detectInternetState((data) => {
            internetStatusMessage.value = data
        })
    })

    return {
        loadConfig,
        configData,
        internetStatusMessage
    }
}