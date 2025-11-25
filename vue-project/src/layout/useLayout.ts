import { getWPOption } from "@/api"
import { detectInternetState, showNotification } from "@/helper"
import { onMounted, onUnmounted, ref } from "vue"


const configData = ref()
export const useLayout = () => {
    const loadConfig = async () => {
        if(configData.value) return
        const { data } = await getWPOption({ option_name: 'config' })
        configData.value = data
    }

    const cleanup = detectInternetState(showNotification);
    onUnmounted(cleanup);

    return {
        loadConfig,
        configData
    }
}