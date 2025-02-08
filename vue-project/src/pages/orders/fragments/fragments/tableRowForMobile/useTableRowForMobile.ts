import { ref } from 'vue';

export const useTableRowForMobile = () => {
    let tapTimer = null
    const showOrderDetailsPopup = ref(false)

    const startLongPress = (e) => 
    {
        tapTimer = setTimeout(() => {
            e.preventDefault()
            showOrderDetailsPopup.value = true
        }, 1000)
    }

    const cancelLongPress = () => {
        clearTimeout(tapTimer)
    }

    const onTouchMove = () => {
        cancelLongPress()
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
        onTouchMove
    }
}