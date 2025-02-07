export default {
    mounted(el, binding) {
        // Define the handler function to check if the click is outside
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                // Call the method provided in the directive's value
                binding.value(event)
            }
        }
        // Add a click event listener to the document
        document.addEventListener('click', el.clickOutsideEvent)
    },
    unmounted(el) {
        // Remove the event listener when the element is unmounted
        document.removeEventListener('click', el.clickOutsideEvent)
    }
}  