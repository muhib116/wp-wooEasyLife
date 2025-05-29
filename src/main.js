import { createApp } from 'vue'
import App from './App.vue'
import './assets/tailwind.css'
import NewRequisitionForm from './components/NewRequisitionForm.vue'

const app = createApp(App)

// Register the NewRequisitionForm as a global component
app.component('NewRequisitionForm', NewRequisitionForm)

app.mount('#app')