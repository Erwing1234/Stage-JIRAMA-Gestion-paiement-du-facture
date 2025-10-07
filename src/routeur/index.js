import { createRouter, createWebHistory } from 'vue-router'
import Authentification from '@/vues/Authentification.vue'
import TableauDeBord from '@/vues/TableauDeBord.vue'
import Clients from '@/vues/Clients.vue'
import Compteurs from '@/vues/Compteurs.vue'
import Tarifs from '@/vues/Tarifs.vue'
import Relevés from '@/vues/Relevés.vue'
import Factures from '@/vues/Factures.vue'
import Paiements from '@/vues/Paiements.vue'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Authentification },
  { path: '/tableau-de-bord', component: TableauDeBord },
  { path: '/clients',component: Clients},
  { path: '/compteurs',component: Compteurs},
  { path: '/tarifs',component: Tarifs},
  { path: '/relevés',component: Relevés},
  { path: '/factures',component: Factures},
  { path: '/paiements',component: Paiements},

]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router