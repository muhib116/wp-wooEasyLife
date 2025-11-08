import { createApp } from 'vue'
import ChatBoat from './Index.vue'
import '../index.css'

// Mount chatbot only if the element exists
const chatbotElement = document.getElementById('wel-chatbot-app')
console.log('chatbotElement:', chatbotElement);

if (chatbotElement) {
    const app = createApp(ChatBoat)
    app.mount('#wel-chatbot-app')
}
