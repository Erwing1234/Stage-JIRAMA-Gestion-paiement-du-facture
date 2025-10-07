<?php
// En-têtes API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=jirama;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur de connexion : " . $e->getMessage()]);
    exit;
}

// Méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    //  GET : Liste des factures + nom client
    case 'GET':
        $stmt = $pdo->query("
            SELECT f.*, cl.nom AS nom_client
            FROM facture f
            JOIN client cl ON f.codecli = cl.codecli
            ORDER BY f.date_emission DESC
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    //  POST : Ajouter une facture
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['codecli'], $data['id_releve'], $data['total_ttc'], $data['date_emission'], $data['date_echeance'], $data['statut'])) {
            http_response_code(400);
            echo json_encode(["error" => "Champs requis manquants."]);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO facture (codecli, id_releve, date_emission, date_echeance, total_ht, total_ttc, statut)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        // Remplir total_ht = total_ttc / 1.2 (TVA 20%) par défaut
        $total_ht = $data['total_ttc'] / 1.2;

        $stmt->execute([
            $data['codecli'],
            $data['id_releve'],
            $data['date_emission'],
            $data['date_echeance'],
            $total_ht,
            $data['total_ttc'],
            $data['statut']
        ]);

        echo json_encode(["message" => " Facture ajoutée avec succès."]);
        break;

    //  PUT : Mettre à jour une facture
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id_fact'], $data['codecli'], $data['id_releve'], $data['total_ttc'], $data['date_emission'], $data['date_echeance'], $data['statut'])) {
            http_response_code(400);
            echo json_encode(["error" => "Champs requis pour modification."]);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE facture SET 
                codecli = ?,
                id_releve = ?,
                date_emission = ?,
                date_echeance = ?,
                total_ht = ?,
                total_ttc = ?,
                statut = ?
            WHERE id_fact = ?
        ");

        $total_ht = $data['total_ttc'] / 1.2;

        $stmt->execute([
            $data['codecli'],
            $data['id_releve'],
            $data['date_emission'],
            $data['date_echeance'],
            $total_ht,
            $data['total_ttc'],
            $data['statut'],
            $data['id_fact']
        ]);

        echo json_encode(["message" => " Facture modifiée avec succès."]);
        break;

    //  DELETE : Supprimer une facture
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $data);

        if (!isset($data['id_fact'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID facture requis pour suppression."]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM facture WHERE id_fact = ?");
        $stmt->execute([$data['id_fact']]);

        echo json_encode(["message" => " Facture supprimée avec succès."]);
        break;
}