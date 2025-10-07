<template>
  <v-container class="py-6 bg-grey-lighten-4">
    <!-- TITRES -->
    <h1 class="font-weight-bold text-h5 mb-2">Gestion des Factures</h1>

    <!-- FILTRES + BOUTONS -->
    <div class="d-flex justify-space-between align-center mb-4">
      <div class="d-flex gap-2 align-center">
        <v-select
          v-model="filtreClient"
          :items="['Tous les clients', ...clients.map(c => ({ label: c.nom, codecli: c.codecli }))]"
          item-title="label"
          item-value="label"
          label="Client"
          hide-details dense outlined style="width: 240px"
        />
        <v-select
          v-model="filtreStatut"
          :items="['Tous les statuts', 'payé', 'impayé']"
          label="Statut"
          dense outlined hide-details style="width: 180px"
        />
      </div>
      <div class="d-flex gap-2">
        <v-btn color="orange" dark @click="ouvrirForm()">Nouvelle Facture</v-btn>
        <v-btn color="red" dark @click="afficherClientsImpayes()">Clients Impayés</v-btn>
      </div>
    </div>

    <!-- TABLEAU DES FACTURES -->
    <v-card>
      <v-table>
        <thead>
          <tr>
            <th>ID</th>
            <th>CLIENT</th>
            <th>RELEVÉ</th>
            <th>MONTANT TTC</th>
            <th>DATE ÉMISSION</th>
            <th>DATE ÉCHÉANCE</th>
            <th>STATUT</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="f in facturesFiltres" :key="f.id_fact">
            <td>{{ f.id_fact }}</td>
            <td>{{ f.nom_client }}</td>
            <td>{{ f.id_releve }}</td>
            <td class="font-weight-medium">{{ formatPrix(f.total_ttc) }} Ar</td>
            <td>{{ formatDate(f.date_emission) }}</td>
            <td class="text-red">{{ formatDate(f.date_echeance) }}</td>
            <td>
              <span class="badge badge-statut" :class="'badge-' + f.statut">
                {{ f.statut }}
              </span>
            </td>
            <td>
              <span class="text-green" @click="ouvrirPDF(f)">PDF</span> |
              <span class="text-blue" @click="ouvrirForm(f)">Modifier</span> |
              <span class="text-red" @click="supprimerFacture(f.id_fact)">Supprimer</span>
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <!-- MODALE NOUVELLE/MODIF FACTURE -->
    <v-dialog v-model="formVisible" max-width="600">
      <v-card>
        <v-card-title class="bg-orange text-white">
          {{ formData.id_fact ? "Modifier" : "Ajouter" }} une Facture
        </v-card-title>
        <v-card-text>
          <v-form>
            <v-row dense>
              <v-col cols="12" sm="6">
                <v-select v-model="formData.codecli" :items="clients" item-title="nom" item-value="codecli" label="Client" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-select v-model="formData.id_releve" :items="releves" item-title="id_releve" item-value="id_releve" label="Relevé associé" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field v-model="formData.total_ttc" label="Montant TTC (Ar)" type="number"/>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select v-model="formData.statut" :items="['payé', 'impayé']" label="Statut" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field v-model="formData.date_emission" label="Date émission" type="date" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field v-model="formData.date_echeance" label="Date échéance" type="date" />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-btn text @click="formVisible = false">Annuler</v-btn>
          <v-btn color="orange" dark @click="enregistrerFacture">Enregistrer</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODALE CLIENTS IMPAYÉS -->
    <v-dialog v-model="dialogImpayes" max-width="700">
      <v-card>
       <!-- <v-card-title class="text-h6 font-weight-bold"> -->
          <v-card-title class="bg-orange text-white">
          Clients avec Factures Impayées
        </v-card-title>
        <v-card-text>
          <v-table>
            <thead>
              <tr>
                <th>CLIENT</th>
                <th>QUARTIER</th>
                <th>MONTANT DÛ</th>
                <th>CONTACT</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, index) in clientsImpayes" :key="index">
                <td>{{ c.nom }}</td>
                <td>{{ c.quartier }}</td>
                <td class="text-red">{{ formatPrix(c.montant) }} Ar</td>
                <td>{{ c.téléphone }}</td>
                <td>
                  <v-btn color="blue" size="small" @click="envoyerRappelClient(c)">
  📩 Rappel
</v-btn>
               
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>
        <v-card-actions>
          <v-btn text @click="dialogImpayes = false">Fermer</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
