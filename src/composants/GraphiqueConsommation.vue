<template>
    <canvas ref="canvas" :height="height"></canvas>
  </template>
  
  <script setup>
  /*  Histogramme Eau / Électricité (Chart.js)  */
  import { onMounted, ref, watch } from 'vue'
  import { Chart, registerables } from 'chart.js'
  Chart.register(...registerables)
  
  const props = defineProps({
    data: { type: Array, required: true },          // mois,eau,elec
    height: { type:[String,Number], default: 220 }
  })
  
  const canvas = ref(null)
  let chart
  
  /* Construit ou reconstruit le graphique chaque fois que data change */
  const build = () => {
    if (chart) chart.destroy()
    const labels = props.data.map(e => e.mois)
    const eau    = props.data.map(e => e.eau)
    const elec   = props.data.map(e => e.elec)
  
    chart = new Chart(canvas.value, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label: 'Eau',  data: eau,  backgroundColor:'#42a5f5' },
          { label: 'Électricité', data: elec, backgroundColor:'#ffb74d' }
        ]
      },
      options: {
        responsive:true,
        plugins:{ legend:{position:'bottom'}},
        scales:{ x:{ stacked:false }, y:{ beginAtZero:true } }
      }
    })
  }
  
  onMounted(build)
  watch(() => props.data, build, { deep:true })
  </script>