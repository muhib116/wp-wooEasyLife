import { getWPOption, createOrUpdateWPOption } from '@/api'
import { onMounted, ref } from 'vue'

export const useIntegration = () => {
    const optionName = 'config'
    const isLoading = ref(false)
    const configData = ref([])
    const alertMessage = ref({
        message: '',
        type: ''
    })

    const UpdateConfig = async (btn: {isLoading?: boolean}) => {
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
            alertMessage.value.message = 'Configuration updated successfully!'
            alertMessage.value.type = 'Success'
            isLoading.value = false
            btn ? btn.isLoading = false : ''
        }

        setTimeout(() => {
            alertMessage.value = {
                message: '',
                type: ''
            }
        }, 4000)
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
        alertMessage,
        getData,
        UpdateConfig
    }
}