<template>
  <v-container fluid class="d-flex align-center justify-center fond-login">
    <v-row
      class="login-wrapper rounded-xl overflow-hidden"
      no-gutters
    >
      <!-- Image à gauche -->
      <v-col
        cols="12"
        md="6"
        class="d-none d-md-flex align-center justify-center image-section"
      >
        <v-img
          src="Copilot_20251111_160935.png"  
          alt="Image de fond"
          cover
          height="100%"
        />
      </v-col>

      <!-- Formulaire à droite -->
      <v-col
        cols="12"
        md="6"
        class="d-flex align-center justify-center"
      >
        <v-card elevation="10" class="pa-8 form-section mx-5">
          <div class="text-center mb-5">
            <v-img
              src="/logo.png"
              alt="JIRAMA Logo"
              max-width="90"
              class="mx-auto mb-3"
            />
            <h2 class="font-weight-bold orange--text text--darken-2">
              Bienvenue
            </h2>
            <p class="grey--text text--darken-1">
              Veuillez entrer vos informations de connexion
            </p>
          </div>

          <v-card-text>
            <v-text-field
              v-model="email"
              label="Adresse e-mail"
              prepend-icon="mdi-email"
              outlined
              dense
              color="orange darken-2"
            />

            <v-text-field
              v-model="password"
              label="Mot de passe"
              prepend-icon="mdi-lock"
              type="password"
              outlined
              dense
              color="orange darken-2"
            />

            <v-btn
              block
              class="mt-4 white--text"
              color="orange darken-2"
              @click="seConnecter"
            >
              Se connecter
            </v-btn>

            

            <v-alert
              v-if="messageErreur"
              type="error"
              class="mt-4"
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
  name: "Authentification",
  data() {
    return {
      email: "",
      password: "",
      messageErreur: ""
    };
  },
  methods: {
    async seConnecter() {
      try {
        const response = await fetch("http://localhost/Stage1/backend/api/login.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            email: this.email,
            password: this.password
          })
        });

        const data = await response.json();

        if (data.success) {
          localStorage.setItem("utilisateur", JSON.stringify(data.user));
          this.$router.push("/tableau-de-bord");
        } else {
          this.messageErreur = data.message;
        }
      } catch (error) {
        this.messageErreur = "Erreur de connexion.";
      }
    }
  }
};
</script>

<style scoped>
.fond-login {
  min-height: 100vh;
  background-color: #fff8e1; /* Fond clair JIRAMA */
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Le cadre central (image + formulaire) */
.login-wrapper {
  width: 80%;
  max-width: 1100px;
  height: 600px;
  background-color: rgb(250, 245, 245);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

/* Partie image */
.image-section {
  background-color: #ffe0b2; /* Orange clair */
}

/* Formulaire */
.form-section {
  width: 100%;
  max-width: 400px;
  background-color: rgba(0, 0, 0, 0);
  border-radius: 16px;
}
</style>
