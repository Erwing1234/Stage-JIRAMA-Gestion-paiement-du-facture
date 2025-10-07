<template>
    <v-container class="py-6 bg-grey-lighten-4">
      <div class="d-flex justify-space-between align-center mb-6">
        <h2 class="font-weight-bold text-h5">Gestion des Tarifs</h2>
        <div class="d-flex align-center gap-3">
          <v-select
            v-model="filtreType"
            :items="['Tous les types', 'eau', 'elec']"
            label="Type"
            dense variant="outlined"
            style="width: 240px"
          />
          <v-btn color="orange" dark @click="ouvrirForm()">Nouveau Tarif</v-btn>
        </div>
      </div>
  
      <v-card>
        <v-table>
          <thead>
            <tr>
              <th>ID</th>
              <th>TYPE</th>
              <th>TRANCHE</th>
              <th>PRIX UNITAIRE</th>
              <th>PÉRIODE</th>
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in tarifsFiltres" :key="t.id_tarif">
              <td>{{ t.id_tarif }}</td>
              <td>
                <span
                  class="badge badge-type"
                  :class="t.type === 'eau' ? 'badge-eau' : 'badge-elec'"
                >{{ t.type === 'eau' ? 'Eau' : 'Électricité' }}</span>
              </td>
              <td>{{ formatTranche(t.min_u, t.max_u, t.type) }}</td>
              <td>{{ formatPrix(t.prix_unit) }} Ar</td>
              <td>
                <div>{{ t.date_debut }}</div>
                <small>{{ t.date_fin ? 'Jusqu’au ' + t.date_fin : 'En cours' }}</small>
              </td>
              <td>
                <span class="text-blue" @click="ouvrirForm(t)">Modifier</span>
                &nbsp;|&nbsp;
                <span class="text-red" @click="supprimerTarif(t.id_tarif)">Supprimer</span>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-card>
  
      <!-- Formulaire -->
      <v-dialog v-model="formVisible" max-width="600">
        <v-card>
          <v-card-title class="bg-orange text-white font-weight-bold">
            {{ formData.id_tarif ? 'Modifier' : 'Nouveau' }} Tarif
          </v-card-title>
          <v-card-text>
            <v-form>
              <v-row dense>
                <v-col cols="12" sm="6">
                  <v-select v-model="formData.type" :items="['eau', 'elec']" label="Type" />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field label="Min" type="number" v-model="formData.min_u" />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field label="Max" type="number" v-model="formData.max_u" />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field label="Prix unitaire (Ar)" type="number" v-model="formData.prix_unit" />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field label="Date début" type="date" v-model="formData.date_debut" />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field label="Date fin" type="date" v-model="formData.date_fin" />
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn text @click="formVisible = false">Annuler</v-btn>
            <v-btn color="orange" dark @click="enregistrerTarif">Enregistrer</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </template>
  
  <script>
  export default {
    name: "Tarifs",
    data() {
      return {
        tarifs: [],
        formVisible: false,
        formData: {},
        filtreType: "Tous les types",
      };
    },
    computed: {
      tarifsFiltres() {
        return this.tarifs.filter(item => this.filtreType === "Tous les types" || item.type === this.filtreType);
      }
    },
    mounted() {
      this.chargerTarifs();
    },
    methods: {
      async chargerTarifs() {
        const res = await fetch("http://localhost/Stage1/backend/api/tarifs.php");
        this.tarifs = await res.json();
      },
      formatTranche(min, max, type) {
        const unit = type === "eau" ? "m³" : "kWh";
        if (!max || max === null || max === "") return `${min}+ ${unit}`;
        return `${min} - ${max} ${unit}`;
      },
      formatPrix(val) {
        return parseInt(val).toLocaleString("fr-FR");
      },
      ouvrirForm(tarif = {}) {
        this.formData = { ...tarif };
        this.formVisible = true;
      },
      async enregistrerTarif() {
        const method = this.formData.id_tarif ? "PUT" : "POST";
        const res = await fetch("http://localhost/Stage1/backend/api/tarifs.php", {
          method,
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(this.formData),
        });
        const result = await res.json();
        alert(result.message || result.error);
        this.formVisible = false;
        this.chargerTarifs();
      },
      async supprimerTarif(id) {
        if (!confirm("Supprimer ce tarif ?")) return;
        await fetch("http://localhost/Stage1/backend/api/tarifs.php", {
          method: "DELETE",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id_tarif=${id}`,
        });
        this.chargerTarifs();
      },
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
  .badge-eau {
    background-color: #e3f2fd;
    color: #1976d2;
  }
  .badge-elec {
    background-color: #fff3e0;
    color: #ff9800;
  }
  .text-blue {
    color: #1976d2;
    font-weight: 500;
  }
  .text-red {
    color: #d32f2f;
  }
  </style>