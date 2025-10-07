import { createApp }    from 'vue'
import App              from './App.vue'
import routeur          from './routeur'
import vuetify          from './plugins/vuetify'

// si vous utilisez Pinia ou autres, on les .use() ici
import { createPinia }  from 'pinia'

const app = createApp(App)

app
  .use(createPinia())
  .use(vuetify)
  .use(routeur)
  .mount('#app')