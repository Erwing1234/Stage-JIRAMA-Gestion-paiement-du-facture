<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");


require __DIR__.'/../lib/PHPMailer-master/src/Exception.php';
require __DIR__.'/../lib/PHPMailer-master/src/PHPMailer.php';
require __DIR__.'/../lib/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* Lecture des paramètres  */
$in = json_decode(file_get_contents('php://input'), true);

if (
    empty($in['id_fact']) ||
    empty($in['email'])   ||
    empty($in['nom'])     ||
    empty($in['montant'])
){
    echo json_encode(['success'=>false,'message'=>'Paramètres manquants']); exit;
}

$idFact  = (int)$in['id_fact'];
$email   = $in['email'];
$nom     = $in['nom'];
$montant = number_format($in['montant'],0,',',' ');

/*  Récupération ou construction du chemin PDF  */
try{
    $pdo = new PDO("mysql:host=localhost;dbname=jirama;charset=utf8","root","");
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

    $pdfPath = $pdo->prepare("SELECT pdf_path FROM facture WHERE id_fact=?");
    $pdfPath->execute([$idFact]);
    $pdfPath = $pdfPath->fetchColumn();

} catch(Exception $e){
    echo json_encode(['success'=>false,'message'=>'Erreur BDD : '.$e->getMessage()]); exit;
}

/* Dossier standard */
$defaultRelative = "/pdf_fact/facture_{$idFact}.pdf";

/*  si la colonne est vide, on prend le chemin par défaut relatif à www */
if(!$pdfPath){
    $pdfPath = $defaultRelative;
}

/* convertir en absolu */
$absolute = realpath(__DIR__.'/..'.$pdfPath); 

/* si realpath renvoie FALSE, on tente avec deux niveaux (cas projet root) */
if(!$absolute){
    $absolute = realpath(__DIR__.'/../..'.$pdfPath);
}

/* 3) si toujours pas de fichier, on régénère le PDF */
if(!$absolute || !file_exists($absolute)){
    ob_start();
    include __DIR__."/generer_pdf.php?id_fact={$idFact}";
    ob_end_clean();
    $absolute = realpath(__DIR__.'/..'.$pdfPath) ?: realpath(__DIR__.'/../..'.$pdfPath);
}

if(!$absolute || !file_exists($absolute)){
    http_response_code(404);
    echo json_encode([
        'success'=>false,
        'message'=>"PDF toujours introuvable pour la facture $idFact : chemin testé = ".$pdfPath
    ]);
    exit;
}

/* Envoi de l’e-mail  */
$mail = new PHPMailer(true);
try{
    // SMTP Gmail
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'razanakolonaelvinah@gmail.com';  
    $mail->Password   = 'yyrylepmkrtmbvhy';                
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Adresses
    $mail->setFrom('erwinrazanakolona8@gmail.com','JIRAMA');
    $mail->addAddress($email,$nom);

    // Pièce jointe
    $mail->addAttachment($absolute, basename($absolute));

    // Corps
    $mail->isHTML(true);
    $mail->Subject = "Rappel de paiement - Facture #$idFact";
    $mail->Body    = "
       Bonjour <b>$nom</b>,<br><br>
       Vous avez actuellement un solde impayé</br>
       Ceci est un rappel concernant votre facture n° <b>$idFact</b>
       d’un montant de <b>$montant&nbsp;Ar</b>.<br>
       Vous trouverez la facture en pièce jointe.<br>
       Merci de bien vouloir régulariser votre situation.<br><br>
       Cordialement,<br>
       <i>Service Client JIRAMA</i>";
    $mail->AltBody = "Bonjour $nom,\nFacture #$idFact : $montant Ar (PDF joint).";

    $mail->send();
    echo json_encode(['success'=>true,'message'=>"E-mail envoyé avec la facture jointe."]);
}catch(Exception $e){
    echo json_encode(['success'=>false,'message'=>'Erreur SMTP : '.$mail->ErrorInfo]);
}