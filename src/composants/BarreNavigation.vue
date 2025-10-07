<template>
  <v-app-bar app dark color="orange">
    <!-- Logo + nom -->
    <v-toolbar-title class="d-flex align-center">
      <v-img
        src="480506256_643225961441733_1468366460892376833_n.jpg"
        max-width="30"
        max-height="30"
        class="mr-2"
        alt="JIRAMA Logo"
      />
      <span class="text-h6 font-weight-bold">JIRAMA</span>
    </v-toolbar-title>

    <v-spacer />

    <!-- Liens -->
    <v-btn
      to="/tableau-de-bord"
      text
      exact
      class="text-white font-weight-bold"
      :class="{ 'bg-white text-orange': $route.path === '/tableau-de-bord' }"
    >
      Tableau de Bord
    </v-btn>
    <v-btn
      to="/clients"
      text
      exact
      class="text-white font-weight-bold"
      :class="{ 'bg-white text-orange': $route.path === '/clients' }"
    >
      Clients
    </v-btn>
    <v-btn
      to="/compteurs"
      text
      exact
      class="text-white font-weight-bold"
      :class="{ 'bg-white text-orange': $route.path === '/compteurs' }"
    >
    Compteurs
    </v-btn>
    <v-btn
      to="/tarifs"
      text
      exact
      class="text-white font-weight-bold"
      :class="{ 'bg-white text-orange': $route.path === '/tarifs' }"
    >
    Tarifs
    </v-btn>
    <v-btn
      to="/relevés"
      text
      exact
      class="text-white font-weight-bold"
      :class="{ 'bg-white text-orange': $route.path === '/relevés' }"
    >
    Relevés
    </v-btn>
    <v-btn
      to="/factures"
      text
      exact
      class="text-white font-weight-bold"
      :class="{ 'bg-white text-orange': $route.path === '/factures' }"
    >
      Factures
    </v-btn>
    <v-btn
      to="/paiements"
      text
      exact
      class="text-white font-weight-bold"
      :class="{ 'bg-white text-orange': $route.path === '/paiements' }"
    >
      Paiements
    </v-btn>
    <v-btn
      icon
      @click="deconnexion"
      title="Se déconnecter"
      class="ml-4"
    >
      <v-icon>mdi-logout</v-icon>
    </v-btn>
  </v-app-bar>
</template>

<script>
export default {
  name: "BarreNavigation",

  methods: {
    deconnexion() {
      //  Vider la session utilisateur
      localStorage.removeItem('utilisateur');
      // Rediriger vers login
      this.$router.push('/login');
    }
  }

};
</script>

<style scoped>
.v-btn:not(.v-btn--flat).bg-white {
  border-radius: 4px;
}
</style>