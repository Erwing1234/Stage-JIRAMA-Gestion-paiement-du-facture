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
        // Lister les compteurs + nom client
        $stmt = $pdo->query("SELECT c.*, cl.nom AS nom_client FROM compteur c JOIN client cl ON c.codecli = cl.codecli");
        $compteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($compteurs);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['codecmp'])) {
            echo json_encode(["success" => false, "message" => "Code compteur requis."]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO compteur (codecmp, codecli, type, pu, date_inst, status)
                               VALUES (?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $data['codecmp'], $data['codecli'], $data['type'], $data['pu'],
            $data['date_inst'], $data['status']
        ]);

        echo json_encode(["success" => true, "message" => " Compteur ajouté avec succès."]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['codecmp'])) {
            echo json_encode(["success" => false, "message" => "Code compteur requis pour modification."]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE compteur SET codecli=?, type=?, pu=?, date_inst=?, status=? WHERE codecmp=?");

        $stmt->execute([
            $data['codecli'], $data['type'], $data['pu'], $data['date_inst'],
            $data['status'], $data['codecmp']
        ]);

        echo json_encode(["success" => true, "message" => " Compteur modifié avec succès."]);
        break;

    /* DELETE : RG15 – empêcher la suppression si relevés présents*/
case 'DELETE':
    parse_str(file_get_contents("php://input"), $d);

    if (empty($d['codecmp'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'codecmp requis pour suppression.'
        ]);
        exit;
    }

    /* existe-t-il au moins un relevé lié à ce compteur ? */
    $q = $pdo->prepare("SELECT EXISTS(SELECT 1 FROM releve WHERE codecmp = ? LIMIT 1)");
    $q->execute([$d['codecmp']]);
    $hasReleve = (int) $q->fetchColumn();   // 1 = oui, 0 = non

    if ($hasReleve) {
        http_response_code(409);            
        echo json_encode([
            'success' => false,
            'message' =>
              'Suppression impossible : des relevés sont encore associés à ce compteur.'
        ]);
        exit;
    }

    /* AUCUN relevé ⇒ on autorise la suppression */
    $pdo->prepare("DELETE FROM compteur WHERE codecmp = ?")->execute([$d['codecmp']]);

    echo json_encode([
        'success' => true,
        'message' => 'Compteur supprimé avec succès.'
    ]);
    break;
}