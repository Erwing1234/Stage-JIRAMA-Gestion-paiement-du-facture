<template>
  <v-app>
    <!-- On n'affiche Pas la barre si route courante est /login -->
    <BarreNavigation v-if="$route.path !== '/login'" />
    
    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>

<script>
import BarreNavigation from '@/composants/BarreNavigation.vue'

export default {
  components: { BarreNavigation }
}
</script>