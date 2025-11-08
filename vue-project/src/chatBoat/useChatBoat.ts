import { ref, nextTick, computed, type Ref } from 'vue'

export interface Message {
    text: string
    type: 'user' | 'bot'
    time: string
}

export const useChatBoat = () => {
    const isOpen = ref(false)
    const userInput = ref('')
    const messages: Ref<Message[]> = ref([
        {
            text: 'Hello! How can I help you today?',
            type: 'bot',
            time: getCurrentTime()
        }
    ])
    const messagesContainer = ref<HTMLElement | null>(null)

    // Computed to help TypeScript inference in template
    const messageList = computed(() => messages.value)

    function getCurrentTime(): string {
        const now = new Date()
        return now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
    }

    function toggleChat() {
        isOpen.value = !isOpen.value
    }

    async function sendMessage() {
        if (!userInput.value.trim()) return

        // Add user message
        messages.value.push({
            text: userInput.value,
            type: 'user',
            time: getCurrentTime()
        })

        const userMessage = userInput.value
        userInput.value = ''

        // Scroll to bottom
        await nextTick()
        scrollToBottom()

        // Simulate bot response (replace with actual API call)
        setTimeout(() => {
            messages.value.push({
                text: getBotResponse(userMessage),
                type: 'bot',
                time: getCurrentTime()
            })
            scrollToBottom()
        }, 1000)
    }

    function getBotResponse(message: string): string {
        // Simple response logic (replace with actual chatbot integration)
        const lowerMessage = message.toLowerCase()
        
        if (lowerMessage.includes('hello') || lowerMessage.includes('hi')) {
            return 'Hello! How can I assist you today?'
        } else if (lowerMessage.includes('order')) {
            return 'I can help you with your order. Please provide your order number.'
        } else if (lowerMessage.includes('help')) {
            return 'I\'m here to help! You can ask me about orders, products, shipping, or any other questions.'
        } else {
            return 'Thank you for your message. Our team will get back to you shortly.'
        }
    }

    function scrollToBottom() {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
    }

    return {
        // State
        isOpen,
        userInput,
        messages,
        messageList,
        messagesContainer,

        // Methods
        toggleChat,
        sendMessage,
        getCurrentTime,
        getBotResponse,
        scrollToBottom
    }
}