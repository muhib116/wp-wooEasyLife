import { createApp } from 'vue'
import App from './App.vue'
import './assets/tailwind.css'
import VariableTemplateEditor from './components/VariableTemplateEditor.vue'

const app = createApp(App)

// Register the VariableTemplateEditor as a global component
app.component('VariableTemplateEditor', VariableTemplateEditor)

app.mount('#app')