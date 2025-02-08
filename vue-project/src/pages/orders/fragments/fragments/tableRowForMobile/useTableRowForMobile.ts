import { ref } from 'vue';

export const useTableRowForMobile = () => {
    let tapTimer = null
    const showPopup = ref(false)

    const startLongPress = () => {
    tapTimer = setTimeout(() => {
        showPopup.value = true
    }, 2000)
    }

    const cancelLongPress = () => {
    clearTimeout(tapTimer)
    }

    const closePopup = () => {
    showPopup.value = false
    }

    return {
        tapTimer,
        showPopup,
        startLongPress,
        cancelLongPress,
        closePopup,
    }
}