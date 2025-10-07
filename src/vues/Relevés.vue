<template>
  <v-container class="py-6 bg-grey-lighten-4">
    <h2 class="font-weight-bold text-h5 mb-4">Gestion des Relevés</h2>

    <div class="d-flex justify-space-between align-center mb-4">
      <v-select
        v-model="filtreCmp"
        :items="['Tous les compteurs', ...compteurs.map(c => c.codecmp)]"
        label="Tous les compteurs"
        dense outlined
        style="width: 260px"
      />
      <v-btn color="orange" dark @click="ouvrirForm()">Nouveau Relevé</v-btn>
    </div>

    <v-card>
      <v-table>
        <thead>
          <tr>
            <th>ID</th>
            <th>COMPTEUR</th>
            <th>CLIENT</th>
            <th>TYPE</th>
            <th>VALEUR</th>
            <th>DATE RELEVÉ</th>
            <th>DATE LIMITE</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in relevesFiltres" :key="r.id_releve">
            <td>{{ r.id_releve }}</td>
            <td>{{ r.codecmp }}</td>
            <td>{{ r.nom_client }}</td>
            <td>
              <span
                class="badge badge-type"
                :class="r.type === 'eau' ? 'badge-eau' : 'badge-elec'"
              >
                {{ r.type === 'eau' ? 'Eau' : 'Électricité' }}
              </span>
            </td>
            <td>{{ r.valeur }} {{ r.type === 'eau' ? 'm³' : 'kWh' }}</td>
            <td>{{ formatDate(r.date_releve) }}</td>
            <td class="text-red">{{ formatDate(r.date_limite) }}</td>
            <td>
              <span class="text-blue" @click="ouvrirForm(r)">Modifier</span>
              &nbsp;|&nbsp;
              <span class="text-red" @click="supprimerReleve(r.id_releve)">Supprimer</span>
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <!-- FORM MODAL -->
    <v-dialog v-model="formVisible" max-width="600">
      <v-card>
        <v-card-title class="bg-orange text-white font-weight-bold">
          {{ formData.id_releve ? 'Modifier' : 'Nouveau' }} Relevé
        </v-card-title>
        <v-card-text>
          <v-form>
            <v-row dense>
              <v-col cols="12">
                <v-select
                  v-model="formData.codecmp"
                  :items="compteurs.map(c => ({ codecmp: c.codecmp, label: `${c.codecmp} - ${c.nom_client}` }))"
                  item-value="codecmp"
                  item-title="label"
                  label="Compteur"
                />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field v-model="formData.valeur" type="number" label="Valeur" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field v-model="formData.date_releve" label="Date Relevé" type="date" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field v-model="formData.date_limite" label="Date Limite" type="date" />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-btn text @click="formVisible = false">Annuler</v-btn>
          <v-btn color="orange" dark @click="enregistrerReleve">Enregistrer</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
export default {
  name: "Releves",
  data() {
    return {
      releves: [],
      compteurs: [],
      filtreCmp: "Tous les compteurs",
      formVisible: false,
      formData: {}
    };
  },
  computed: {
    relevesFiltres() {
      if (this.filtreCmp === "Tous les compteurs") return this.releves;
      return this.releves.filter(r => r.codecmp === this.filtreCmp);
    }
  },
  mounted() {
    this.chargerReleves();
    this.chargerCompteurs();
  },
  methods: {
    formatDate(date) {
      if (!date) return "";
      return new Date(date).toLocaleDateString("fr-FR");
    },
    async chargerReleves() {
      const res = await fetch("http://localhost/Stage1/backend/api/releves.php");
      this.releves = await res.json();
    },
    async chargerCompteurs() {
      const res = await fetch("http://localhost/Stage1/backend/api/compteurs.php");
      this.compteurs = await res.json();
    },
    ouvrirForm(r = {}) {
      this.formData = {
        id_releve: r.id_releve || null,
        codecmp: r.codecmp || "",
        valeur: r.valeur || "",
        date_releve: r.date_releve || "",
        date_limite: r.date_limite || ""
      };
      this.formVisible = true;
    },
    async enregistrerReleve() {
      const method = this.formData.id_releve ? "PUT" : "POST";
      const res = await fetch("http://localhost/Stage1/backend/api/releves.php", {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(this.formData)
      });
      const result = await res.json();
      alert(result.message || result.error);
      this.formVisible = false;
      this.chargerReleves();
    },
    async supprimerReleve(id) {
      if (!confirm("Supprimer ce relevé ?")) return;
      await fetch("http://localhost/Stage1/backend/api/releves.php", {
        method: "DELETE",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `id_releve=${id}`
      });
      this.chargerReleves();
    }
  }
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
  cursor: pointer;
}
.text-red {
  color: #d32f2f;
  cursor: pointer;
}
</style>