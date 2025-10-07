<?php
ob_start();
require_once __DIR__.'/../lib/fpdf186/fpdf.php';

try{
    $pdo = new PDO("mysql:host=localhost;dbname=jirama;charset=utf8","root","");
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    die('Conn. BDD: '.$e->getMessage());
}

if(!isset($_GET['id_fact'])) die('id_fact manquant');
$id = (int)$_GET['id_fact'];

/*  données facture + relevé + client */
$sql="
 SELECT f.*, r.valeur, r.date_releve, c.type,
        cl.nom AS nom_client, cl.quartier, cl.email
 FROM facture f
 JOIN releve   r ON r.id_releve = f.id_releve
 JOIN compteur c ON c.codecmp   = r.codecmp
 JOIN client   cl ON cl.codecli = f.codecli
 WHERE f.id_fact=?";
$st=$pdo->prepare($sql); $st->execute([$id]); $F=$st->fetch(PDO::FETCH_ASSOC);
if(!$F) die('Facture introuvable');

/* helper utf8 -> iso */
function t($s){return utf8_decode($s);}

/* PDF  */
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();

/* Logo */
$logo = dirname(__DIR__,2).'/logo.png';
if(file_exists($logo)) $pdf->Image($logo,10,8,28);

/* En-tête société */
$pdf->SetFont('Arial','B',18);
$pdf->SetTextColor(25,55,153);  $pdf->Cell(0,10,t('JIRO SY RANO '),0,0,'C');
$pdf->SetTextColor(255,153,0);  $pdf->Cell(-105,10,t('MALAGASY'),0,1,'C');
$pdf->SetTextColor(0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,5,t('149, Rue Rainandriamampandry - BP 200'),0,1,'C');
$pdf->Cell(0,5,t('NIF 1 000 000 704 - Statistique N° 360031 1957 0 10005'),0,1,'C');
$pdf->Cell(0,5,t('Votre facture mois de : ')
            .date('F Y',strtotime($F['date_emission']))
            .t('    Numero : ').$id,0,1,'C');
$pdf->Ln(5);

/*  Infos client  */
$pdf->SetFont('Arial','',10);
$infos=[
  [t('Titulaire du compte :'), $F['nom_client']],
  [t('Référence client :'),    $F['codecli']],
  [t('Référence encais. :'),   $id],
  [t('Catégorie :'),           t('Particulier')],
  [t('Adresse installation :'),$F['quartier']],
  [t('Statut facture :'),      ucfirst($F['statut'])]
];
foreach($infos as $row){
  $pdf->SetFont('Arial','B',10); $pdf->Cell(45,5,$row[0],0,0);
  $pdf->SetFont('Arial','',10);  $pdf->Cell(80,5,t($row[1]),0,1);
}
$pdf->Ln(3);

/* Tableau détail  */
$pdf->SetFont('Arial','B',11);
$pdf->SetFillColor(225,225,225);
$pdf->Cell(0,7,t('Détail de la facture'),1,1,'C',true);

/* entêtes */
$head=[t('Service'),t('Quantité'),t('Prix unitaire (HT)'),t('Prix Total (HT)')];
$w   =[60,40,45,45];
$pdf->SetFont('Arial','B',9);
foreach($head as $i=>$h) $pdf->Cell($w[$i],7,$h,1,0,'C');
$pdf->Ln();

/* données */
$pdf->SetFont('Arial','',9);
$service = ucfirst($F['type']=='elec' ? 'Electricité' : 'Eau');
$quant   = $F['valeur'].' '.($F['type']=='eau'?'m³':'kWh');
$montant = number_format($F['total_ttc'],0,',',' ').' Ar';      // ← TTC
$row     = [$service,$quant,$montant,$montant];

foreach($row as $i=>$txt) $pdf->Cell($w[$i],7,t($txt),1,0,'C');
$pdf->Ln(12);

/* ---------- Encadré total TTC ---------- */
$pdf->SetFont('Arial','B',12);
$pdf->Cell(130);
$pdf->Cell(40,10,t('Prix total à régler :'),1,0,'R');
$pdf->Cell(30,10,$montant,1,1,'R');

/*  Pied  */
$pdf->SetY(-15);
$pdf->SetFont('Arial','I',8);
$pdf->Cell(0,0,t('Merci de votre confiance - JIRAMA'),0,0,'C');

ob_end_clean();
$pdf->Output('I',"facture_$id.pdf");