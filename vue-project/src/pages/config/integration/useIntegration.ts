import { getWPOption, createOrUpdateWPOption } from '@/api'
import { showNotification } from '@/helper'
import { onMounted, ref } from 'vue'

export const useIntegration = () => {
    const optionName = 'config'
    const isLoading = ref(false)
    const configData = ref([])

    const UpdateConfig = async (btn: {isLoading?: boolean}) => {
        console.log(btn)
        try {
            isLoading.value = true
            btn ? btn.isLoading = true : ''
            await createOrUpdateWPOption({
                option_name: optionName,
                data: configData.value
            })
        } catch (error) {
            console.log(error)
        } finally {
            showNotification({
                type: 'success',
                message: 'Configuration updated successfully!'
            })
            isLoading.value = false
            btn ? btn.isLoading = false : ''
        }
    }

    const getData = async () => {
        try {
            isLoading.value = true
            const { data } = await getWPOption({option_name: optionName})
            configData.value = data
        } catch (error) {
            console.log(error)
        } finally {
            isLoading.value = false
        }
    }

    onMounted(() => {
        getData()
    })

    return {
        optionName,
        isLoading,
        configData,
        getData,
        UpdateConfig
    }
}