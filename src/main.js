import { createApp } from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify'
import router from './routeur/index'

import '@mdi/font/css/materialdesignicons.css'


createApp(App)
  .use(vuetify)
  .use(router)
  .mount('#app')