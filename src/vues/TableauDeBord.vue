<template>
  <v-container class="py-6">

    <!-- KPI avec dégradé orange --->
    <v-row dense>
      <v-col v-for="card in kpiCards" :key="card.label" cols="12" sm="6" lg="3">
        <CarteKpi
          :icon="card.icon"
          :label="card.label"
          :value="card.value"
          color="orange darken-2"
          grad-start="#ff9800" grad-end="#ff5722"
        />
      </v-col>
    </v-row>

    <!-- Paiements + Histogramme --->
    <v-row dense class="mt-8">
      <v-col cols="12" md="6">
        <v-card elevation="1" class="pa-4">
          <h4 class="font-weight-bold mb-4">Derniers Paiements</h4>
          <v-list density="compact" nav>
            <v-list-item v-for="p in stats.derniersPaiements" :key="p.id_pay"
              class="mb-2 border rounded pa-3 d-flex justify-space-between align-center">
              <div>
                <div class="font-weight-medium">{{ p.nom_client }}</div>
                <div class="text-caption grey--text">{{ p.date_paiement }}</div>
              </div>
              <div class="text-right">
                <div class="font-weight-bold">{{ fmt(p.montant) }} Ar</div>
                <v-chip color="green" size="x-small" dark>réussi</v-chip>
              </div>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card elevation="1" class="pa-4">
          <h4 class="font-weight-bold mb-4">Consommation Mensuelle</h4>
          <GraphiqueConsommation :data="stats.consoMois" :height="220"/>
        </v-card>
      </v-col>
    </v-row>

  </v-container>
</template>

<script setup>
import { reactive, onMounted, computed } from 'vue'
import CarteKpi             from '@/composants/CarteKpi.vue'
import GraphiqueConsommation from '@/composants/GraphiqueConsommation.vue'

const stats = reactive({
  totalClients:0, compteursActifs:0, nbImpayees:0, montantDu:0,
  consoMois:[], derniersPaiements:[]
})

const fmt = n => Number(n).toLocaleString('fr-FR')

const kpiCards = computed(()=>[
  { icon:'mdi-account-group', label:'Total Clients',       value:stats.totalClients },
  { icon:'mdi-gauge',         label:'Compteurs Actifs',    value:stats.compteursActifs },
  { icon:'mdi-file-alert',    label:'Factures Impayées',   value:stats.nbImpayees },
  { icon:'mdi-currency-ariary',label:'Montant Dû',        value:fmt(stats.montantDu)+' Ar' }
])

onMounted(async()=>{
  const r = await fetch('http://localhost/Stage1/backend/api/dashboard.php')
  Object.assign(stats, await r.json())
})
</script>                                                                                                                                                  