export default {
  name: "Factures",
  data() {
    return {
      factures: [],
      clients: [],
      releves: [],
      formData: {},
      formVisible: false,
      filtreClient: "Tous les clients",
      filtreStatut: "Tous les statuts",

      // nouveaux pour la modale des clients impayés
      dialogImpayes: false,
      clientsImpayes: [],
    };
  },
  computed: {
    facturesFiltres() {
      return this.factures
        .filter(f =>
          this.filtreClient === "Tous les clients" || f.nom_client === this.filtreClient
        )
        .filter(f =>
          this.filtreStatut === "Tous les statuts" || f.statut === this.filtreStatut
        );
    }
  },
  mounted() {
    this.chargerDonnees();
  },
  methods: {
    formatDate(date) {
      return new Date(date).toLocaleDateString("fr-FR");
    },
    formatPrix(val) {
      return parseInt(val).toLocaleString("fr-FR");
    },
    async chargerDonnees() {
      const [factures, clients, releves] = await Promise.all([
        fetch("http://localhost/Stage1/backend/api/factures.php").then(res => res.json()),
        fetch("http://localhost/Stage1/backend/api/clients.php").then(res => res.json()),
        fetch("http://localhost/Stage1/backend/api/releves.php").then(res => res.json()),
      ]);
      this.factures = factures;
      this.clients = clients;
      this.releves = releves;
    },
    ouvrirForm(f = {}) {
      this.formData = {
        id_fact: f.id_fact || null,
        codecli: f.codecli || "",
        id_releve: f.id_releve || "",
        total_ttc: f.total_ttc || "",
        date_emission: f.date_emission || "",
        date_echeance: f.date_echeance || "",
        statut: f.statut || "impayé"
      };
      this.formVisible = true;
    },
    async enregistrerFacture() {
      const method = this.formData.id_fact ? "PUT" : "POST";
      const res = await fetch("http://localhost/Stage1/backend/api/factures.php", {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(this.formData),
      });
      const result = await res.json();
      alert(result.message || result.error);
      this.formVisible = false;
      this.chargerDonnees();
    },
    async supprimerFacture(id) {
      if (!confirm("Supprimer cette facture ?")) return;
      await fetch("http://localhost/Stage1/backend/api/factures.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id_fact=${id}`
      });
      this.chargerDonnees();
    },
    ouvrirPDF(facture) {
      window.open(`http://localhost/Stage1/backend/api/generer_pdf.php?id_fact=${facture.id_fact}`, '_blank');
    },

    // méthode modale impayés
    afficherClientsImpayes() {
      const impayes = this.factures.filter(f => f.statut === "impayé");

      const grouped = {};
      for (let f of impayes) {
        const client = this.clients.find(c => c.codecli === f.codecli);
        if (!client) continue;

        if (!grouped[f.codecli]) {
          grouped[f.codecli] = {
            nom: client.nom,
            codecli  : client.codecli,
            quartier: client.quartier,
            téléphone: client.téléphone,
            email: client.email,
            montant: 0
          };
        }
        grouped[f.codecli].montant += parseFloat(f.total_ttc);
      }

      this.clientsImpayes = Object.values(grouped);
      this.dialogImpayes = true;
    },

    async envoyerRappelClient(client) {
  if (!client.email) { 
    alert("Aucune adresse e-mail"); 
    return; 
  }

  if (!confirm(`Envoyer un rappel à ${client.nom} (${client.email}) ?`)) return;

  try {
    const response = await fetch("http://localhost/Stage1/backend/api/rappel_facture_client.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        email: client.email,
        nom: client.nom,
        montant: client.montant
      })
    });

    // 🔹 Lire le body UNE SEULE FOIS
    const text = await response.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      throw new Error("Réponse invalide du serveur : " + text);
    }

    // 🔹 Vérifier le succès
    if (response.ok && data.success) {
      alert(` ${data.message}`);
    } else {
      alert(` ${data.error || "Erreur inconnue"}`);
    }
  } catch (error) {
    console.error("Erreur lors de l'envoi du rappel:", error);
    alert(` Erreur: ${error.message}`);
  }
}

   /* async envoyerRappelClient(client) {
  if (!client.email) {
    alert("Adresse e-mail manquante pour ce client"); return;
  }
  if (!confirm(`Envoyer un rappel à ${client.nom} (${client.email}) ?`)) return;

  const res = await fetch("http://localhost/Stage1/backend/api/rappel_facture_client.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      email   : client.email,
      nom     : client.nom,
      montant : client.montant
    })
  });

  const data = await res.json();
  alert(data.message || data.error || "Réponse inconnue");
},
   
   /* async envoyerRappelClient(client) {
  const confirmSend = confirm(`Envoyer un rappel à ${client.nom} (${client.email}) ?`);
  if (!confirmSend) return;

  const res = await fetch("http://localhost/Stage1/backend/api/rappel_facture_client.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      email: client.email,
      nom: client.nom,
      montant: client.montant
    })
  });

  const result = await res.json();

  //  Affiche le résultat
  if (result.message) {
    alert(result.message); // ou snackbar
  } else {
    alert("Échec de l'envoi : " + result.error);
  }
}*/
  }
};
</script>

<style scoped>
.badge-statut {
  border-radius: 10px;
  font-size: 12px;
  padding: 4px 10px;
  font-weight: 500;
  display: inline-block;
  text-transform: capitalize;
}
.badge-payé {
  background-color: #e0f2f1;
  color: #00796b;
}
.badge-impayé {
  background-color: #ffebee;
  color: #c62828;
}
.text-blue {
  color: #1976d2;
  font-weight: 500;
  cursor: pointer;
}
.text-red {
  color: #d32f2f;
  font-weight: 500;
}
.text-green {
  color: #2e7d32;
  font-weight: 500;
}
</style>