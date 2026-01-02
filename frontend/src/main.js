import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/main.css'
import { setupErrorHandler } from './utils/errorHandler'

const app = createApp(App)

app.use(createPinia())
app.use(router)

// Setup global error handling
setupErrorHandler(app)

// Make confirm dialog available globally
app.config.globalProperties.$confirmDialog = null
app.mixin({
  mounted() {
    if (this.$refs?.confirmDialogRef) {
      app.config.globalProperties.$confirmDialog = this.$refs.confirmDialogRef
    }
  }
})

app.mount('#app')
