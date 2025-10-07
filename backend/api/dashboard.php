<?php
header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=jirama;charset=utf8","root","");
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["error"=>"DB ".$e->getMessage()]); exit;
}

/* KPI */
$totalClients    = (int)$pdo->query("SELECT COUNT(*) FROM client")->fetchColumn();
$compteursActifs = (int)$pdo->query("SELECT COUNT(*) FROM compteur WHERE status='actif'")->fetchColumn();
$nbImpayees      = (int)$pdo->query("SELECT COUNT(*) FROM facture WHERE statut='impayé'")->fetchColumn();

/* Montant dû = somme factures impayées + partielles */
$montantDu = (int)$pdo->query("
   SELECT IFNULL(SUM(total_ttc),0)
   FROM facture
   WHERE statut IN ('impayé','partiel')
")->fetchColumn();

/* 3 derniers paiements */
$lastPay = $pdo->query("
  SELECT p.id_pay, p.montant,
         DATE_FORMAT(p.date_paiement,'%d/%m/%Y %H:%i') date_paiement,
         cl.nom nom_client
  FROM paiement p
  JOIN facture f ON f.id_fact=p.id_fact
  JOIN client  cl ON cl.codecli=f.codecli
  ORDER BY p.date_paiement DESC LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);

/* conso Eau/Élec sur 8 mois */
$conso = $pdo->query("
  SELECT DATE_FORMAT(date_releve,'%b') mois,
         SUM(IF(c.type='eau',valeur,0))  AS eau,
         SUM(IF(c.type='elec',valeur,0)) AS elec
  FROM releve r
  JOIN compteur c ON c.codecmp=r.codecmp
  WHERE date_releve >= DATE_SUB(CURDATE(),INTERVAL 7 MONTH)
  GROUP BY mois
  ORDER BY MIN(date_releve)
")->fetchAll(PDO::FETCH_ASSOC);

/* répond */
echo json_encode([
    "totalClients"      => $totalClients,
    "compteursActifs"   => $compteursActifs,
    "nbImpayees"        => $nbImpayees,
    "montantDu"         => $montantDu,
    "derniersPaiements" => $lastPay,
    "consoMois"         => $conso
]);