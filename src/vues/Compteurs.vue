<template>
  <v-container class="py-6 bg-grey-lighten-4">

    <!-- Titre + Filtres + Bouton -->
    <div class="d-flex justify-space-between align-center mb-6">
      <h2 class="font-weight-bold text-h5">Gestion des Compteurs</h2>
      <div class="d-flex align-center gap-3">
        <v-select
          v-model="filtreType"
          :items="['Tous les types', 'eau', 'elec']"
          label="Type"
          dense
          variant="outlined"
          hide-details
          style="width: 180px"
        />
        <v-select
          v-model="filtreStatus"
          :items="['Tous les statuts', 'actif', 'inactif']"
          label="Statut"
          dense
          variant="outlined"
          hide-details
          style="width: 180px"
        />
        <v-btn color="orange" dark @click="ouvrirForm()">Nouveau Compteur</v-btn>
      </div>
    </div>

    <!-- Tableau design -->
    <v-card class="pa-2">
      <v-table>
        <thead>
          <tr>
            <th>CODE COMPTEUR</th>
            <th>CLIENT</th>
            <th>TYPE</th>
            <th>PRIX UNITAIRE</th>
            <th>DATE INSTALLATION</th>
            <th>STATUT</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cmp in compteursFiltres" :key="cmp.codecmp">
            <td>{{ cmp.codecmp }}</td>
            <td>{{ cmp.codecli }} - {{ cmp.nom_client }}</td>
            <td>
              <span
                class="badge badge-type"
                :class="cmp.type === 'eau' ? 'badge-eau' : 'badge-elec'"
              >{{ cmp.type === 'eau' ? 'Eau' : 'Électricité' }}</span>
            </td>
            <td>{{ formatPrix(cmp.pu) }} Ar</td>
            <td>{{ cmp.date_inst }}</td>
            <td>
              <span
                class="badge badge-status"
                :class="cmp.status === 'actif' ? 'badge-actif' : 'badge-inactif'"
              >{{ cmp.status === 'actif' ? 'Actif' : 'Inactif' }}</span>
            </td>
            <td>
              <span class="text-blue" @click="ouvrirForm(cmp)" style="cursor: pointer">Modifier</span>
              &nbsp;|&nbsp;
              <span class="text-red" @click="supprimerCompteur(cmp.codecmp)" style="cursor: pointer">Supprimer</span>
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <!-- Formulaire modal -->
    <v-dialog v-model="formVisible" max-width="600">
      <v-card>
        <v-card-title class="bg-orange text-white font-weight-bold">
          {{ formData.codecmp ? 'Modifier' : 'Nouveau' }} Compteur
        </v-card-title>
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field label="Code" v-model="formData.codecmp" :disabled="estModification" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-select label="Client" :items="clients" item-title="nom" item-value="codecli" v-model="formData.codecli" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-select label="Type" :items="['eau', 'elec']" v-model="formData.type" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="Prix unitaire" v-model="formData.pu" type="number" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field label="Date installation" v-model="formData.date_inst" type="date" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-select label="Statut" :items="['actif', 'inactif']" v-model="formData.status" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn text @click="formVisible = false">Annuler</v-btn>
          <v-btn color="orange" dark @click="enregistrerCompteur">Enregistrer</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-container>
</template>

<script>
export default {
  name: "Compteurs",
  data() {
    return {
      compteurs: [],
      clients: [],
      formVisible: false,
      estModification: false,
      formData: {
        codecmp: "",
        codecli: "",
        type: "",
        pu: "",
        date_inst: "",
        status: ""
      },
      filtreType: "Tous les types",
      filtreStatus: "Tous les statuts"
    };
  },
  computed: {
    compteursFiltres() {
      return this.compteurs
        .filter(c => this.filtreType === "Tous les types" || c.type === this.filtreType)
        .filter(c => this.filtreStatus === "Tous les statuts" || c.status === this.filtreStatus);
    }
  },
  mounted() {
    this.chargerCompteurs();
    this.chargerClients();
  },
  methods: {
    formatPrix(val) {
      return parseInt(val).toLocaleString("fr-FR");
    },
    async chargerCompteurs() {
      const res = await fetch("http://localhost/Stage1/backend/api/compteurs.php");
      this.compteurs = await res.json();
    },
    async chargerClients() {
      const res = await fetch("http://localhost/Stage1/backend/api/clients.php");
      this.clients = await res.json();
    },
    ouvrirForm(cmp = {}) {
      this.estModification = !!cmp.codecmp;
      this.formData = { ...cmp };
      this.formVisible = true;
    },
    async enregistrerCompteur() {
      const method = this.estModification ? "PUT" : "POST";
      const res = await fetch("http://localhost/Stage1/backend/api/compteurs.php", {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(this.formData)
      });
      const response = await res.json();
      alert(response.message ?? response.error);
      this.formVisible = false;
      this.chargerCompteurs();
    },
    async supprimerCompteur(codecmp) {
      if (confirm("Supprimer ce compteur ?")) {
        await fetch("http://localhost/Stage1/backend/api/compteurs.php", {
          method: "DELETE",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `codecmp=${codecmp}`
        });
       
        this.chargerCompteurs();
      }
    }
  },
};
</script>

<style scoped>
.badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  display: inline-block;
}
.badge-type.badge-eau {
  background-color: #e3f2fd;
  color: #1976d2;
}
.badge-type.badge-elec {
  background-color: #fff8e1;
  color: #ff8f00;
}
.badge-status.badge-actif {
  background-color: #e8f5e9;
  color: #2e7d32;
}
.badge-status.badge-inactif {
  background-color: #ffebee;
  color: #c62828;
}
.text-blue {
  color: #1976d2;
  font-weight: 500;
}
.text-red {
  color: #d32f2f;
}
</style>