<template>
  <v-container class="py-6 bg-grey-lighten-4">
    <h2 class="font-weight-bold text-h5 mb-4">Gestion des Paiements</h2>

    <!-- Snackbar (confirmation fluide) -->
    <v-snackbar v-model="snackbar.visible" :color="snackbar.color" timeout="3000">
      {{ snackbar.text }}
    </v-snackbar>

    <!-- Filtres + bouton -->
    <div class="d-flex justify-space-between align-center mb-4">
      <div class="d-flex gap-3">
        <v-select
          v-model="filtreFacture"
          :items="['Toutes les factures', ...factures.map(f => `#${f.id_fact}`)]"
          hide-details dense outlined label="Facture" style="width:220px"
        />
        <v-select
          v-model="filtreStatut"
          :items="['Tous les statuts', 'réussi', 'échoué']"
          hide-details dense outlined label="Statut" style="width:220px"
        />
      </div>
      <v-btn color="orange" dark @click="ouvrirForm()">NOUVEAU PAIEMENT</v-btn>
    </div>

    <!-- Tableau -->
    <v-card>
      <v-table>
        <thead>
          <tr>
            <th>ID</th><th>FACTURE</th><th>CLIENT</th><th>MONTANT</th>
            <th>MODE</th><th>DATE</th><th>STATUT</th><th>RÉF.</th><th>ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in paiementsFiltres" :key="p.id_pay">
            <td>{{ p.id_pay }}</td>
            <td>#{{ p.id_fact }}</td>
            <td>{{ p.nom_client }}</td>
            <td><strong>{{ formatPrix(p.montant) }} Ar</strong></td>
            <td><span class="badge badge-mode">{{ p.mode }}</span></td>
            <td>{{ formatDate(p.date_paiement) }}</td>
            <td><span class="badge" :class="'badge-' + p.statut">{{ p.statut }}</span></td>
            <td>{{ p.ref_transac }}</td>
            <td>
              <span class="text-red" @click="supprimerPaiement(p.id_pay)">Supprimer</span>
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <!-- Modale Paiement -->
    <v-dialog v-model="formVisible" max-width="600">
      <v-card>
        <v-card-title class="bg-orange text-white font-weight-bold">
          Nouveau Paiement
        </v-card-title>

        <v-card-text>
          <v-row dense>
            <!-- Sélection facture -->
            <v-col cols="12">
              <v-select
                v-model="formData.id_fact"
                :items="facturesDispo"
                item-title="label"
                item-value="id_fact"
                label="Facture"
                @change="chargerDetailsFacture"
              />
            </v-col>

            <!-- Infos facture -->
            <v-col cols="12" v-if="selectedFacture">
              <v-alert type="info" border="start">
                <div><strong>Client :</strong> {{ selectedFacture.nom_client }}</div>
                <div><strong>Montant TTC :</strong> {{ formatPrix(selectedFacture.total_ttc) }} Ar</div>
                <div><strong>Échéance :</strong> {{ selectedFacture.date_echeance }}</div>
              </v-alert>
            </v-col>

            <!-- Mode -->
            <v-col cols="12">
              <v-select
                v-model="formData.mode"
                :items="['agence','mobile_money','carte']"
                label="Mode de paiement"
              />
            </v-col>
          </v-row>
        </v-card-text>

        <v-card-actions class="justify-end">
          <v-btn text @click="formVisible=false">ANNULER</v-btn>
          <v-btn color="orange" dark @click="enregistrerPaiement">
            ENREGISTRER
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
export default {
  name: 'Paiements',
  data() {
    return {
      paiements: [],
      factures: [],
      formVisible: false,
      formData: { id_fact: '', mode: 'agence' },
      selectedFacture: null,
      filtreFacture: 'Toutes les factures',
      filtreStatut: 'Tous les statuts',
      snackbar: { visible:false, text:'', color:'success' }
    };
  },
  computed: {
    paiementsFiltres() {
      return this.paiements
        .filter(p => this.filtreFacture === 'Toutes les factures' || `#${p.id_fact}` === this.filtreFacture)
        .filter(p => this.filtreStatut  === 'Tous les statuts'   || p.statut === this.filtreStatut);
    },
    facturesDispo() {
      return this.factures
        .filter(f => f.statut === 'impayé')
        .map(f => ({ id_fact: f.id_fact, label: `#${f.id_fact} - ${f.nom_client}` }));
    }
  },
  mounted() { this.chargerDonnees(); },
  methods: {
    formatPrix(v){ return Number(v).toLocaleString('fr-FR'); },
    formatDate(d){ const dt=new Date(d); return dt.toLocaleDateString('fr-FR')+' '+dt.toLocaleTimeString('fr-FR'); },

    async chargerDonnees(){
      const [p,f] = await Promise.all([
        fetch('http://localhost/Stage1/backend/api/paiements.php').then(r=>r.json()),
        fetch('http://localhost/Stage1/backend/api/factures.php').then(r=>r.json())
      ]);
      this.paiements=p; this.factures=f;
    },

    ouvrirForm(){
      this.formData = { id_fact:'', mode:'agence' };
      this.selectedFacture = null;
      this.formVisible = true;
    },

    chargerDetailsFacture(id){
      this.selectedFacture = this.factures.find(f=>f.id_fact===id);
    },

    async enregistrerPaiement(){
      if (!this.formData.id_fact){ 
        this.snackbar = {visible:true, text:'Choisir une facture', color:'error'}; 
        return; 
      }

      const res = await fetch('http://localhost/Stage1/backend/api/paiements.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id_fact:this.formData.id_fact, mode:this.formData.mode })
      });
      const r = await res.json();

      if(r.error){
        this.snackbar = {visible:true, text:r.error, color:'error'};
        return;
      }

      // Ajout DIRECT dans le tableau sans reload
      this.paiements.unshift({
        id_pay: r.id_pay, 
        id_fact: this.formData.id_fact,
        mode: this.formData.mode,
        montant: this.selectedFacture.total_ttc,
        statut: 'réussi',
        ref_transac: r.ref,
        date_paiement: new Date().toISOString(),
        nom_client: this.selectedFacture.nom_client
      });

      // Snackbar confirmation
      this.snackbar = { visible:true, text:r.message, color:'success' };

      // ouvrir le PDF si dispo
      if (r.pdf) window.open(r.pdf, '_blank');

      this.formVisible=false;
    },

    async supprimerPaiement(id_pay){
      if(!confirm('Supprimer ce paiement ?')) return;
      await fetch('http://localhost/Stage1/backend/api/paiements.php',{
        method:'DELETE',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`id_pay=${id_pay}`
      });
      // update local sans reload
      this.paiements = this.paiements.filter(p => p.id_pay !== id_pay);
      this.snackbar = { visible:true, text:"Paiement supprimé", color:"success" };
    }
  }
};
</script>

<style scoped>
.badge{padding:4px 10px;font-size:12px;border-radius:12px;font-weight:500;display:inline-block}
.badge-mode{background:#f3e5f5;color:#6a1b9a;text-transform:capitalize}
.badge-réussi{background:#c8e6c9;color:#2e7d32}
.badge-échoué{background:#ffcdd2;color:#c62828}
.text-blue{color:#1976d2;font-weight:500;cursor:pointer}
.text-red {color:#d32f2f;font-weight:500;cursor:pointer}
</style>