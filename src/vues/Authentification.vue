<template>
  <v-container
    class="fill-height d-flex align-center orange lighten-5"
    fluid
  >
    <v-row justify="center">
      <v-col cols="12" sm="8" md="4">
        <v-card elevation="10" class="pa-4">
          <v-card-title class="justify-start d-flex align-center">
            <v-img
              src="/logo.png"
              alt="JIRAMA Logo"
              max-width="500"
              class="mr-3"
            />
          
          </v-card-title>

          <v-card-text>
            <v-text-field
              v-model="email"
              label="Adresse e-mail"
              prepend-icon="mdi-email"
              outlined
              dense
              color="blue"
            />

            <v-text-field
              v-model="password"
              label="Mot de passe"
              prepend-icon="mdi-lock"
              type="password"
              outlined
              dense
              color="blue"
            />

            <v-btn
              block
              class="mt-4 white--text"
              color="orange darken-2"
              @click="seConnecter"
            >
              Se connecter
            </v-btn>

            <!-- Message d’erreur -->
            <v-alert
              v-if="messageErreur"
              type="error"
              class="mt-3"
              dense
            >
              {{ messageErreur }}
            </v-alert>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'Authentification',
  data() {
    return {
      email: '',
      password: '',
      messageErreur: ''
    }
  },
  methods: {
    async seConnecter() {
      try {
        const response = await fetch("http://localhost/Stage1/backend/api/login.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            email: this.email,
            password: this.password
          })
        });

        const data = await response.json();

        if (data.success) {
          localStorage.setItem("utilisateur", JSON.stringify(data.user));
          this.$router.push('/tableau-de-bord');
        } else {
          this.messageErreur = data.message;
        }
      } catch (error) {
        this.messageErreur = "Erreur de connexion.";
      }
    }
  }
}
</script>

<style scoped>
.fill-height {
  height: 100vh;
  background-color: #fff8e1;
}
</style>