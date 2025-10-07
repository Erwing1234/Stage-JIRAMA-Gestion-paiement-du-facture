<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__.'/../lib/fpdf186/fpdf.php';   //  ← chemin vers FPDF

try{
    $pdo = new PDO("mysql:host=localhost;dbname=jirama;charset=utf8","root","");
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["error"=>"BDD : ".$e->getMessage()]); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method){

/*  GET  */
case 'GET':
    $stmt=$pdo->query("
      SELECT p.*, f.total_ttc, cl.nom AS nom_client
      FROM paiement p
      JOIN facture f ON p.id_fact = f.id_fact
      JOIN client  cl ON f.codecli = cl.codecli
      ORDER BY p.date_paiement DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;

/* POST  */
case 'POST':
    $d = json_decode(file_get_contents("php://input"), true);
    if(!isset($d['id_fact'],$d['mode'])){
        http_response_code(400);
        echo json_encode(["error"=>"id_fact et mode requis"]); exit;
    }

    // total TTC + client
    $q=$pdo->prepare("
        SELECT total_ttc, cl.nom AS nom_client
        FROM facture f
        JOIN client cl ON cl.codecli = f.codecli
        WHERE id_fact=?");
    $q->execute([$d['id_fact']]); $row = $q->fetch(PDO::FETCH_ASSOC);
    if(!$row){ http_response_code(404); echo json_encode(["error"=>"Facture inconnue"]); exit; }

    $montant = $row['total_ttc'];
    $nomCli  = $row['nom_client'];

    $ref = strtoupper(substr($d['mode'],0,2)).date("YmdHis");

    // insertion paiement
    $ins=$pdo->prepare("
        INSERT INTO paiement(id_fact,montant,mode,statut,ref_transac)
        VALUES (?,?,?, 'réussi', ?)");
    $ins->execute([$d['id_fact'],$montant,$d['mode'],$ref]);

    $id_pay = $pdo->lastInsertId();
    updateFactureStatut($pdo,$d['id_fact']);

    /* Génération du reçu PDF  */
    $dirRec = __DIR__.'/../receipts';
    if(!is_dir($dirRec)) mkdir($dirRec,0777,true);
    $pdfPath = $dirRec."/recu_$id_pay.pdf";

    $pdf = new FPDF();
    $pdf->AddPage();

    // logo
    $logo = dirname(__DIR__,2).'/logo.png';          // Stage1/logo.png
    if(file_exists($logo)) $pdf->Image($logo,10,8,25);

    // Titre
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,utf8_decode('REÇU DE PAIEMENT'),0,1,'C');
    $pdf->Ln(3);

    $pdf->SetFont('Arial','',11);
    $pdf->Cell(0,8,utf8_decode('N° Reçu  : ').$id_pay,0,1);
    $pdf->Cell(0,8,utf8_decode('Facture  : #').$d['id_fact'],0,1);
    $pdf->Cell(0,8,utf8_decode('Client   : ').$nomCli,0,1);
    $pdf->Cell(0,8,utf8_decode('Montant  : ').number_format($montant,0,',',' ').' Ar',0,1);
    $pdf->Cell(0,8,utf8_decode('Mode     : ').$d['mode'],0,1);
    $pdf->Cell(0,8,'Reference: '.$ref,0,1);
    $pdf->Ln(10);
    $pdf->SetFont('Arial','I',9);
    $pdf->Cell(0,8,utf8_decode('Merci pour votre reglement.'),0,1,'C');

    $pdf->Output('F',$pdfPath);

    $url = "http://localhost/Stage1/backend/receipts/recu_$id_pay.pdf";
    echo json_encode(["message"=>"Paiement enregistré","ref"=>$ref,"pdf"=>$url]);
    break;

/* DELETE  */
case 'DELETE':
    parse_str(file_get_contents("php://input"),$d);
    if(!isset($d['id_pay'])){ http_response_code(400); echo json_encode(["error"=>"id_pay manquant"]); exit; }

    $id_fact = $pdo->query("SELECT id_fact FROM paiement WHERE id_pay=".$d['id_pay'])->fetchColumn();
    $pdo->prepare("DELETE FROM paiement WHERE id_pay=?")->execute([$d['id_pay']]);
    if($id_fact) updateFactureStatut($pdo,$id_fact);

    // Supprimer reçu éventuel
    @unlink(__DIR__.'/../receipts/recu_'.$d['id_pay'].'.pdf');

    echo json_encode(["message"=>"Paiement supprimé"]);
    break;
}


/*  FONCTION  */
function updateFactureStatut($pdo,$id_fact){
    $totalPaye = $pdo->prepare("SELECT IFNULL(SUM(montant),0) FROM paiement WHERE id_fact=?");
    $totalPaye->execute([$id_fact]);
    $somme = $totalPaye->fetchColumn();

    $ttc = $pdo->prepare("SELECT total_ttc FROM facture WHERE id_fact=?");
    $ttc->execute([$id_fact]);
    $total = $ttc->fetchColumn();

    $statut = "impayé";
    if ($somme >= $total)  $statut = "payé";
    elseif ($somme > 0)    $statut = "partiel";

    $pdo->prepare("UPDATE facture SET statut=? WHERE id_fact=?")->execute([$statut,$id_fact]);
}