import { defineStore } from 'pinia'
import axios          from 'axios'
import router         from '@/routeur'

export const useAuthStore = defineStore('authentification', {
  state: () => ({
    jeton:       localStorage.getItem('jeton') || null,
    utilisateur: null,
    erreur:      null
  }),
  getters: {
    estConnecte: (state) => !!state.jeton
  },
  actions: {
    async connexion(email, motdepasse) {
      this.erreur = null
      try {
        const { data } = await axios.post('/api/auth/login', { email, motdepasse })
        this.jeton       = data.jeton
        this.utilisateur = data.utilisateur
        localStorage.setItem('jeton', this.jeton)
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.jeton}`
        router.push({ name: 'TableauDeBord' })
      }
      catch (e) {
        this.erreur = e.response?.data?.message || 'Identifiants invalides'
      }
    },
    deconnexion() {
      this.jeton = null
      this.utilisateur = null
      localStorage.removeItem('jeton')
      delete axios.defaults.headers.common['Authorization']
      router.push({ name: 'Authentification' })
    }
  }
})