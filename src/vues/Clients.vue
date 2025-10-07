<template>
  <v-container class="py-8 bg-grey-lighten-4">

    <!-- Titre + Bouton -->
    <div class="d-flex justify-space-between align-center mb-6">
      <h2 class="font-weight-bold text-h5">Gestion des Clients</h2>
      <v-btn color="orange" dark @click="ouvrirForm()">Nouveau Client</v-btn>
    </div>

    <!-- Barre de recherche + filtre -->
    <v-row class="mb-4">
      <v-col cols="12" md="8">
        <v-text-field
          v-model="recherche"
          placeholder="Rechercher par nom, email ou quartier..."
          density="comfortable"
          clearable
          variant="outlined"
        ></v-text-field>
      </v-col>
      <v-col cols="12" md="4">
        <v-select
          v-model="filtreQuartier"
          :items="quartiersDisponibles"
          placeholder="Tous les quartiers"
          clearable
          density="comfortable"
          variant="outlined"
        ></v-select>
      </v-col>
    </v-row>

    <!-- Tableau -->
    <v-card>
      <v-table>
        <thead>
          <tr>
            <th>CODE</th>
            <th>NOM</th>
            <th>SEXE</th>
            <th>QUARTIER</th>
            <th>NIVEAU</th>
            <th>CONTACT</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="client in clientsFiltres" :key="client.codecli">
            <td>{{ client.codecli }}</td>
            <td>{{ client.nom }}</td>
            <td>{{ client.sexe === 'M' ? 'Masculin' : 'Féminin' }}</td>
            <td>{{ client.quartier }}</td>
            <td>{{ client.niveau }}</td>
            <td>
              <div>{{ client.email }}</div>
              <div>{{ client.téléphone }}</div>
            </td>
            <td>
              <span class="text-blue" @click="ouvrirForm(client)" style="cursor: pointer">Modifier</span>
              &nbsp;|&nbsp;
              <span class="text-red" @click="supprimerClient(client.codecli)" style="cursor: pointer">Supprimer</span>
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <!-- Formulaire modal -->
    <v-dialog v-model="formVisible" max-width="600">
      <v-card>
        <v-card-title class="bg-orange text-white font-weight-bold">
          {{ formData.codecli ? 'Modifier' : 'Nouveau' }} Client
        </v-card-title>
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field label="Code" v-model="formData.codecli" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="Nom" v-model="formData.nom" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-select label="Sexe" :items="['M', 'F']" v-model="formData.sexe" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="Quartier" v-model="formData.quartier" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-select label="Niveau" :items="['Particulier', 'Entreprise']" v-model="formData.niveau" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="Email" v-model="formData.email" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="Téléphone" v-model="formData.téléphone" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn text @click="formVisible = false">Annuler</v-btn>
          <v-btn color="orange" class="text-white" @click="enregistrerClient">
            Enregistrer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-container>
</template>

<script>
export default {
  name: "Clients",
  data() {
    return {
      clients: [],
      recherche: "",
      filtreQuartier: null,
      formData: {},
      formVisible: false,
    };
  },
  computed: {
    clientsFiltres() {
      const val = this.recherche.toLowerCase();
      return this.clients
        .filter(c =>
          !this.recherche ||
          c.nom?.toLowerCase().includes(val) ||
          c.email?.toLowerCase().includes(val) ||
          c.quartier?.toLowerCase().includes(val)
        )
        .filter(c => !this.filtreQuartier || c.quartier === this.filtreQuartier);
    },
    quartiersDisponibles() {
      return [...new Set(this.clients.map(c => c.quartier))];
    }
  },
  mounted() {
    this.chargerClients();
  },
  methods: {
    async chargerClients() {
      const res = await fetch("http://localhost/Stage1/backend/api/clients.php");
      const data = await res.json();
      this.clients = data;
    },
    ouvrirForm(client = {}) {
      this.formData = { ...client };
      this.formVisible = true;
    },
    async enregistrerClient() {
      const method = this.clients.some(c => c.codecli === this.formData.codecli) ? "PUT" : "POST";
      await fetch("http://localhost/Stage1/backend/api/clients.php", {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(this.formData),
      });
      this.formVisible = false;
      this.chargerClients();
    },
    async supprimerClient(codecli) {
      if (confirm("Supprimer ce client ?")) {
        await fetch("http://localhost/Stage1/backend/api/clients.php", {
          method: "DELETE",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `codecli=${codecli}`,
        });
        this.chargerClients();
      }
    },
  },
};
</script>

<style scoped>
tr > td {
  vertical-align: top;
}
</style>