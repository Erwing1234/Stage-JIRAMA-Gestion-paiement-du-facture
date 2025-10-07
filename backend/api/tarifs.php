<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $pdo = new PDO("mysql:host=localhost;dbname=jirama;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => " Connexion échouée : " . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM tarif ORDER BY type, min_u ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("INSERT INTO tarif (type, min_u, max_u, prix_unit, date_debut, date_fin) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['type'],
            $data['min_u'],
            $data['max_u'] !== '' ? $data['max_u'] : null,
            $data['prix_unit'],
            $data['date_debut'],
            $data['date_fin'] !== '' ? $data['date_fin'] : null
        ]);
        echo json_encode(["message" => "Tarif ajouté"]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("UPDATE tarif SET type=?, min_u=?, max_u=?, prix_unit=?, date_debut=?, date_fin=?
                               WHERE id_tarif=?");
        $stmt->execute([
            $data['type'],
            $data['min_u'],
            $data['max_u'] !== '' ? $data['max_u'] : null,
            $data['prix_unit'],
            $data['date_debut'],
            $data['date_fin'] !== '' ? $data['date_fin'] : null,
            $data['id_tarif']
        ]);
        echo json_encode(["message" => " Tarif modifié"]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $data);
        $stmt = $pdo->prepare("DELETE FROM tarif WHERE id_tarif = ?");
        $stmt->execute([$data['id_tarif']]);
        echo json_encode(["message" => " Tarif supprimé"]);
        break;
}