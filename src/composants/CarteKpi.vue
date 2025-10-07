<template>
  <v-card elevation="1" class="pa-4 kpi-card">
    <div class="kpi-top" :style="barStyle"></div>

    <div class="d-flex align-center gap-3">
      <v-avatar size="40" :color="color">
        <v-icon color="white">{{ icon }}</v-icon>
      </v-avatar>

      <div>
        <div class="font-weight-bold text-h6">{{ value }}</div>
        <div class="caption grey--text">{{ label }}</div>
      </div>
    </div>
  </v-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  icon      : { type:String, required:true },
  label     : { type:String, required:true },
  value     : { type:[String,Number], required:true },
  color     : { type:String, default:'blue' },
  gradStart : { type:String, default:'#006dff' },
  gradEnd   : { type:String, default:'#ff6b00' }
})

const barStyle = computed(() => ({
  background:`linear-gradient(90deg, ${props.gradStart}, ${props.gradEnd})`
}))
</script>

<style scoped>
.kpi-card{ position:relative; border-radius:10px; }
.kpi-top { position:absolute; left:0; right:0; top:0; height:4px;
           border-radius:10px 10px 0 0; }
</style>