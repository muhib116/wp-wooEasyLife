import { ref } from 'vue';

export const useTableRowForMobile = () => {
    let tapTimer = null
    const showOrderDetailsPopup = ref(true)

    const startLongPress = (e) => 
    {
        e.preventDefault()
        tapTimer = setTimeout(() => {
            showOrderDetailsPopup.value = true
            console.log('hidden')
        }, 400)
    }

    const cancelLongPress = () => {
        clearTimeout(tapTimer)
    }

    const closePopup = () => {
        showOrderDetailsPopup.value = false
    }

    return {
        tapTimer,
        showOrderDetailsPopup,
        startLongPress,
        cancelLongPress,
        closePopup,
    }
}