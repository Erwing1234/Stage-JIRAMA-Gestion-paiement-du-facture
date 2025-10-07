<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Connexion à MySQL
try {
    $pdo = new PDO("mysql:host=localhost;dbname=jirama;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion à la base de données."]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        //  Lister tous les clients
        $stmt = $pdo->query("SELECT * FROM client");
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($clients);
        break;

    case 'POST':
        //  Ajouter un client
        $data = json_decode(file_get_contents("php://input"), true);

        // Vérification des champs
        if (!isset($data['codecli'])) {
            echo json_encode(["success" => false, "message" => "Code client requis."]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO client (codecli, nom, sexe, quartier, niveau, email, téléphone)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $data['codecli'], $data['nom'], $data['sexe'], $data['quartier'],
            $data['niveau'], $data['email'], $data['téléphone']
        ]);

        echo json_encode(["success" => true, "message" => "Client ajouté avec succès."]);
        break;

    case 'PUT':
        //  Modifier un client
        $data = json_decode(file_get_contents("php://input"), true);

        // Vérification des champs
        if (!isset($data['codecli'])) {
            echo json_encode(["success" => false, "message" => "Code client requis pour modifier."]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE client SET nom=?, sexe=?, quartier=?, niveau=?, email=?, téléphone=? WHERE codecli=?");

        $stmt->execute([
            $data['nom'], $data['sexe'], $data['quartier'], $data['niveau'],
            $data['email'], $data['téléphone'], $data['codecli']
        ]);

        echo json_encode(["success" => true, "message" => "Client modifié avec succès."]);
        break;

    case 'DELETE':
        //  Supprimer un client
        parse_str(file_get_contents("php://input"), $data);

        if (!isset($data['codecli'])) {
            echo json_encode(["success" => false, "message" => "Code client requis pour suppression."]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM client WHERE codecli = ?");
        $stmt->execute([$data['codecli']]);

        echo json_encode(["success" => true, "message" => "Client supprimé avec succès."]);
        break;
}