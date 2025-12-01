<?php
// Autoriser l'appel depuis le frontend (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Inclure PHPMailer
require __DIR__ . '/../lib/PHPMailer-master/src/Exception.php';
require __DIR__ . '/../lib/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/../lib/PHPMailer-master/src/SMTP.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Récupérer les données envoyées depuis le frontend 
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input["email"]) || !isset($input["nom"]) || !isset($input["montant"])) {
    echo json_encode(["success" => false, "message" => "Paramètres manquants"]);
    exit;
}

$emailDestinataire = $input["email"];
$nomClient = $input["nom"];
$montant = $input["montant"];

$mail = new PHPMailer(true);

try {
    // Configurer SMTP Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'razanakolonaelvinah@gmail.com'; //  Mets ton adresse Gmail
    $mail->Password = 'yyrylepmkrtmbvhy';    //  Ton mot de passe d'application SANS espace
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Expéditeur
    $mail->setFrom('erwinrazanakolona8@gmail.com', 'JIRAMA');

    // Destinataire
    $mail->addAddress($emailDestinataire, $nomClient);

    // Contenu du mail
    $mail->isHTML(true);
    $mail->Subject = "Rappel de paiement - Facture JIRAMA";
    $mail->Body    = "
        Bonjour <b>$nomClient</b>,<br><br>
        Vous avez actuellement un solde impayé.<br>
        Ceci est un rappel concernant votre facture d'un montant de <b>$montant Ar</b>.<br>
        Merci de bien vouloir régulariser votre situation.<br><br>
        Cordialement,<br>
        <i>Service Client JIRAMA</i>
    ";
    $mail->AltBody = "Bonjour $nomClient,\nVotre facture est de $montant Ar.\nMerci.";

    // Envoyer
    $mail->send();

    echo json_encode(["success" => true, "message" => "Email envoyé à $emailDestinataire"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erreur lors de l'envoi: {$mail->ErrorInfo}"]);
}
