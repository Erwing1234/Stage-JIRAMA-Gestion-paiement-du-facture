<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $pdo = new PDO("mysql:host=localhost;dbname=jirama;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(["error" => "Erreur BDD: " . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("
            SELECT r.*, c.type, cl.nom AS nom_client
            FROM releve r
            JOIN compteur c ON r.codecmp = c.codecmp
            JOIN client cl ON c.codecli = cl.codecli
            ORDER BY r.date_releve DESC
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("
            INSERT INTO releve (codecmp, valeur, date_releve, date_limite) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['codecmp'],
            $data['valeur'],
            $data['date_releve'],
            $data['date_limite']
        ]);
        echo json_encode(["message" => " Relevé ajouté."]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("
            UPDATE releve SET codecmp=?, valeur=?, date_releve=?, date_limite=? 
            WHERE id_releve=?
        ");
        $stmt->execute([
            $data['codecmp'],
            $data['valeur'],
            $data['date_releve'],
            $data['date_limite'],
            $data['id_releve']
        ]);
        echo json_encode(["message" => " Relevé modifié."]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $data);
        $stmt = $pdo->prepare("DELETE FROM releve WHERE id_releve = ?");
        $stmt->execute([$data['id_releve']]);
        echo json_encode(["message" => " Relevé supprimé."]);
        break;
}