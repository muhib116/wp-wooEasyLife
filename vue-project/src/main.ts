import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './index.css'
import clickOutside from './directives/clickOutside'

const app = createApp(App)

app.directive('click-outside', clickOutside)
app.use(router)

app.mount('#woo-easy-life')
